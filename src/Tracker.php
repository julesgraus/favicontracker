<?php

namespace JulesGraus\FaviconTracker;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class Tracker
{
    use LogTrait;
    private TrackerService $service;
    private string $key;

    public function __construct(Request $request)
    {
        if(app()->runningInConsole()) return;
        
        $this->service = new TrackerService();
        $this->key = $request->ip() ?? 0;
    }

    public function action(Request $request) {
        return response(view('fit::action'));
    }

    public function write(Request $request) {
        //Generate a new id for the user. And prepare the first write cycle.
        $this->service->setIsWriteMode($this->key, true);
        $trackingId = $this->service->generateTrackingId();
        $vector = implode('', $this->service->generateVectorFromId($trackingId));
        $this->service->cache($this->key, $trackingId, $vector, true);

        $this->log('debug', 'Lets write the id into the favicon cache!');
        return redirect()->to(route('fit.loop', [
            'path' => $this->service->routes()[0] ?? null,
            'vector' => $this->service->getVectorFromCache($this->key),
            'trackingId' => $this->service->getTrackingIdFromCache($this->key),
            'nextRouteIndex' => 0
        ]));
    }

    public function read(Request $request) {
        $auto = false;
        if(!!$request->get('auto', false)) {
            $this->log('debug', 'Auto mode on, first checking if user has an id. If not give him a new one.');
            $auto = true;
        }

        $this->service->cache($this->key, -1, '', false, $auto);
        $this->log('debug', 'Lets read the id from the favicon cache!');
        return redirect()->to(route('fit.loop', [
            'path' => $this->service->routes()[0] ?? null,
            'vector' => $this->service->getVectorFromCache($this->key),
            'trackingId' => $this->service->getTrackingIdFromCache($this->key),
            'nextRouteIndex' => 0
        ]));
    }

    public function loop(Request $request) {
        //Determine the next route
        $nextRouteIndex = $request->input('nextRouteIndex', -1) + 1;
        $this->log('debug', 'Next route: ' . ($this->service->routes()[$nextRouteIndex] ?? '-'));

        //Determine if we need to read or write more bits from or into the favicon cache.
        $done = $nextRouteIndex >= count($this->service->routes());

        //Create the next route that will trigger the browser into retrieving the next favicon or not.
        $nextRoute = route(!$done ? 'fit.loop' : 'fit.done',
            [
                'path' => $this->service->routes()[$nextRouteIndex] ?? null,
                'vector' => $this->service->getVectorFromCache($this->key),
                'trackingId' => $this->service->getTrackingIdFromCache($this->key),
                'nextRouteIndex' => !$done ? $nextRouteIndex : null,
            ]
        );

        //Return the view
        return response(view('fit::track', [
            'nextRoute' => $nextRoute,
            'redirectSpeed' => 300
        ]))->withHeaders([
            "Cache-Control" => "no-cache, must-revalidate, no-store, max-age=0, private",
        ]);
    }

    public function done(Request $request) {

        if (!$this->service->inWriteMode($this->key)) {
            //Inverse the vector when done reading. Because in read mode the browser only requests favicons that are not cached (the zeroes)
            //And we want to determine the routes used for writing the ones.
            if(mb_strlen($this->service->getVectorFromCache($this->key)) > 0) {
                $invertedVector = $this->service->inverseVector($this->service->getVectorFromCache($this->key), $this->service->routes());
                $trackingId = $this->service->generateIdFromVector($invertedVector);
                $this->log('debug', 'Done reading! The browser requested favicons for these routes: ' .
                    $this->service->getVectorFromCache($this->key) . '. ' .
                    'This means that those represent zero\'s. And means that these routes mean ones: ' . $invertedVector . '. ' .
                    'With that knowledge the tracking id that could be identified was: ' . $trackingId
                );
                $this->service->cache($this->key, $trackingId, $invertedVector, $this->service->inWriteMode($this->key));
            } else {
                $this->log('debug', 'Done Reading. No favicons where requested. This means the user is not tracked yet. Automode was '.($this->service->inAutoMode($this->key) ? '"on"' : '"off"'));
                $this->service->cache($this->key, -1, '', false, $this->service->inAutoMode($this->key));
                if($this->service->inAutoMode($this->key)) {
                    $this->log('debug', 'Because of that, we are going to assign him a new id.');
                    return response()->redirectTo(route('fit.write'));
                }
            }
        } else {
            $this->log('debug', 'Done writing!');
        }

        $trackingId = $this->service->getTrackingIdFromCache($this->key);
        $vector = $this->service->getVectorFromCache($this->key);
        $this->service->clearCacheForKey($this->key);
        return response()->json(['trackingId' => $trackingId, 'vector' => $vector]);
    }

    public function favicon(Request $request) {
        $this->log('debug', 'favicon requested: '.$request->path());
        $key = $this->key;

        $path = $request->route()->parameter('path');
        $vector = $this->service->getVectorFromCache($key);
        $logPrefix = ($this->service->inWriteMode($key) ? 'Write' : 'Read').' mode: Favicon request "'.$request->path().'"';

        //If in write mode we return the favicon for routes in the vector. And abort with status 404 if not in the vector. This writes the generated id into the favicon cache.
        if($this->service->inWriteMode($key)) {
            if(strpos($vector, $path) !== false) {
                $this->log('debug', $logPrefix . ' "'.$path.'" was found in vector "'.$vector.'. Response to request will be status 200 with a favicon. Resulting in "writing a 1" to the browsers favicon cache');
                return response()->file(__DIR__.DIRECTORY_SEPARATOR.'favicon.png');
            }
        } else if(!$this->service->inWriteMode($key)) {
            $this->log('debug', $logPrefix. ' recording "'.$path.'" in vector. Response to request will be status 404 without a favicon to keep id intact.');
            $this->service->cache($key, $this->service->getTrackingIdFromCache($key), $this->service->getVectorFromCache($key).$path, $this->service->inWriteMode($key));
        }

        //When in read mode we always return a 404 not found to keep the earlier written id.
        //We also return a 404 not found when in write mode and the path was not in the vector.
        return response(null, Response::HTTP_NOT_FOUND);
    }
}