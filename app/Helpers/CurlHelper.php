<?php

namespace App\Helpers;
use Illuminate\Support\Facades\Http;


class CurlHelper
{
    public static function call_api($url, $type, $payload=[], $headers=[] ){
        $result=[];
        $result['status']=false;
        $result['data']=[];
        
        try {
            $client = Http::withBasicAuth(config('services.wp_api.client_id'), config('services.wp_api.client_secret'));
            $response = $client->put($url, $payload);

            if ($response->getStatusCode() == 200)
            {
                $result['status'] = true;
            }
            else
            {
                $result['status'] = false;
            }

            $result['data']=$response->json();
            $result['response_status_code'] = $response->getStatusCode();

        } catch (\Exception $exception) {
            
            $result['response_status_code'] = @$response->getStatusCode();
            $result['data']=[
                'url'=>$url,
                'getCode'=>$exception->getCode(),
                'getFile'=>$exception->getFile(),
                'getLine'=>$exception->getLine(),
                'message'=>$exception->getMessage(),
            ];
        }
        return $result;
    }
}