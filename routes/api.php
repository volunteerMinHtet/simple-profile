<?php

use App\Http\Controllers\API\{AuthController};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use \Milon\Barcode\DNS1D;
use \Milon\Barcode\DNS2D;

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

Route::middleware('auth:sanctum')->get('/user-qrcode', function (Request $request) {
    $user = $request->user();

    return storage_path($user->qrcode);
});

Route::post('/create-account', [AuthController::class, 'createAccount']);
Route::post('/login', [AuthController::class, 'login']);


Route::get('/clean-storage-dir', function () {
    // Storage::deleteDirectory('backgrounds');

    return response()->json(['files' => Storage::disk('local')->allFiles()]);
});
