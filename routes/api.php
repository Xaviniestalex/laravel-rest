<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\PostsApiController;
use App\Http\Controllers\RedeemController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('custom-login', [AuthController::class, 'customLogin'])->name('login.custom');
Route::get('registration', [AuthController::class, 'registration'])->name('register-user');
Route::post('custom-registration', [AuthController::class, 'customRegistration'])->name('register.custom');
Route::get('signout', [AuthController::class, 'signOut'])->name('signout');

Route::group(['middleware' => ['web']], function () {
    Route::get('login', [AuthController::class, 'index'])->name('login');
});

Route::post('add-point', [PointController::class, 'addPointByLuckyDraw']);

Route::get('/coupons', [CouponController::class, 'getAll']);
Route::post('/coupon', [CouponController::class, 'create']);
Route::put('/coupon/{id}', [CouponController::class, 'update']);

Route::post('/redeem', [RedeemController::class, 'create']);
