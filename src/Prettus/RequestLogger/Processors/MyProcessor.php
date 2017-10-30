<?php

namespace Prettus\RequestLogger\Processors;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MyProcessor
{
    public function __invoke(array $record)
    {
        $request = app('request');

        $record['extra']['request']['url'] = $request->url();
        $record['extra']['request']['ip'] = $request->ip();
        //$record['extra']['request']['ip'] = $this->getIp(); to get real IP when behind load balancers, etc.
        $record['extra']['request']['user-agent'] = $request->header('User-Agent');
        $record['extra']['request']['data'] = $request->all();

        $record['extra']['response']['time'] = \Prettus\RequestLogger\Helpers\Benchmarking::duration('application');
        $record['extra']['response']['memory'] = memory_get_peak_usage(); // (in bytes), optional memory_get_peak_usage(true) to get real usage
        $record['extra']['response']['code'] = $record['message'];

        $record['extra']['level'] = $record['level'];
        $record['extra']['datetime'] = $record['datetime'];

        $record = $record['extra'];

        return $record;
    }

    public function getIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
    }
}