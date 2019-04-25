<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        // return parent::render($request, $exception);
        $statusCode = 1;
        $status = '';


        // if ($exception->validator->fails()) {

        //     $errors = $exception->validator->errors()->getMessages();


        //     $failedRules = $exception->validator->failed();

        //     // echo "<pre>";print_r($failedRules);echo "</pre>";exit;

        //     /********************************START : Common Rules*************************************/
        //     /*
        //     hash
        //     */
        //     if(isset($failedRules['hash']) && isset($failedRules['hash']['Required'])) {
        //         $statusCode = 2;
        //         $status = $errors['hash'][0];
        //     }

        //     /*
        //     timestamp
        //     */
        //     if(isset($failedRules['timestamp']) && isset($failedRules['timestamp']['Required'])) {
        //         $statusCode = 2;
        //         $status = $errors['timestamp'][0];
        //     }
        //     elseif(isset($failedRules['timestamp']) && isset($failedRules['timestamp']['Isvalidtimestamp'])){
        //         $statusCode = 1;
        //         $status = $errors['timestamp'][0];
        //     }

        //     /*
        //     session id
        //     */
        //     if(isset($failedRules['sessionId']) && isset($failedRules['sessionId']['Required'])) {
        //         $statusCode = 2;
        //         $status = $errors['sessionId'][0];
        //     }
        //     elseif(isset($failedRules['sessionId']) && isset($failedRules['sessionId']['String'])){
        //         $statusCode = 1;
        //         $status = $errors['sessionId'][0];
        //     }
        //     elseif(isset($failedRules['sessionId']) && isset($failedRules['sessionId']['Max'])){
        //         $statusCode = 1;
        //         $status = $errors['sessionId'][0];
        //     }

        //     /*
        //     partnerPlayerId
        //     */
        //     if(isset($failedRules['partnerPlayerId']) && isset($failedRules['partnerPlayerId']['Required'])) {
        //         $statusCode = 2;
        //         $status = $errors['partnerPlayerId'][0];
        //     }
        //     elseif(isset($failedRules['partnerPlayerId']) && isset($failedRules['partnerPlayerId']['String'])){
        //         $statusCode = 1;
        //         $status = $errors['partnerPlayerId'][0];
        //     }

        //     /*
        //     currency
        //     */
        //     if(isset($failedRules['currency']) && isset($failedRules['currency']['Required'])) {
        //         $statusCode = 2;
        //         $status = $errors['currency'][0];
        //     }
        //     elseif(isset($failedRules['currency']) && isset($failedRules['currency']['String'])){
        //         $statusCode = 1;
        //         $status = $errors['currency'][0];
        //     }

        //     /*
        //     gameId
        //     */
        //     if(isset($failedRules['gameId']) && isset($failedRules['gameId']['Required'])) {
        //         $statusCode = 2;
        //         $status = $errors['gameId'][0];
        //     }
        //     elseif(isset($failedRules['gameId']) && isset($failedRules['gameId']['String'])){
        //         $statusCode = 1;
        //         $status = $errors['gameId'][0];
        //     }

        //     /*
        //     token
        //     */
        //     if(isset($failedRules['token']) && isset($failedRules['token']['String'])){
        //         $statusCode = 1;
        //         $status = $errors['token'][0];
        //     }

        //     /*
        //     operatorName
        //     */
        //     if(isset($failedRules['operatorName']) && isset($failedRules['operatorName']['String'])){
        //         $statusCode = 1;
        //         $status = $errors['operatorName'][0];
        //     }

        //     /*
        //     action
        //     */
        //     if(isset($failedRules['action']) && isset($failedRules['action']['Required'])) {
        //         $statusCode = 2;
        //         $status = $errors['action'][0];
        //     }
        //     elseif(isset($failedRules['action']) && isset($failedRules['action']['String'])){
        //         $statusCode = 1;
        //         $status = $errors['action'][0];
        //     }


        //     /*
        //     playerIp
        //     */
        //     if(isset($failedRules['playerIp']) && isset($failedRules['playerIp']['Required'])) {
        //         $statusCode = 2;
        //         $status = $errors['playerIp'][0];
        //     }elseif(isset($failedRules['playerIp']) && isset($failedRules['playerIp']['Ip'])){
        //         $statusCode = 4;
        //         $status = $errors['playerIp'][0];
        //     }

        //     /********************************END : Common Rules*************************************/

        //     /********************************START : Start Rules*************************************/
        //     // if($request->is('*/start')){

        //     // }
        //     /********************************END : Start Rules*************************************/

        //     /********************************START : Play Rules*************************************/
            
        //     if($request->is('*/play')){
            
        //         /*
        //         transactionId
        //         */
        //         if(isset($failedRules['transactionId']) && isset($failedRules['transactionId']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['transactionId'][0];
        //         }elseif(isset($failedRules['transactionId']) && isset($failedRules['transactionId']['String'])){
        //             $statusCode = 1;
        //             $status = $errors['transactionId'][0];
        //         }

        //         /*
        //         betAmount
        //         */
        //         if(isset($failedRules['betAmount']) && isset($failedRules['betAmount']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['betAmount'][0];
        //         }elseif(isset($failedRules['betAmount']) && isset($failedRules['betAmount']['Numeric'])){
        //             $statusCode = 1;
        //             $status = $errors['betAmount'][0];
        //         }

        //         /*
        //         winAmount
        //         */
        //         if(isset($failedRules['winAmount']) && isset($failedRules['winAmount']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['winAmount'][0];
        //         }elseif(isset($failedRules['winAmount']) && isset($failedRules['winAmount']['Numeric'])){
        //             $statusCode = 1;
        //             $status = $errors['winAmount'][0];
        //         }

        //         /*
        //         selections
        //         */
        //         if(isset($failedRules['selections']) && isset($failedRules['selections']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['selections'][0];
        //         }elseif(isset($failedRules['selections']) && isset($failedRules['selections']['Integer'])){
        //             $statusCode = 1;
        //             $status = $errors['selections'][0];
        //         }

        //         /*
        //         betPerSelection
        //         */
        //         if(isset($failedRules['betPerSelection']) && isset($failedRules['betPerSelection']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['betPerSelection'][0];
        //         }elseif(isset($failedRules['betPerSelection']) && isset($failedRules['betPerSelection']['Numeric'])){
        //             $statusCode = 1;
        //             $status = $errors['betPerSelection'][0];
        //         }

        //         /*
        //         freeGames
        //         */
        //         if(isset($failedRules['freeGames']) && isset($failedRules['freeGames']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['freeGames'][0];
        //         }elseif(isset($failedRules['freeGames']) && isset($failedRules['freeGames']['Boolean'])){
        //             $statusCode = 1;
        //             $status = $errors['freeGames'][0];
        //         }

        //         /*
        //         round
        //         */
        //         if(isset($failedRules['round']) && isset($failedRules['round']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['round'][0];
        //         }elseif(isset($failedRules['round']) && isset($failedRules['round']['Integer'])){
        //             $statusCode = 1;
        //             $status = $errors['round'][0];
        //         }

        //         /*
        //         roundsRemaining
        //         */
        //         if(isset($failedRules['roundsRemaining']) && isset($failedRules['roundsRemaining']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['roundsRemaining'][0];
        //         }elseif(isset($failedRules['roundsRemaining']) && isset($failedRules['roundsRemaining']['Integer'])){
        //             $statusCode = 1;
        //             $status = $errors['roundsRemaining'][0];
        //         }

        //     }
            

        //     /********************************END : Play Rules*************************************/


        //     /********************************START : Balance Rules*************************************/
        //     // if($request->is('*/balance')){

        //     // }
        //     /********************************END : Balance Rules*************************************/


        //     /********************************START : Credit Rules*************************************/
        //     if($request->is('*/credit')){
        //         /*
        //         transactionId
        //         */
        //         if(isset($failedRules['transactionId']) && isset($failedRules['transactionId']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['transactionId'][0];
        //         }elseif(isset($failedRules['transactionId']) && isset($failedRules['transactionId']['String'])){
        //             $statusCode = 1;
        //             $status = $errors['transactionId'][0];
        //         }

        //         /*
        //         amount
        //         */
        //         if(isset($failedRules['amount']) && isset($failedRules['amount']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['amount'][0];
        //         }elseif(isset($failedRules['amount']) && isset($failedRules['amount']['Numeric'])){
        //             $statusCode = 1;
        //             $status = $errors['amount'][0];
        //         }

        //         /*
        //         type
        //         */
        //         if(isset($failedRules['type']) && isset($failedRules['type']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['type'][0];
        //         }elseif(isset($failedRules['type']) && isset($failedRules['type']['String'])){
        //             $statusCode = 1;
        //             $status = $errors['type'][0];
        //         }

        //         /*
        //         creditIndex
        //         */
        //         if(isset($failedRules['creditIndex']) && isset($failedRules['creditIndex']['Integer'])){
        //             $statusCode = 1;
        //             $status = $errors['creditIndex'][0];
        //         }

        //     }
        //     /********************************END : Credit Rules*************************************/


        //     /********************************START : End Rules*************************************/
        //     // if($request->is('*/end')){

        //     // }
        //     /********************************END : End Rules*************************************/


        //     /********************************START : Revoke Rules*************************************/
        //     if($request->is('*/revoke')){
        //         /*
        //         transactionId
        //         */
        //         if(isset($failedRules['transactionId']) && isset($failedRules['transactionId']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['transactionId'][0];
        //         }elseif(isset($failedRules['transactionId']) && isset($failedRules['transactionId']['String'])){
        //             $statusCode = 1;
        //             $status = $errors['transactionId'][0];
        //         }

        //         /*
        //         round
        //         */
        //         if(isset($failedRules['round']) && isset($failedRules['round']['Required'])) {
        //             $statusCode = 2;
        //             $status = $errors['round'][0];
        //         }elseif(isset($failedRules['round']) && isset($failedRules['round']['Integer'])){
        //             $statusCode = 1;
        //             $status = $errors['round'][0];
        //         }
        //     }
        //     /********************************END : Revoke Rules*************************************/

            
        // }


        $json = [
            'status' => $status,
            'statusCode' => $statusCode,
            'errorMessage' => '',
            'userMessage' => ''
        ];


        return response()->json($json, 400);
        
    }
}
