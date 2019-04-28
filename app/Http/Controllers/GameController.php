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
use Illuminate\Support\Facades\Storage;

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
     * Play game
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


    /**
     * 派彩：not ready yet
     * 必須直接transfer 金額給player
     * 
     * @return \Illuminate\Http\Response
     */

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

    /**
     * End：not ready yet
     * 停止後直接呼叫 wallet api 去上鍊
     * 
     * @return \Illuminate\Http\Response
     */

    public function end(EndRequest $request)
    {
        if($request->isRequestValid()){
            
            return $request->validateSuccess(array());
        }
    }

    /**
     * Revoke: not ready yet (尚未討論)
     * 取回或取消某筆下注
     * 
     * @return \Illuminate\Http\Response
     */
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

    
    // 取得緩存餘額
    private function getLastBalance($balance_key){
        // get storage
        $disk = Storage::disk('tx');    // TODO: any safe storage

        // get last balance
        $last_balance = 0;
        if (!$disk->exists($balance_key)){
            $last_balance = $this->getBalance();
            $disk->put($balance_key, json_encode(['balance' => $last_balance]));
        } else {
            // parse data record
            $split_data = preg_split('/\s/', $disk->get($balance_key));
            foreach($split_data as $key => $value){
                if(!empty($value)){
                    $value = json_decode($value);
                    if(isset($value->balance)){
                        $last_balance = $value->balance;
                    }
                    if(isset($value->betAmount)){
                        $last_balance -= $value->betAmount;
                    }
                    if(isset($value->winAmount)){
                        $last_balance += $value->winAmount;
                    }
                }
            }
        }

        return $last_balance;
    }

    // 同步緩存餘額到 GGC
    private function syncLastBalanceToGGC($balance_key){
        // get storage
        $disk = Storage::disk('tx');    // TODO: any safe storage

        if (!$disk->exists($balance_key)){
            error_log(__FUNCTION__ . ' | ' . $balance_key . ' 找不到記錄 gg');
            return false;
        }

        $real_balance = $this->getBalance();

        // parse data record
        $split_data = preg_split('/\s/', $disk->get($balance_key));
        // preg_match('/balance":(?<number>\d+.\d+)/', $str, $matches);
        foreach($split_data as $key => $value){
            if(!empty($value)){
                $value = json_decode($value);
                if(isset($value->balance)){
                    if($real_balance != $value->balance) {
                        // 金額沒對上 gg
                        error_log(__FUNCTION__ . ' | ' . $balance_key . ' 金額沒對上 gg');
                        return false;
                    }
                }

                if(isset($value->betAmount)){
                    /*
                     * execute start by admin 
                     * NOTICE: got to find out a way to confirm transaction is completed
                     */
                    $ret = exec("cd ".$this->nodejs_path."; /usr/local/bin/node startByAdmin.js ".$value->betAmount*pow(10,8)." ".$value->roundId);
                }

                if(isset($value->winAmount)){
                    /*
                    * execute settle
                    * NOTICE: got to find out a way to confirm transaction is completed
                    */
                    $ret = exec("cd ".$this->nodejs_path."; /usr/local/bin/node settle.js ".$value->winAmount*pow(10,8)." ".$value->roundId);
                }
            }
        }

        // TODO: remove storage record
        $disk->move($balance_key, 'finished/' . date("Ymd"). '/' . $balance_key);

        return true;
    }

    // test only
    public function testEnd(EndRequest $request)
    {
        if($request->isRequestValid()){
            
            $sessionId = Input::get('sessionId');

            $sync_result = $this->syncLastBalanceToGGC($sessionId);

            if(!$sync_result){
                $extra_response = array(
                    'statusCode'=>666,
                    'status'=>'sync failed..'
                );
                return $request->validateSuccess($extra_response);
            }

            $extra_response = array(
                'balance' => number_format($this->getBalance()*pow(10,2), 0, '.', ''),
                'balanceSequence' => ''
            );
            return $request->validateSuccess($extra_response);
        }
    }

    // test only
    public function testPlay(PlayRequest $request)
    {
        if($request->isRequestValid()){
            
            // 'hash' => 'required|isvalidhash',
            // 'timestamp' => 'required|isvalidtimestamp',
            // 'sessionId' => 'required|string|max:32',
            // 'partnerPlayerId' => 'required|string',
            // 'currency' => 'required|string',
            // 'gameId' => 'required|string',
            // 'token' => 'string',
            // 'operatorName' => 'string',
            // 'action' => 'required|string',
            // 'playerIp' => 'required|ip'
            
            $sessionId = Input::get('sessionId');
            $transactionId = Input::get('transactionId');
            $betAmount = Input::get('betAmount')/pow(10,2); // calculate for cents purpose
            $winAmount = Input::get('winAmount')/pow(10,2);// calculate for cents purpose

            // ERR 1
            if($betAmount < 0.01){
                $extra_response = array(
                    'statusCode'=>201,
                    'status'=>'bet amount can not be less than 1 ggc'
                );
                return $request->validateSuccess($extra_response);
            }

            // get last balnce
            $balance_key = $sessionId;
            $last_balance = $this->getLastBalance($balance_key);

            // ERR 2
            if($last_balance < $betAmount){
                $extra_response = array(
                    'statusCode'=>200,
                    'status'=>'player balance is not enough'
                );
                return $request->validateSuccess($extra_response);
            }

            // rec current tx
            $disk = Storage::disk('tx');    // TODO: any safe storage
            $disk->append($balance_key, json_encode([
                'transactionId' => $transactionId, 
                'betAmount' => $betAmount, 
                'winAmount' => $winAmount,
                'roundId'   => time()
                ]));

            // get current balance
            $last_balance -= $betAmount;
            $last_balance += $winAmount;

            $extra_response = array(
                'balance' => number_format($last_balance*pow(10,2), 0, '.', ''),
                'balanceSequence' => ''
            );

            return $request->validateSuccess($extra_response);
        }
    }

}
