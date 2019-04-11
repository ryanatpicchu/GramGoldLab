<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartRequest;
use App\Http\Requests\PlayRequest;
use App\Http\Requests\BalanceRequest;
use App\Http\Requests\CreditRequest;
use App\Http\Requests\EndRequest;
use App\Http\Requests\RevokeRequest;

use Illuminate\Support\Facades\Input;

class GameController extends Controller
{   

    public function getTempWalletBalance(){
        
        $nodejs_path = base_path().'/node';
        
        $ret = exec("cd ".$nodejs_path."; /usr/local/bin/node getTempWalletBalance.js");

        echo ($ret/100000000);
    }

    public function genUserSig(){
        echo 'test';exit;
        $nodejs_path = base_path().'/node';
        
        // $ret = exec("cd ".$nodejs_path."; /usr/local/bin/node genUserSig.js 0x37b1030fb71a49d40696a48e7ee7dafaec6e5966dd0030ac8794ef4887ff4913");

        $ret = exec("cd ".$nodejs_path."; /usr/local/bin/node delegateTransfer.js");
        echo $ret;
    }


    /**
     * Start game
     *
     * @return \Illuminate\Http\Response
     */
    public function start(StartRequest $request)
    {   

        

        // $json_body = '[{ "timestamp":1503383341514, "sessionId":"375e3e418a45494c92bf1e6ec2f7460e", "partnerPlayerId":"demouserf", "currency":"ISO 4217", "gameId":"Vampire", "action":"play", "playerIp":"223.27.48.212" }]';

        // $test_secret_key = 'gramgoldlab';
        // $url_hash_param = hash_hmac('SHA256', $json_body, $test_secret_key);

        // echo 'requested hash : '.$request->hash."<br />";
        // echo 'jsoned hash : '.$url_hash_param."<br />";exit;

        if($request->isRequestValid()){
            

            $extra_response = array(
                'playerId' => $request->testFields()['playerId'],
                'sessionId' => Input::get('sessionId'),
                'balance' => $request->testFields()['balance'],
                'balanceSequence' => '',
                'currency' => Input::get('currency'),
                'betLimitId' => '',
                'sessionRTP' => ''
            );
            return $request->validateSuccess($extra_response);
        }
        
    }

    public function play(PlayRequest $request)
    {
        if($request->isRequestValid()){
            

            $extra_response = array(
                'balance' => $request->testFields()['balance'],
                'balanceSequence' => ''
            );
            return $request->validateSuccess($extra_response);
        }
    }

    public function balance(BalanceRequest $request)
    {
        if($request->isRequestValid()){
            

            $extra_response = array(
                'balance' => $request->testFields()['balance'],
                'balanceSequence' => ''
            );
            return $request->validateSuccess($extra_response);
        }
    }

    public function credit(CreditRequest $request)
    {
        if($request->isRequestValid()){
            

            $extra_response = array(
                'balance' => $request->testFields()['balance'],
                'balanceSequence' => ''
            );
            return $request->validateSuccess($extra_response);
        }
    }

    public function end(EndRequest $request)
    {
        if($request->isRequestValid()){
            
            return $request->validateSuccess(array());
        }
    }

    public function revoke(RevokeRequest $request)
    {
        if($request->isRequestValid()){
            

            $extra_response = array(
                'balance' => $request->testFields()['balance'],
                'balanceSequence' => ''
            );
            return $request->validateSuccess($extra_response);
        }
    }

}
