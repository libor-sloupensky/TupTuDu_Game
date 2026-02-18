<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QuizController extends Controller
{
    /**
     * Generate a random math question (addition/subtraction up to 100).
     */
    public function question(): JsonResponse
    {
        $operation = rand(0, 1) === 0 ? '+' : '-';

        if ($operation === '+') {
            $a = rand(1, 99);
            $b = rand(1, 100 - $a);
            $correct = $a + $b;
        } else {
            $a = rand(2, 100);
            $b = rand(1, $a - 1);
            $correct = $a - $b;
        }

        $questionId = base64_encode(json_encode([
            'a' => $a,
            'b' => $b,
            'op' => $operation,
            'correct' => $correct,
            'ts' => time(),
        ]));

        return response()->json([
            'question_id' => $questionId,
            'question' => "{$a} {$operation} {$b} = ?",
            'a' => $a,
            'b' => $b,
            'operation' => $operation,
        ]);
    }

    /**
     * Check an answer.
     */
    public function answer(Request $request): JsonResponse
    {
        $request->validate([
            'question_id' => 'required|string',
            'answer' => 'required|integer',
        ]);

        $decoded = json_decode(base64_decode($request->question_id), true);

        if (!$decoded || !isset($decoded['correct'])) {
            return response()->json(['error' => 'Invalid question_id'], 400);
        }

        $isCorrect = (int) $request->answer === (int) $decoded['correct'];

        return response()->json([
            'correct' => $isCorrect,
            'expected' => $decoded['correct'],
            'your_answer' => (int) $request->answer,
        ]);
    }
}
