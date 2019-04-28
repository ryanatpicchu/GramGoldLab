<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->middleware('jsonapi')->group(function () {
	Route::post('/genTempHash', 'GameController@genTempHash');

	Route::post('/setSecureToken', 'GameController@setSecureToken');

	Route::post('/unlockTokenToSignature', 'GameController@unlockTokenToSignature');	
	
	Route::post('/getTempWalletBalance', 'GameController@getTempWalletBalance');	

	Route::post('/start', 'GameController@start');

	Route::post('/play', 'GameController@play');

	Route::post('/balance', 'GameController@balance');

	Route::post('/credit', 'GameController@credit');

	Route::post('/end', 'GameController@end');

	Route::post('/revoke', 'GameController@revoke');

	Route::post('/testPlay', 'GameController@testPlay');

	Route::post('/testEnd', 'GameController@testEnd');
});
