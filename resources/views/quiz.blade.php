<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TupTuDu - Matematický kvíz</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .game-container {
            background: white;
            border-radius: 24px;
            padding: 40px;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }

        h1 {
            font-size: 28px;
            color: #333;
            margin-bottom: 8px;
        }

        .score-bar {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin: 16px 0 32px;
            font-size: 18px;
            color: #666;
        }

        .score-bar .correct { color: #22c55e; font-weight: bold; }
        .score-bar .wrong { color: #ef4444; font-weight: bold; }

        .question {
            font-size: 48px;
            font-weight: bold;
            color: #1a1a2e;
            margin: 24px 0;
            min-height: 60px;
        }

        .answer-input {
            font-size: 36px;
            text-align: center;
            width: 180px;
            padding: 12px;
            border: 3px solid #ddd;
            border-radius: 16px;
            outline: none;
            transition: border-color 0.2s;
        }

        .answer-input:focus {
            border-color: #667eea;
        }

        .answer-input.correct {
            border-color: #22c55e;
            background: #f0fdf4;
        }

        .answer-input.wrong {
            border-color: #ef4444;
            background: #fef2f2;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 14px 40px;
            font-size: 20px;
            font-weight: bold;
            color: white;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 16px;
            cursor: pointer;
            transition: transform 0.1s, box-shadow 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102,126,234,0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .feedback {
            font-size: 24px;
            margin-top: 16px;
            min-height: 36px;
            font-weight: bold;
        }

        .feedback.correct { color: #22c55e; }
        .feedback.wrong { color: #ef4444; }

        .streak {
            font-size: 14px;
            color: #999;
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="game-container">
        <h1>TupTuDu Kvíz</h1>
        <p style="color: #888; font-size: 14px;">Sčítání a odčítání do 100</p>

        <div class="score-bar">
            <span>Správně: <span class="correct" id="correctCount">0</span></span>
            <span>Špatně: <span class="wrong" id="wrongCount">0</span></span>
        </div>

        <div class="question" id="question">Načítám...</div>

        <div>
            <input type="number" class="answer-input" id="answerInput" autofocus
                   placeholder="?" min="0" max="200">
        </div>

        <button class="btn" id="submitBtn" onclick="submitAnswer()">Odpovědět</button>

        <div class="feedback" id="feedback"></div>
        <div class="streak" id="streak"></div>
    </div>

    <script>
        let currentQuestion = null;
        let correctCount = 0;
        let wrongCount = 0;
        let streakCount = 0;
        let waiting = false;

        async function loadQuestion() {
            waiting = false;
            document.getElementById('feedback').textContent = '';
            document.getElementById('feedback').className = 'feedback';
            const input = document.getElementById('answerInput');
            input.value = '';
            input.className = 'answer-input';
            input.disabled = false;
            input.focus();

            try {
                const res = await fetch('/api/quiz/question');
                const data = await res.json();
                currentQuestion = data;
                document.getElementById('question').textContent = data.question;
            } catch (e) {
                document.getElementById('question').textContent = 'Chyba načítání...';
            }
        }

        async function submitAnswer() {
            if (waiting || !currentQuestion) return;

            const input = document.getElementById('answerInput');
            const answer = parseInt(input.value);

            if (isNaN(answer)) {
                input.focus();
                return;
            }

            waiting = true;
            input.disabled = true;

            try {
                const res = await fetch('/api/quiz/answer', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        question_id: currentQuestion.question_id,
                        answer: answer,
                    }),
                });
                const data = await res.json();
                const feedback = document.getElementById('feedback');

                if (data.correct) {
                    correctCount++;
                    streakCount++;
                    feedback.textContent = 'Správně!';
                    feedback.className = 'feedback correct';
                    input.className = 'answer-input correct';
                } else {
                    wrongCount++;
                    streakCount = 0;
                    feedback.textContent = `Špatně! Správná odpověď: ${data.expected}`;
                    feedback.className = 'feedback wrong';
                    input.className = 'answer-input wrong';
                }

                document.getElementById('correctCount').textContent = correctCount;
                document.getElementById('wrongCount').textContent = wrongCount;
                document.getElementById('streak').textContent =
                    streakCount > 1 ? `Série: ${streakCount} správně za sebou!` : '';

                setTimeout(loadQuestion, data.correct ? 800 : 2000);

            } catch (e) {
                document.getElementById('feedback').textContent = 'Chyba spojení';
                waiting = false;
                input.disabled = false;
            }
        }

        document.getElementById('answerInput').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') submitAnswer();
        });

        loadQuestion();
    </script>
    <div style="position:fixed;bottom:8px;right:12px;font-size:11px;color:rgba(255,255,255,0.3);text-align:right;">
        {{ config('version.number') }}<br>{{ config('version.deployed_at') }}
    </div>
</body>
</html>
