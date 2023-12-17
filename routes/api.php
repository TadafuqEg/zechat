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
    Route::post('/user/login','Api\User\AuthController@login');
    Route::post('/user/register','Api\User\AuthController@register');

    Route::group(['prefix' => 'user','namespace' => 'Api\User' , 'middleware' => ['auth:api','user-middleware']], function () {
        Route::get('info','AuthController@userInfo');
        Route::post('change-password','AuthController@changePassword');
        Route::get('unfriends-list','FriendController@unfriendsList');
        Route::get('friends-list','FriendController@friendsList');
        Route::get('received-friend-requests-list','FriendController@friendRequestsReceivedList');
        Route::get('sent-friend-requests-list','FriendController@SentFriendRequestsList');
        Route::post('send-friend-request','FriendController@friendRequest');
        Route::post('accept-or-reject-friend-request/{id}','FriendController@AcceptOrRejectFriendRequest');
        Route::get('search','FriendController@search');

        Route::get('get-messages/{userId}','MessageController@getMessages');
        Route::post('send-message','MessageController@sendMessage');
        
        // friendRequest
        // AcceptOrRejectFriendRequest
    });
});
