<?php

use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('send_otp',[UserController::class,'send_otp']);
Route::post('verify_otp',[UserController::class,'verify_otp']);
Route::post('complete_profile',[UserController::class,'complete_profile']);
Route::get('get_user_profile/{userId}',[UserController::class,'get_user_profile']);
Route::post('get_nearby_store_videos',[UserController::class,'get_nearby_store_videos']);
Route::post('get_nearby_stores',[UserController::class,'get_nearby_stores']);
Route::get('get_video_comments/{postId}',[UserController::class,'get_video_comments']);
Route::get('get_store_details/{storeId}',[UserController::class,'get_store_details']);
Route::get('get_store_content/{storeId}',[UserController::class,'get_store_content']);
Route::post('like_video',[UserController::class,'like_video']);
Route::post('add_comment',[UserController::class,'add_comment']);


Route::post('send_store_otp',[StoreController::class,'send_store_otp']);
Route::post('verify_store_otp',[StoreController::class,'verify_store_otp']);
Route::post('complete_store_profile',[StoreController::class,'complete_store_profile']);
Route::get('get_store_profile/{storeId}',[StoreController::class,'get_store_profile']);
Route::get('get_content_store/{storeId}',[StoreController::class,'get_content_store']);
Route::post('update_store',[StoreController::class,'update_store']);
Route::post('create_post',[StoreController::class,'create_post']);
Route::get('delete_post/{postId}',[StoreController::class,'delete_post']);
Route::post('update_store_location',[StoreController::class,'update_store_location']);