<?php

namespace App\Providers;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
class CosRateLimiterMobileProvider extends ServiceProvider
{
    public static function configure()
    {
        RateLimiter::for('global', function(Request $request){
            return Limit::perMinute(50)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('artikel', function(Request $request){
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('acara', function(Request $request){
            return Limit::perMinute(25)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('disi', function(Request $request){
            return Limit::perMinute(25)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('emotal', function(Request $request){
            return Limit::perMinute(25)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('konsultasi', function(Request $request){
            return Limit::perMinute(25)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('nutrisi', function(Request $request){
            return Limit::perMinute(25)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('pengasuhan', function(Request $request){
            return Limit::perMinute(25)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('admin', function(Request $request){
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    }
}