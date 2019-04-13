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

// Route::prefix('v1')->group(function () {
// Route::prefix('v1')->post('/getTempWalletBalance', 'GameController@getTempWalletBalance');	

Route::prefix('v1')->middleware('jsonapi')->group(function () {
	Route::get('/genUserSig', 'GameController@genUserSig');

	Route::post('/getTempWalletBalance', 'GameController@getTempWalletBalance');	

	Route::post('/start', 'GameController@start');

	Route::post('/play', 'GameController@play');

	Route::post('/balance', 'GameController@balance');

	Route::post('/credit', 'GameController@credit');

	Route::post('/end', 'GameController@end');

	Route::post('/revoke', 'GameController@revoke');


});

// Route::get('articles', function() {
//     // If the Content-Type and Accept headers are set to 'application/json', 
//     // this will return a JSON structure. This will be cleaned up later.
//     return Article::all();
// });
 
// Route::get('articles/{id}', function($id) {
//     return Article::find($id);
// });

// Route::post('articles', function(Request $request) {
//     return Article::create($request->all);
// });

// Route::put('articles/{id}', function(Request $request, $id) {
//     $article = Article::findOrFail($id);
//     $article->update($request->all());

//     return $article;
// });

// Route::delete('articles/{id}', function($id) {
//     Article::find($id)->delete();

//     return 204;
// })