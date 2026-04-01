<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PostController;
use function Ramsey\Uuid\v1;
use App\Http\Controllers\Api\V1\PromptGenerationController;
use App\Http\Controllers\Api\V1\UserController;
 
Route::middleware(['auth:sanctum','throttle:api'])->group(function () {
    // Route::get('/user', function (Request $request) {
    //     return $request->user();
    // });
        Route::prefix('v1')->group(function(){
    // Route::apiResource('posts',PostController::class);
   
    Route::apiResource('prompt-generations', PromptGenerationController::class)
    ->only(['index', 'store', 'destroy']);
    
    //User Controller routes
    Route::apiResource('user',UserController::class) 
    ->only(['show','update','destroy']);
});


    
});



require __DIR__.'/auth.php';
