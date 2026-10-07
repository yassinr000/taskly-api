<?php

use Illuminate\Support\Facades\Route;

// The API has no web pages: answer with a small JSON message
Route::get('/', function () {
    return response()->json(['name' => 'Taskly API', 'status' => 'ok']);
});