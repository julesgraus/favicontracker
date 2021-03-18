<?php

namespace JulesGraus\FaviconTracker;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Tracker
{
    use LogTrait;
    private TrackerService $service;

    public function __construct()
    {
        $this->service = new TrackerService();
    }

    public function write(Request $request) {
        //Generate a new id for the user. And prepare the first write cycle.
        $this->service->setIsWriteMode($request->ip() ?? 0, true);
        $trackingId = $this->service->generateTrackingId();
        $vector = implode('', $this->service->generateVectorFromId($trackingId));
        $this->service->cache($request->ip() ?? 0, $trackingId, $vector, true);

        return redirect()->to(route('fit.loop', ['path' => $this->service->routes()[0] ?? null]));
    }

    public function read(Request $request) {
        $this->service->cache($request->ip() ?? 0, -1, '', false);
        return redirect()->to(route('fit.loop', ['path' => $this->service->routes()[0] ?? null]));
    }

    public function loop(Request $request) {
        //Determine the next route
        $nextRouteIndex = $request->input('nextRouteIndex', -1) + 1;

        //Determine if we need to write more bits into the favicon cache.
        $done = $nextRouteIndex >= count($this->service->routes());

        //Return the next route that will trigger the browser into retrieving the next favicon or not.
        $nextRoute = route(!$done ? 'fit.loop' : 'fit.done',
            [
                'path' => $this->service->routes()[$nextRouteIndex] ?? null,
                'vector' => $this->service->getVectorFromCache($request->ip() ?? 0),
                'trackingId' => $this->service->getTrackingIdFromCache($request->ip() ?? 0),
                'nextRouteIndex' => !$done ? $nextRouteIndex : null,
            ]
        );

        //Return the view
        return view('fit::track', [
            'nextRoute' => $nextRoute,
            'redirectSpeed' => intval(config('favicontracker.redirectDelay', 0))
//            'redirectSpeed' => 1000
        ]);
    }

    public function done(Request $request) {
        $this->service->clearCacheForKey($request->ip() ?? 0);

        $trackingId = $request->input('trackingId');
        $vector = $request->input('vector');
        return response()->json(['trackingId' => $trackingId, 'vector' => $vector]);
    }

    public function favicon(Request $request) {
        $key = $request->ip() ?? 0;

        $path = $request->route()->parameter('path');
        $vector = $this->service->getVectorFromCache($key);
        $logPrefix = ($this->service->inWriteMode($key) ? 'Write' : 'Read').' mode: Favicon request "'.$request->path();

        //If in write mode we return the favicon for routes in the vector. And abort with status 404 if not in the vector. This writes the generated id into the favicon cache.
        if($this->service->inWriteMode($key)) { //TODO Handle write mode
            if(str_contains($vector, $path)) {
                $this->log('debug', $logPrefix . '". "'.$path.'" was found in vector "'.$vector.'. Response to request will be status 200 with a favicon. Resulting in "writing a 1" to the browsers favicon cache');
                return response()->file(__DIR__.DIRECTORY_SEPARATOR.'favicon.png');
            }
        } else if(!$this->service->inWriteMode($key)) {
            $this->log('debug', $logPrefix. '". recording "'.$path.'" in vector. Response to request will be status 404 without a favicon to keep id intact.');
            $this->service->cache($key, $this->service->getTrackingIdFromCache($key), $this->service->getVectorFromCache($key).$path, $this->service->inWriteMode($key));
        }

        //When in read mode we always return a 404 not found to keep the earlier written id.
        //We also return a 404 not found when in write mode and if path was not in the vector.
        return response(null, Response::HTTP_NOT_FOUND);
    }
}