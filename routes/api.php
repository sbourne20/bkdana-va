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



Route::group(['middleware' => ['client']], function () {
    //Route::get('bca','Api\BcaController@index');

    Route::post('va/bills','Api\BcaController@bills');
    Route::post('va/payments','Api\BcaController@payments');
});

Route::get('server','SoapController@index');
Route::post('server','SoapController@index');
Route::get('client','SoapController@client');


