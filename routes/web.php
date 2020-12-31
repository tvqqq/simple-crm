<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();
//Auth::routes(['register' => false]);

Route::get('/home', 'HomeController@index')->name('home');

Route::group([
        'as' => 'app.',
        'middleware' => 'auth:web'
    ], function() {

    Route::group([
        'as' => 'products.',
        'prefix' => 'products'
    ], function() {
        Route::get('/', 'ProductController@index');
    });

    Route::group([
        'as' => 'imports.',
        'prefix' => 'imports'
    ], function() {
        Route::get('/', 'ImportController@index');
    });

    Route::group([
        'as' => 'exports.',
        'prefix' => 'exports'
    ], function() {
        Route::get('/', 'ExportController@index');
    });

    Route::group([
        'as' => 'returns.',
        'prefix' => 'returns'
    ], function() {
        Route::get('/', 'ReturnController@index');
    });
});
