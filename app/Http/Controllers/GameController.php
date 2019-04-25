<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartRequest;
use App\Http\Requests\PlayRequest;
use App\Http\Requests\BalanceRequest;
use App\Http\Requests\CreditRequest;
use App\Http\Requests\EndRequest;
use App\Http\Requests\RevokeRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class GameController extends Controller
{   
    
    public $nodejs_path;
    public function __construct()
    {
        $this->nodejs_path = base_path().'/node';
    }

    public function setSecureToken(Request $request){
        
        $input = $request->all();
        echo "<pre>";print_r($input);echo "</pre>";
        error_log($input['unlockToken']);
        error_log($input['walletAddress']);
        // echo 'test';
    }

    public function unlockTokenToSignature(){
        

    }

    public function getTempWalletBalance(){

        $nodejs_path = base_path().'/node';
        
        $ret = exec("cd ".$nodejs_path."; /usr/local/bin/node getTempWalletBalance.js");

        echo ($ret/100000000);
    }

    public function genTempHash(){
        // $temp_json = $validator->getData();
        // $bodyContent = $request->getContent();
        // $content = Request::all();

        $temp_json = Input::json()->all();

        unset($temp_json['hash']);
        // $temp_json['timestamp']='111';
        echo hash_hmac('SHA256', json_encode($temp_json), 'gramgoldlab888');
         
    }

    /**
     * Start game
     *
     * @return \Illuminate\Http\Response
     */
    public function play(PlayRequest $request)
    {   
        if($request->isRequestValid()){


            $betAmount = Input::get('betAmount')/pow(10,2); // calculate for cents purpose
            $winAmount = Input::get('winAmount')/pow(10,2);// calculate for cents purpose


            
            /*
            wager step
            */

            if( $betAmount >= 0.01){ //bet amount must be > 0.01 (equal to 1 ggc)
                $player_balance = $this->getBalance();
                
                if($player_balance >= $betAmount){

                    $roundId = time();

                    /*
                     * execute start by admin 
                     * NOTICE: got to find out a way to confirm transaction is completed
                     */
                    
                    $ret = exec("cd ".$this->nodejs_path."; /usr/local/bin/node startByAdmin.js ".$betAmount*pow(10,8)." ".$roundId);

                   /*
                    * execute settle
                    * NOTICE: got to find out a way to confirm transaction is completed
                    */
                    $ret = exec("cd ".$this->nodejs_path."; /usr/local/bin/node settle.js ".$winAmount*pow(10,8)." ".$roundId);

                    

                    $extra_response = array(
                        'balance' => number_format($this->getBalance()*pow(10,2), 0, '.', ''),
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
            else{//bet amount must be >= 0.01 (1 ggc)
                $extra_response = array(
                    'statusCode'=>201,
                    'status'=>'bet amount can not be less than 1 ggc'
                );
            }


            return $extra_response;
            
        }
        
    }

    public function start(StartRequest $request)
    {
        if($request->isRequestValid()){
            

            $extra_response = array(
                'playerId' => Input::get('partnerPlayerId'),
                'sessionId' => Input::get('sessionId'),
                'balance' => number_format($this->getBalance()*pow(10,2), 0, '.', ''),
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
                'balance' => number_format($this->getBalance()*pow(10,2), 0, '.', ''),
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
