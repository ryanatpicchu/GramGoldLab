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
use GuzzleHttp\Exception\ServerException;

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
            // init local storage
            $walletAddress = self::getWalletAddress();
            $disk = $this->getDisk($walletAddress);

            if ($disk !== false){
                // TODO: session exists?      
                $wallet_balance = $this->getDiskBalance($walletAddress);

            } else {
                // call wallet
                $wallet_balance = $this->initPlayer($walletAddress);
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

            $transactionId = Input::get('transactionId');
            $betAmount = Input::get('betAmount')/pow(10,2); // calculate for cents purpose
            $winAmount = Input::get('winAmount')/pow(10,2);// calculate for cents purpose

            $walletAddress = self::getWalletAddress();

            /*
            wager step
            */

            if( $betAmount >= 0.01){ //bet amount must be > 0.01 (equal to 1 ggc)
                $player_balance = $this->getDiskBalance($walletAddress);

                // not start yet, auto start
                if($player_balance === false){
                    error_log(__FUNCTION__ . '|' . $walletAddress . '| not start, auto start player');
                    $player_balance = $this->initPlayer($walletAddress);
                }

                if($player_balance === false){
                    $extra_response = array(
                        'statusCode'=>666,
                        'status'=>'invalid player'
                    );
                    return $request->validateSuccess($extra_response);
                }
                
                if($player_balance >= $betAmount){

                    // rec current tx
                    $disk = $this->getDisk($walletAddress);
                    $disk->append($walletAddress, json_encode([
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
            
            $walletAddress = self::getWalletAddress();

            $player_balance = $this->getDiskBalance($walletAddress);

            // not start yet, auto start
            if($player_balance === false){
                error_log(__FUNCTION__ . '|' . $walletAddress . '| not start, auto start player');
                $player_balance = $this->initPlayer($walletAddress);
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
            
            $walletAddress = self::getWalletAddress();

            // move to waitting
            $this->syncDiskToGGC($walletAddress);

            // sync to ggc
            $this->syncDiskToGGC($walletAddress, true); // TODO: unsync this step
            
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
            
            $walletAddress = self::getWalletAddress();

            // return $request->validateSuccess(array());

            // ob_end_clean();
            // ignore_user_abort(true);
            // ob_start();
            // header("Connection: close");
            // header("Content-Length: " . ob_get_length());
            // ob_end_flush();
            // flush();

            $this->syncDiskToGGC($walletAddress, true);

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

            $creditAmount = Input::get('amount')/pow(10,2);// calculate for cents purpose
            
            $wallet_input = [
                'timestamp' =>  time(),
                'walletAddress' =>  self::getWalletAddress(),
                'creditAmount'  =>  $creditAmount
            ];
            $wallet_result = self::call_wallet('credit', $wallet_input);
           
            if(!isset($wallet_result->statusCode) || $wallet_result->statusCode != 0){
                $extra_response = array(
                    'statusCode'=>444,
                    'status'=>'something went wrong'
                );
                return $request->validateSuccess($extra_response);
            }

            $extra_response = array(
                'balance' =>  number_format($wallet_result->balance*pow(10,2), 0, '.', ''),
                'amount' => $wallet_result->creditAmount*pow(10,2),
                'creditTX' => $wallet_result->creditTX
            );
            return $request->validateSuccess($extra_response);
        }
    }

    /**
     * Revoke: not ready yet (尚未討論);
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
            error_log(__FUNCTION__ . 
                ' | '. $action . ' | ' . $wallet_response->getStatusCode() .
                ' | result: ' . $wallet_result . 
                ' | input: '. json_encode($wallet_input));
            return json_decode($wallet_result);

        } catch (ServerException $e) {
            error_log(__FUNCTION__ . ' | ' . $e->getMessage());
            return false;
        }
    }

    // get current wallet addr
    private static function getWalletAddress() {
        return explode('_', Input::get('token'))[0];
    }

    // init without start
    private function initPlayer($data_key) {
        // parse token
        $wallet_input = Input::json()->all();
        $tokens = explode('_', $wallet_input['token']);
        $wallet_input['walletAddress'] = $tokens[0];
        $wallet_input['unlockToken'] = $tokens[1];
        $wallet_result = self::call_wallet('start', $wallet_input);
        //$wallet_result = json_decode('{"status":"success","statusCode":0,"userMessage":"","approveAmount":2000000000000,"balance":"0","startTX":"cafd293ce778f15aa69a57e71af5612356414864acacd008fe3a99591911fc8b","nextUnlockToken":"GuQeRamtdYD4bay1Fxmv1MaNf2Y7z9Lb95kprDpZmyCv"}');

        if(!isset($wallet_result->statusCode) || $wallet_result->statusCode != 0){
            return false;
        }

        // init disk
        $this->initDisk($data_key, 
            $wallet_result->balance, $wallet_result->nextUnlockToken, $wallet_input['walletAddress']);

        return $wallet_result->balance;
    }

    // init disk
    private function initDisk($data_key, $balance, $nextUnlockToken, $walletAddress){
        $disk = Storage::disk($this->disk_name);
        $disk->put($data_key, json_encode([
            'balance' => $balance, 
            'nextUnlockToken' => $nextUnlockToken,
            'walletAddress' => $walletAddress,
            ]));
        error_log(__FUNCTION__ . ' | ' . $data_key);
    }

    // get session disk
    private function getDisk($data_key, $waiting=false){
        $disk = Storage::disk($this->disk_name);

        if (!$disk->exists($data_key)){
            error_log(__FUNCTION__ . '|' . $data_key . '| not exists');
            return false;
        }

        if (!$waiting && $disk->exists($this->disk_waitting_dir . $data_key)){
            error_log(__FUNCTION__ . '|' . $data_key . '| waitting sync');
            return false;
        }

        return $disk;
    }

    // get disk balance （緩存餘額）
    private function getDiskBalance($data_key){

        $disk = $this->getDisk($data_key);

        if (!$disk){
            error_log(__FUNCTION__ . '|' . $data_key . '| disk not found');
            return false;
        }

        // parse data record
        $disk_balance = 0;
        $split_data = preg_split('/\s/', $disk->get($data_key));
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
    private function syncDiskToGGC($data_key, $waitting=false){
        
        // if is not waitting, check balance and move to waitting dir first
        if(!$waitting){

            $disk_balance = $this->getDiskBalance($data_key);

            if(!$disk_balance){
                return false;
            }

            $disk = $this->getDisk($data_key);

            // parse data record
            $transactions = ['start' => [], 'play' => []];
            $split_data = preg_split('/\s/', $disk->get($data_key));
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
                error_log(__FUNCTION__ . '|' . $data_key . '| unknown start');
                return false;
            }

            // get wallet
            $walletAddress = $transactions['start'][0]->walletAddress;
            $nextUnlockToken = $transactions['start'][0]->nextUnlockToken;

            // wallet api (balance)
            $wallet_result = self::call_wallet('balance', ['timestamp' => time(), 'walletAddress' => $walletAddress, 'unlockToken' => $nextUnlockToken]);

            if(!isset($wallet_result->statusCode) || $wallet_result->statusCode != 0){
                error_log(__FUNCTION__ . '|' . $data_key . '| wallet api (balance) error');
                return false;
            }

            if($wallet_result->balance != $transactions['start'][0]->balance){
                error_log(__FUNCTION__ . '|' . $data_key . '| balance mismatch');
                // move to error
                $disk->move($data_key, $this->disk_error_dir . $data_key);
                return false;
            }

            // check complete, move to waitting dir
            $disk->move($data_key, $this->disk_waitting_dir . $data_key);

            return $disk_balance;
        }

        // get waiting disk
        $disk = $this->getDisk($this->disk_waitting_dir . $data_key, true);

        if(!$disk){
            return false;
        }

        // parse data record
        $transactions = ['start' => [], 'play' => []];
        $split_data = preg_split('/\s/', $disk->get($this->disk_waitting_dir . $data_key));
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
            error_log(__FUNCTION__ . '|' . $data_key . '| unknown start');
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
                'betAmount' => $nextx->betAmount,
                'winAmount' => $nextx->winAmount,
                'round'     => $nextx->roundId,
                'timestamp' => time(),
            ];
            $wallet_result = self::call_wallet('play', $wallet_input);
            if(!isset($wallet_result->statusCode) || $wallet_result->statusCode != 0){
                error_log(__FUNCTION__ . '|' . $data_key . '| call wallet failed' . $nextx->transactionId);
                $retrys[] = $nextx;
                continue;
            }
            error_log(__FUNCTION__ . '|' . $data_key . '| tx success => ' . $nextx->transactionId);
            $nextUnlockToken = $wallet_result->nextUnlockToken;
        };

        $wallet_result = self::call_wallet('balance', ['timestamp' => time(), 'walletAddress' => $walletAddress, 'unlockToken' => $nextUnlockToken]);

        if(!isset($wallet_result->statusCode) || $wallet_result->statusCode != 0){
            error_log(__FUNCTION__ . '|' . $data_key . '| call wallet failed | when init');
            $last_balance = 0;
        } else {
            $last_balance = $wallet_result->balance;
            $disk->append($this->disk_waitting_dir . $data_key, json_encode([
                'balance' => $last_balance, 
                'retrys' => $retrys, 
                ]));            
        }

        // move to finished
        $disk->move($this->disk_waitting_dir . $data_key, $this->disk_finished_dir . date("Ymd"). '/' . $data_key . '_' . time());

        // move to retry
        if(!empty($retrys)) {
            $this->initDisk($this->disk_retry_dir . $data_key, 
                $last_balance, $nextUnlockToken, $walletAddress);

            foreach($retrys as $key => $value) {
                $disk->append($this->disk_retry_dir . $data_key, json_encode([
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
