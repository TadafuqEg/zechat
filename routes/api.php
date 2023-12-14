<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['middleware' => ['json.response','cors']], function () {
    Route::post('/admin/login','Api\Admin\AuthController@login');
    Route::post('/user/login','Api\User\AuthController@login');
    Route::group(['prefix' => 'dashboard','namespace' => 'Api\Admin' , 'middleware' => ['auth:api','admin-middleware']], function () {
        Route::resource('users','UserController')->except(['create','edit']);;
        Route::post('users/update/{user}','UserController@update');
        // Route::post('users/generate-update-code/{id}','UserController@generateUpdateCode');
        Route::post('change-password','AuthController@changePassword');

    });


    Route::group(['prefix' => 'user','namespace' => 'Api\User' , 'middleware' => ['auth:api','user-middleware']], function () {
        Route::get('info','AuthController@userInfo');
        Route::post('change-password','AuthController@changePassword');
        
    });
});
