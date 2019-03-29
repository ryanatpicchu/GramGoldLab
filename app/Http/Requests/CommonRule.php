<?php

namespace App\Http\Requests;
use Illuminate\Support\Facades\Validator;

trait CommonRule
{
    
    protected function testFields()
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
    protected function commonRules()
    {
        Validator::extend('isvalidtimestamp', function ($attribute, $timestamp, $parameters, $validator) {

            return ((int) $timestamp === $timestamp)
            && ($timestamp <= PHP_INT_MAX)
            && ($timestamp >= ~PHP_INT_MAX);
        });

        return [
            'hash' => 'required',
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

    protected function commonMessages()
    {
        return [
            'timestamp.isvalidtimestamp' => 'timestamp is not valid'
        ];
    }

    protected function validateSuccess($extra_response = array()){
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
