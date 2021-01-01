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

//Auth::routes();
Auth::routes(['register' => false]);

Route::group([
        'as' => 'app.',
        'middleware' => 'auth:web'
    ], function() {

    Route::group([
        'as' => 'products.',
        'prefix' => '/'
    ], function() {
        Route::get('/', 'ProductController@index')->name('index');
        Route::get('data', 'ProductController@data');
        Route::get('{id}', 'ProductController@info');
        Route::get('detail/{id}', 'ProductController@detail');
        Route::post('command/{id}', 'ProductController@command');
        Route::post('create', 'ProductController@create');
        Route::post('update/{id}', 'ProductController@update');
        Route::get('delete/{id}', 'ProductController@delete');
    });

});
