<?php

use Illuminate\Support\Facades\Route;
use Pentacom\Repgenerator\Http\Controllers\RepgeneratorController;

Route::get('repgenerator/checkFrontendVersion', [RepgeneratorController::class, 'checkFrontendVersion'])->name('repgenerator.check-frontend-version');
Route::get('repgenerator/tables', [RepgeneratorController::class, 'getTables'])->name('repgenerator.tables');
Route::get('repgenerator/validateTable/{table}', [RepgeneratorController::class, 'validateTable'])->name('repgenerator.validate-table');
Route::get('repgenerator/getGeneratedDomains', [RepgeneratorController::class, 'getGeneratedDomains'])->name('repgenerator.get-domains');
Route::post('repgenerator/generate', [RepgeneratorController::class, 'generate'])->name('repgenerator.generate');
Route::post('repgenerator/reGenerate', [RepgeneratorController::class, 'reGenerate'])->name('repgenerator.re-generate');
Route::post('repgenerator/generateGradient', [RepgeneratorController::class, 'generateGradient'])->name('repgenerator.generate-gradient');
