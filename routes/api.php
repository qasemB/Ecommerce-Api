<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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


Route::group(['prefix'=>'auth'] , function(){

    Route::post('login' , 'AuthController@login');
    Route::post('register' , 'AuthController@register');

    Route::group(["middleware" => 'auth:api'],function(){

        Route::get('user', 'AuthController@getUser');
        Route::get('logout' , 'AuthController@logout');
    });

});

Route::group(['prefix'=>'admin'] , function(){

    Route::group(["middleware" => 'auth:api'],function(){

        Route::get('categories', 'CategoryController@index');
        Route::post('categories', 'CategoryController@store');
        Route::get('categories/{id}', 'CategoryController@show');
        Route::put('categories/{id}', 'CategoryController@update');
        Route::delete('categories/{id}', 'CategoryController@destroy');

    });

});
