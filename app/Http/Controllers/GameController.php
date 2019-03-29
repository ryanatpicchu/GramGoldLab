<?php

namespace App\Http\Controllers;

use App\Http\Requests\StartRequest;
use App\Http\Requests\PlayRequest;
use App\Http\Requests\BalanceRequest;
use App\Http\Requests\CreditRequest;
use App\Http\Requests\EndRequest;
use App\Http\Requests\RevokeRequest;

use App\Http\Requests\CommonRule;
use Illuminate\Support\Facades\Input;

class GameController extends Controller
{
    use CommonRule;
    /**
     * Start game
     *
     * @return \Illuminate\Http\Response
     */
    public function start(StartRequest $request)
    {

        if($request->isRequestValid()){
            

            $extra_response = array(
                'playerId' => $this->testFields()['playerId'],
                'sessionId' => Input::get('sessionId'),
                'balance' => $this->testFields()['balance'],
                'balanceSequence' => '',
                'currency' => Input::get('currency'),
                'betLimitId' => '',
                'sessionRTP' => ''
            );
            return $this->validateSuccess($extra_response);
        }
        
    }

    public function play(PlayRequest $request)
    {
        if($request->isRequestValid()){
            

            $extra_response = array(
                'balance' => $this->testFields()['balance'],
                'balanceSequence' => ''
            );
            return $this->validateSuccess($extra_response);
        }
    }

    public function balance(BalanceRequest $request)
    {
        if($request->isRequestValid()){
            

            $extra_response = array(
                'balance' => $this->testFields()['balance'],
                'balanceSequence' => ''
            );
            return $this->validateSuccess($extra_response);
        }
    }

    public function credit(CreditRequest $request)
    {
        if($request->isRequestValid()){
            

            $extra_response = array(
                'balance' => $this->testFields()['balance'],
                'balanceSequence' => ''
            );
            return $this->validateSuccess($extra_response);
        }
    }

    public function end(EndRequest $request)
    {
        if($request->isRequestValid()){
            
            return $this->validateSuccess();
        }
    }

    public function revoke(RevokeRequest $request)
    {
        if($request->isRequestValid()){
            

            $extra_response = array(
                'balance' => $this->testFields()['balance'],
                'balanceSequence' => ''
            );
            return $this->validateSuccess($extra_response);
        }
    }

}
