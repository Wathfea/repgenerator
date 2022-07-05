<?php

use Illuminate\Support\Facades\Route;
use Pentacom\Repgenerator\Http\Controllers\RepgeneratorController;

Route::get('repgenerator/tables', [RepgeneratorController::class, 'getTables'])->name('repgenerator.tables');
Route::post('repgenerator/generate', [RepgeneratorController::class, 'generate'])->name('repgenerator.generate');
