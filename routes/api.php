<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\GroupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//////////////////////////////USERS//////////////////////////////
Route::post('/register',[UserController::class,'register']);
Route::post('/login',[UserController::class,'login']);
Route::post('/logout',[UserController::class,'logout']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
     Route::get('/allUserGroups', [UserController::class, 'allUserGroups']);
     Route::get('/allUserOwnedGroups', [UserController::class, 'allUserOwnedGroups']);
     Route::get('/allUserFiles', [UserController::class, 'allUserFiles']);
 });
//////////////////////////////GROUPS//////////////////////////////
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
Route::post('/creatGroup',[GroupController::class,'creatGroup'])->middleware('CheckGroupName');
Route::delete('/deleteGroup',[GroupController::class,'deleteGroup'])->middleware('FileReserved');
Route::get('/allGroupFiles',[GroupController::class,'allGroupFiles']);
Route::get('/groupUsers',[GroupController::class,'groupUsers']);
Route::get('/allUserGroup',[GroupController::class,'allUserGroup']);
Route::post('/addUserToGroup',[GroupController::class,'addUserToGroup'])->middleware('CheckGroupOwner');
//Route::delete('/deleteUserFromGroup',[GroupController::class,'deleteUserFromGroup'])->middleware(['CheckGroupOwner','CheckMember','FileReserved']);
Route::delete('/deleteUserFromGroup',[GroupController::class,'deleteUserFromGroup']);

});



//////////////////////////////FILES//////////////////////////////
Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/uploadFileToGroup',[FileController::class,'uploadFileToGroup']);
    Route::post('/downloadFile',[FileController::class,'downloadFile'])->middleware(['CheckMember','FileReserved']);
    Route::post('/getFile',[FileController::class,'getFile']);
    Route::delete('/deleteFile',[FileController::class,'deleteFile'])->middleware(['CheckFileOwner','FileReserved']);
    Route::post('/updateFileAfterCheckOut',[FileController::class,'updateFileAfterCheckOut'])->middleware(['CheckMember','FileReserved']);
    Route::post('/checkIn',[FileController::class,'checkIn'])->middleware(['CheckMember','FileReserved']);
    Route::post('/checkOut',[FileController::class,'checkOut']);
    Route::post('/bulkCheckIn',[FileController::class,'bulkCheckIn'])->middleware(['CheckMember','FileReserved']);

});
