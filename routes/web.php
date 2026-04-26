<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Models\Product;


Route::get('/products', [ProductController::class, 'index'])->name('products');

Route::middleware('visitor')->group(function () {
    // Route::get('/', function () {
    //     $latestProducts = Product::orderBy('created_at', 'desc')->take(5)->get();

    //     return view('index', compact('latestProducts'));
    // })->name('home');
    Route::view('/about', 'about')->name('about');
    Route::view('/contact', 'contact')->name('contact');
    Route::get('/products', [ProductController::class, 'index'])->name('products');

});
