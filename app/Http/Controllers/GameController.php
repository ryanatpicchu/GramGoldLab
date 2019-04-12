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
    
    public $nodejs_path;
    public function __construct()
    {
        $this->nodejs_path = base_path().'/node';
    }

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
    public function play(PlayRequest $request)
    {   
        if($request->isRequestValid()){
            $betAmount = Input::get('betAmount');
            $winAmount = Input::get('winAmount');
            
            /*
            wager step
            */

            if( $betAmount > 0){ //bet amount must be > 0
                $player_balance = $this->getBalance();

                if($player_balance >= $betAmount){

                    $roundId = time();

                    /*
                     * execute start by admin 
                     * NOTICE: got to find out a way to confirm transaction is completed
                     */
                    $ret = exec("cd ".$this->nodejs_path."; /usr/local/bin/node startByAdmin.js ".$betAmount*pow(10,8)." ".$roundId);

                    if($winAmount > 0){ //win amount > 0, means player win this wager
                        /*
                         * execute settle
                         * NOTICE: got to find out a way to confirm transaction is completed
                         */
                        $ret = exec("cd ".$this->nodejs_path."; /usr/local/bin/node settle.js ".$winAmount*pow(10,8)." ".$roundId);
                    }

                    $player_balance = $this->getBalance();

                    $extra_response = array(
                        'balance' => $player_balance,
                        'balanceSequence' => ''
                    );

                    return $request->validateSuccess($extra_response);
                }
                else{
                    $extra_response = array(
                        'statusCode'=>200,
                        'status'=>'player balance is not enough'
                    );
                }
            }
            else{//bet amount must be > 0
                $extra_response = array(
                    'statusCode'=>201,
                    'status'=>'bet amount can not be less than zero'
                );
            }


            return $extra_response;
            
        }
        
    }

    public function start(StartRequest $request)
    {
        if($request->isRequestValid()){
            

            $player_balance = $this->getBalance();

            $extra_response = array(
                'playerId' => $request->testFields()['playerId'],
                'sessionId' => Input::get('sessionId'),
                'balance' => $player_balance,
                'balanceSequence' => '',
                'currency' => Input::get('currency'),
                'betLimitId' => '',
                'sessionRTP' => ''
            );

            return $request->validateSuccess($extra_response);
        }
    }

    public function balance(BalanceRequest $request)
    {
        if($request->isRequestValid()){
            

            $extra_response = array(
                'balance' => $this->getBalance(),
                'balanceSequence' => ''
            );
            return $request->validateSuccess($extra_response);
        }
    }

    public function credit(CreditRequest $request)
    {
        if($request->isRequestValid()){
            

            $extra_response = array(
                'balance' =>  $this->getBalance(),
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


    private function getBalance(){
        $player_balance = exec("cd ".$this->nodejs_path."; /usr/local/bin/node getBalance.js ")/pow(10,8);

        return $player_balance;
    }

}
