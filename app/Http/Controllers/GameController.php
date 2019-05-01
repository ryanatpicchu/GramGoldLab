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

use GuzzleHttp\Client;
use function GuzzleHttp\json_encode;
use TheSeer\Tokenizer\Exception;

class GameController extends Controller
{   
    public $nodejs_path;

    public $disk_name;
    public $disk_waitting_dir;
    public $disk_finished_dir;
    public $disk_error_dir;
    public $disk_retry_dir;

    public function __construct()
    {
        $this->nodejs_path = base_path().'/node';

        // storage dir
        $this->disk_name = 'tx';
        $this->disk_waitting_dir = 'waitting/';     // 等待處理
        $this->disk_finished_dir = 'finished/';     // 已完結
        $this->disk_error_dir = 'error/';           // 異常
        $this->disk_retry_dir = 'retry/';           // 重試
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
        echo self::gen_request_hash(Input::json()->all());
    }

    // gen hash
    private static function gen_request_hash($temp_json){
        unset($temp_json['hash']);
        return hash_hmac('SHA256', json_encode($temp_json), 'gramgoldlab888');
    }

    /**
     * 開始遊戲。用於取得錢包餘額及設定可動用之上限
     * 
     * @return \Illuminate\Http\Response
     */
    public function start(StartRequest $request)
    {
        if($request->isRequestValid()){
            // parse token
            $wallet_input = Input::json()->all();
            $tokens = explode('_', $wallet_input['token']);
            $wallet_input['walletAddress'] = $tokens[0];
            $wallet_input['unlockToken'] = $tokens[1];

            // init local storage
            $sessionId = Input::get('sessionId');
            $disk = $this->getDisk($sessionId);

            if ($disk !== false){
                // TODO: session exists?      
                $wallet_balance = $this->getDiskBalance($sessionId);

            } else {
                // call wallet
                $wallet_result = self::call_wallet('start', $wallet_input);
                //$wallet_result = json_decode('{"status":"success","statusCode":0,"userMessage":"","approveAmount":2000000000000,"balance":"0","startTX":"cafd293ce778f15aa69a57e71af5612356414864acacd008fe3a99591911fc8b","nextUnlockToken":"GuQeRamtdYD4bay1Fxmv1MaNf2Y7z9Lb95kprDpZmyCv"}');

                if(!isset($wallet_result->statusCode) || $wallet_result->statusCode != 0){
                    $extra_response = array(
                        'statusCode'=>201,
                        'status'=>'invalid wallet'
                    );
                    return $request->validateSuccess($extra_response);
                }

                $wallet_balance = $wallet_result->balance;

                // init disk
                $this->initDisk($sessionId, 
                    $wallet_balance, $wallet_result->nextUnlockToken, $wallet_input['walletAddress']);
            }

            $extra_response = array(
                'playerId' => Input::get('partnerPlayerId'),
                'sessionId' => Input::get('sessionId'),
                'balance' => number_format($wallet_balance*pow(10,2), 0, '.', ''),
                'balanceSequence' => '',
                'currency' => Input::get('currency'),
                'betLimitId' => '',
                'sessionRTP' => ''
            );

            return $request->validateSuccess($extra_response);
        }
    }

    /**
     * Play game
     *
     * @return \Illuminate\Http\Response
     */
    public function play(PlayRequest $request)
    {   
        if($request->isRequestValid()){

            $sessionId = Input::get('sessionId');
            $transactionId = Input::get('transactionId');
            $betAmount = Input::get('betAmount')/pow(10,2); // calculate for cents purpose
            $winAmount = Input::get('winAmount')/pow(10,2);// calculate for cents purpose
            
            /*
            wager step
            */

            if( $betAmount >= 0.01){ //bet amount must be > 0.01 (equal to 1 ggc)
                $player_balance = $this->getDiskBalance($sessionId);

                if($player_balance === false){
                    $extra_response = array(
                        'statusCode'=>666,
                        'status'=>'invalid player'
                    );
                    return $request->validateSuccess($extra_response);
                }
                
                if($player_balance >= $betAmount){

                    // rec current tx
                    $disk = $this->getDisk($sessionId);
                    $disk->append($sessionId, json_encode([
                        'transactionId' => $transactionId, 
                        'betAmount' => $betAmount, 
                        'winAmount' => $winAmount,
                        'roundId'   => time()
                        ]));

                    // get current balance
                    $player_balance -= $betAmount;
                    $player_balance += $winAmount;

                    $extra_response = array(
                        'balance' => number_format($player_balance*pow(10,2), 0, '.', ''),
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

    /**
     * 查詢餘額
     * 
     * @return \Illuminate\Http\Response
     */
    public function balance(BalanceRequest $request)
    {
        if($request->isRequestValid()){
            
            $sessionId = Input::get('sessionId');

            $player_balance = $this->getDiskBalance($sessionId);

            // call wallet if session not start yet
            if($player_balance === false){
                error_log(__FUNCTION__ . '|' . $sessionId . '| call wallet');

                // parse token
                $token = Input::get('token');
                $tokens = explode('_', $token);
                $wallet_result = self::call_wallet('balance', 
                    ['timestamp' => time(), 'walletAddress' => $tokens[0], 'unlockToken' => $tokens[1]]);

                if(isset($wallet_result->statusCode)){
                    $player_balance = $wallet_result->balance;
                }
            }

            if($player_balance === false){
                $extra_response = array(
                    'statusCode'=>666,
                    'status'=>'invalid player'
                );
                return $request->validateSuccess($extra_response);
            }

            $extra_response = array(
                'balance' => number_format($player_balance*pow(10,2), 0, '.', ''),
                'balanceSequence' => ''
            );

            return $request->validateSuccess($extra_response);
        }
    }

    
    /**
     * 停止後直接呼叫 wallet api 去上鍊
     * 
     * @return \Illuminate\Http\Response
     */
    public function end(EndRequest $request)
    {
        if($request->isRequestValid()){
            
            $sessionId = Input::get('sessionId');

            // move to waitting
            $this->syncDiskToGGC($sessionId);

            // sync to ggc
            $this->syncDiskToGGC($sessionId, true); // TODO: unsync this step
            
            // // unsync call
            // $unsync_input = Input::json()->all();
            // $unsync_client = new \GuzzleHttp\Client(['base_uri' => 'http://192.168.10.10']);
            // $unsync_client->ansync(
            //     'POST', 
            //     "/api/v1/endless?hash=" . self::gen_request_hash($unsync_input), 
            //     ['json' =>  $unsync_input]);

            return $request->validateSuccess(array());
        }
    }

    /**
     * 上鍊
     * 
     * @return \Illuminate\Http\Response
     */
    public function endless(EndRequest $request)
    {
        if($request->isRequestValid()){
            
            $sessionId = Input::get('sessionId');

            // return $request->validateSuccess(array());

            // ob_end_clean();
            // ignore_user_abort(true);
            // ob_start();
            // header("Connection: close");
            // header("Content-Length: " . ob_get_length());
            // ob_end_flush();
            // flush();

            $this->syncDiskToGGC($sessionId, true);

            return $request->validateSuccess(array());
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

    // test only
    private function getBalance(){
        $player_balance = exec("cd ".$this->nodejs_path."; /usr/local/bin/node getBalance.js ")/pow(10,8);

        return $player_balance;
    }


    // call wallet api
    private static function call_wallet($action, $wallet_input){
        $wallet_api_host = 'http://wallet.gramgoldlab.com';

        // call wallet api
        try{
            $wallet_client = new \GuzzleHttp\Client(['base_uri' => $wallet_api_host]);
            $wallet_response = $wallet_client->request(
                'POST', 
                "/api/v1/$action?hash=" . self::gen_request_hash($wallet_input), 
                ['json' =>  $wallet_input, 'http_errors' => false]);
    
            $wallet_result = (string) $wallet_response->getBody();
            return json_decode($wallet_result);

        } catch (Exception $e) {
            error_log(__FUNCTION__ . ' | ' . $e->getMessage());
            return false;
        }
    }

    // init disk
    private function initDisk($sessionId, $balance, $nextUnlockToken, $walletAddress){
        $disk = Storage::disk($this->disk_name);
        $disk->put($sessionId, json_encode([
            'balance' => $balance, 
            'nextUnlockToken' => $nextUnlockToken,
            'walletAddress' => $walletAddress,
            ]));
    }

    // get session disk
    private function getDisk($sessionId, $waiting=false){
        $disk = Storage::disk($this->disk_name);

        if (!$disk->exists($sessionId)){
            error_log(__FUNCTION__ . '|' . $sessionId . '| not exists');
            return false;
        }

        if (!$waiting && $disk->exists($this->disk_waitting_dir . $sessionId)){
            error_log(__FUNCTION__ . '|' . $sessionId . '| waitting sync');
            return false;
        }

        return $disk;
    }

    // get disk balance （緩存餘額）
    private function getDiskBalance($sessionId){

        $disk = $this->getDisk($sessionId);

        if (!$disk){
            error_log(__FUNCTION__ . '|' . $sessionId . '| disk not found');
            return false;
        }

        // parse data record
        $disk_balance = 0;
        $split_data = preg_split('/\s/', $disk->get($sessionId));
        foreach($split_data as $key => $value){
            if(!empty($value)){
                $value = json_decode($value);
                if(isset($value->balance)){
                    $disk_balance = $value->balance;
                }
                if(isset($value->betAmount)){
                    $disk_balance -= $value->betAmount;
                }
                if(isset($value->winAmount)){
                    $disk_balance += $value->winAmount;
                }
            }
        }

        return $disk_balance;
    }
    
    // sync disk to GGC (上鏈)
    private function syncDiskToGGC($sessionId, $waitting=false){
        
        // if is not waitting, check balance and move to waitting dir first
        if(!$waitting){

            $disk_balance = $this->getDiskBalance($sessionId);

            if(!$disk_balance){
                return false;
            }

            $disk = $this->getDisk($sessionId);

            // parse data record
            $transactions = ['start' => [], 'play' => []];
            $split_data = preg_split('/\s/', $disk->get($sessionId));
            foreach($split_data as $key => $value){
                if(!empty($value)){
                    $value = json_decode($value);
                    if(isset($value->balance)){
                        $transactions['start'][] = $value;
                    }

                    if(isset($value->transactionId)){
                        $transactions['play'][] = $value;
                    }
                }
            }

            // check start
            if(sizeof($transactions['start']) !== 1){
                error_log(__FUNCTION__ . '|' . $sessionId . '| unknown start');
                return false;
            }

            // get wallet
            $walletAddress = $transactions['start'][0]->walletAddress;
            $nextUnlockToken = $transactions['start'][0]->nextUnlockToken;

            // wallet api (balance)
            $wallet_result = self::call_wallet('balance', ['timestamp' => time(), 'walletAddress' => $walletAddress, 'unlockToken' => $nextUnlockToken]);

            if(!isset($wallet_result->statusCode) || $wallet_result->statusCode != 0){
                error_log(__FUNCTION__ . '|' . $sessionId . '| wallet api (balance) error');
                return false;
            }

            if($wallet_result->balance != $transactions['start'][0]->balance){
                error_log(__FUNCTION__ . '|' . $sessionId . '| balance mismatch');
                // move to error
                $disk->move($sessionId, $this->disk_error_dir . $sessionId);
                return false;
            }

            // check complete, move to waitting dir
            $disk->move($sessionId, $this->disk_waitting_dir . $sessionId);

            return $disk_balance;
        }

        // get waiting disk
        $disk = $this->getDisk($this->disk_waitting_dir . $sessionId, true);

        if(!$disk){
            return false;
        }

        // parse data record
        $transactions = ['start' => [], 'play' => []];
        $split_data = preg_split('/\s/', $disk->get($this->disk_waitting_dir . $sessionId));
        foreach($split_data as $key => $value){
            if(!empty($value)){
                $value = json_decode($value);
                if(isset($value->balance)){
                    $transactions['start'][] = $value;
                }

                if(isset($value->transactionId)){
                    $transactions['play'][] = $value;
                }
            }
        }

        // check start
        if(sizeof($transactions['start']) !== 1){
            error_log(__FUNCTION__ . '|' . $sessionId . '| unknown start');
            return false;
        }

        // get wallet
        $walletAddress = $transactions['start'][0]->walletAddress;
        $nextUnlockToken = $transactions['start'][0]->nextUnlockToken;
        
        // wallet api (play)
        $retrys = [];
        while($nextx = array_shift($transactions['play'])) {
            $wallet_input = [
                'walletAddress' => $walletAddress, 
                'unlockToken' => $nextUnlockToken,
                'betAmount' => $nextx->betAmount*pow(10,2),
                'winAmount' => $nextx->winAmount*pow(10,2),
                'round'     => $nextx->roundId,
                'timestamp' => time(),
            ];
            $wallet_result = self::call_wallet('play', $wallet_input);
            if(!isset($wallet_result->statusCode) || $wallet_result->statusCode != 0){
                error_log(__FUNCTION__ . '|' . $sessionId . '| call wallet failed' . $nextx->transactionId);
                $retrys[] = $nextx;
                continue;
            }
            error_log(__FUNCTION__ . '|' . $sessionId . '| tx success => ' . $nextx->transactionId);
            $nextUnlockToken = $wallet_result->nextUnlockToken;
        };

        $wallet_result = self::call_wallet('balance', ['timestamp' => time(), 'walletAddress' => $walletAddress, 'unlockToken' => $nextUnlockToken]);

        if(!isset($wallet_result->statusCode) || $wallet_result->statusCode != 0){
            error_log(__FUNCTION__ . '|' . $sessionId . '| call wallet failed | when init');
            $last_balance = 0;
        } else {
            $last_balance = $wallet_result->balance;
            $disk->append($this->disk_waitting_dir . $sessionId, json_encode([
                'balance' => $last_balance, 
                'retrys' => $retrys, 
                ]));            
        }

        // move to finished
        $disk->move($this->disk_waitting_dir . $sessionId, $this->disk_finished_dir . date("Ymd"). '/' . $sessionId . '_' . time());

        // move to retry
        if(!empty($retrys)) {
            $this->initDisk($this->disk_retry_dir . $sessionId, 
                $last_balance, $nextUnlockToken, $walletAddress);

            foreach($retrys as $key => $value) {
                $disk->append($this->disk_retry_dir . $sessionId, json_encode([
                    'transactionId' => $value->transactionId, 
                    'betAmount' => $value->betAmount, 
                    'winAmount' => $value->winAmount,
                    'roundId'   => $value->roundId,
                    'reCount'   => isset($value->reCount) ? $value->reCount + 1 : 1,
                    ]));
            }
        }
        return true;
    }




}
