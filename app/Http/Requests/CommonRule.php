<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class CommonRule
{
    
    public function testFields()
    {
        return [
            'playerId' => 'Ryan_test',
            'balance' => '1000000'
        ];
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function commonRules()
    {
        Validator::extend('isvalidtimestamp', function ($attribute, $timestamp, $parameters, $validator) {
            
            return ((int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
        });
        
        Validator::extend('isvalidhash', function ($attribute, $hash, $parameters, $validator) {

            $temp_json = $validator->getData();
            // $temp_json = Input::json()->all();
            // echo "<pre>";print_r($temp_json);echo "</pre>";exit;
            unset($temp_json['hash']);
            if($hash == hash_hmac('SHA256', json_encode($temp_json), 'gramgoldlab888')){
                return TRUE;
            }
            else return FALSE;
            
        });

        return [
            'hash' => 'required|isvalidhash',
            'timestamp' => 'required|isvalidtimestamp',
            'sessionId' => 'required|string|max:32',
            'partnerPlayerId' => 'required|string',
            'currency' => 'required|string',
            'gameId' => 'required|string',
            'token' => 'string',
            'operatorName' => 'string',
            'action' => 'required|string',
            'playerIp' => 'required|ip'
        ];
    }

    public function commonMessages()
    {
        return [
            'timestamp.isvalidtimestamp' => 'timestamp is not valid'
        ];
    }

    public function validateSuccess($extra_response = array()){
        $json = [
            'status' => 'success',
            'statusCode' => 0,
            'userMessage' => ''
        ];

        if(!empty($extra_response)){
            $json = array_merge($json,$extra_response);
        }

        return $json;
    }

}
