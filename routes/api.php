<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::group(["controller" => ProductController::class],function(){
    
    Route::get("/products/{id}","getProduct");

    Route::post("/holds","createHold");

    Route::post("/orders","createOrder");

    Route::post("/payments/webhook/","handlePaymentWebhook");

});