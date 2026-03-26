 <?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PostController;
use function Ramsey\Uuid\v1;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
 
// Route::get('/hello', function () {
//     return ["message" => "hello from api route"];

// });

Route::prefix('v1')->group(function(){
    Route::apiResource('posts',PostController::class);
});

