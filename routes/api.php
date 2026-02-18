<?php

use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json(['status' => 'ok', 'app' => 'TupTuDu Game']);
});

Route::get('/quiz/question', [QuizController::class, 'question']);
Route::post('/quiz/answer', [QuizController::class, 'answer']);
