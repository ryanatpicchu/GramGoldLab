<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;

class OperatorController extends Controller
{   
    
    public function __construct()
    {
        
    }

    public function getGGCHash(Request $request){
        $input = $request->all();

        if( !isset($input['partner']) || empty($input['partner']) ){
            exit;
        }

        $required_key = $this->ggcRequiredKey();

        $hash = hash_hmac('SHA256', json_encode($required_key), $input['partner']);
        $t = time();

        echo json_encode(array('hash'=>$hash, 't'=>$t, 'partner'=>$input['partner']));
    }

    private function ggcRequiredKey(){
        $ggc_key = 'gramgoldlab888_test_key';
        $ggc_secret = 'gramgoldlab888_test_secret';

        return array('key'=>$ggc_key, 'secret'=>$ggc_secret);
    }

}
