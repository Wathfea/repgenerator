<?php

use Illuminate\Support\Facades\Route;
use Pentacom\Repgenerator\Http\Controllers\RepgeneratorController;

Route::get('repgenerator/tables', [RepgeneratorController::class, 'getTables'])->name('repgenerator.tables');
Route::get('repgenerator/isTableExist/{table}', [RepgeneratorController::class, 'isTableExists'])->name('repgenerator.is-table-exists');
Route::get('repgenerator/getGeneratedDomains', [RepgeneratorController::class, 'getGeneratedDomains'])->name('repgenerator.get-domains');
Route::post('repgenerator/generate', [RepgeneratorController::class, 'generate'])->name('repgenerator.generate');
Route::post('repgenerator/reGenerate', [RepgeneratorController::class, 'reGenerate'])->name('repgenerator.re-generate');
