<?php


namespace JulesGraus\FaviconTracker;


use Illuminate\Support\Facades\Log;

trait LogTrait
{
    private function log($level, string $message) {
        if(config('favicontracker.debug') === false) return;
        Log::log($level, self::class.': '. $message);
    }
}