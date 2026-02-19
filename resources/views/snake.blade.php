<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TupTuDu - Násobkový had</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .game-header {
            text-align: center;
            margin-bottom: 16px;
        }

        .game-header h1 {
            font-size: 28px;
            margin-bottom: 4px;
        }

        .game-header .task {
            font-size: 20px;
            color: #fbbf24;
        }

        .info-bar {
            display: flex;
            gap: 32px;
            margin-bottom: 12px;
            font-size: 16px;
        }

        .info-bar .score { color: #4ade80; }
        .info-bar .length { color: #60a5fa; }
        .info-bar .lives { color: #f87171; }

        canvas {
            border: 2px solid #334155;
            border-radius: 8px;
            background: #0f172a;
        }

        .feedback {
            font-size: 22px;
            font-weight: bold;
            margin-top: 12px;
            min-height: 30px;
            transition: opacity 0.3s;
        }

        .feedback.correct { color: #4ade80; }
        .feedback.wrong { color: #f87171; }

        .game-over {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .game-over.hidden { display: none; }

        .game-over h2 {
            font-size: 36px;
            margin-bottom: 16px;
        }

        .game-over .final-score {
            font-size: 24px;
            color: #fbbf24;
            margin-bottom: 24px;
        }

        .btn {
            padding: 14px 40px;
            font-size: 18px;
            font-weight: bold;
            color: white;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 16px;
            cursor: pointer;
            text-decoration: none;
            margin: 6px;
        }

        .btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(102,126,234,0.4); }
        .btn-secondary { background: #475569; }

        .controls-hint {
            margin-top: 12px;
            font-size: 13px;
            color: #64748b;
        }

        .start-screen {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.85);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .start-screen.hidden { display: none; }

        .start-screen h2 {
            font-size: 32px;
            margin-bottom: 12px;
        }

        .start-screen p {
            font-size: 16px;
            color: #94a3b8;
            margin-bottom: 8px;
            max-width: 400px;
            text-align: center;
            line-height: 1.5;
        }

        .multiplier-display {
            font-size: 48px;
            color: #fbbf24;
            font-weight: bold;
            margin: 16px 0;
        }
    </style>
</head>
<body>
    <div class="game-header">
        <h1>Násobkový had</h1>
        <div class="task" id="task"></div>
    </div>

    <div class="info-bar">
        <span>Skóre: <span class="score" id="score">0</span></span>
        <span>Délka: <span class="length" id="snakeLength">4</span></span>
    </div>

    <canvas id="game" width="520" height="520"></canvas>

    <div class="feedback" id="feedback"></div>
    <div class="controls-hint">Ovládání: šipky nebo WASD</div>

    <div class="start-screen" id="startScreen">
        <h2>Násobkový had</h2>
        <p>Had loví násobky čísla. Sbírej správné násobky a vyhýbej se ostatním číslům!</p>
        <p>Správný násobek = had roste a dostaneš body.<br>Špatné číslo = had se zkrátí.</p>
        <div class="multiplier-display" id="startMultiplier"></div>
        <button class="btn" onclick="startGame()">Hrát!</button>
        <a href="/" class="btn btn-secondary">Zpět na menu</a>
    </div>

    <div class="game-over hidden" id="gameOver">
        <h2>Konec hry!</h2>
        <div class="final-score" id="finalScore"></div>
        <button class="btn" onclick="restartGame()">Hrát znovu</button>
        <a href="/" class="btn btn-secondary">Zpět na menu</a>
    </div>

    <script>
    const canvas = document.getElementById('game');
    const ctx = canvas.getContext('2d');

    const GRID = 26;
    const COLS = canvas.width / GRID;   // 20
    const ROWS = canvas.height / GRID;  // 20
    const NUM_ITEMS = 18;
    const BASE_SPEED = 15;

    let multiplier = 0;
    let score = 0;
    let gameRunning = false;
    let feedbackTimer = null;
    let frameCount = 0;
    let speed = BASE_SPEED; // frames between moves (lower = faster)
    let correctStreak = 0;

    const snake = {
        x: 0, y: 0,
        dx: GRID, dy: 0,
        cells: [],
        maxCells: 4
    };

    // Number items on the board
    let items = [];
    // Flash effects: { x, y, color, startTime }
    let flashes = [];

    function pickMultiplier() {
        multiplier = Math.floor(Math.random() * 9) + 2; // 2-10
        document.getElementById('task').textContent = `Sbírej násobky čísla ${multiplier}!`;
        document.getElementById('startMultiplier').textContent = `× ${multiplier}`;
    }

    function isMultiple(n) {
        return n > 0 && n % multiplier === 0;
    }

    function generateCorrect() {
        return multiplier * (Math.floor(Math.random() * 10) + 1);
    }

    function generateWrong() {
        let n;
        do {
            n = Math.floor(Math.random() * 100) + 1;
        } while (n % multiplier === 0);
        return n;
    }

    function generateNumber() {
        return Math.random() < 0.4 ? generateCorrect() : generateWrong();
    }

    function tooCloseToItem(x, y) {
        for (const item of items) {
            if (Math.abs(item.x - x) <= GRID && Math.abs(item.y - y) <= GRID) return true;
        }
        return false;
    }

    function findFreePos() {
        let x, y, attempts = 0;
        do {
            x = Math.floor(Math.random() * COLS) * GRID;
            y = Math.floor(Math.random() * ROWS) * GRID;
            attempts++;
        } while (attempts < 100 && (isOccupied(x, y) || isInDangerZone(x, y) || tooCloseToItem(x, y)));
        return { x, y };
    }

    function spawnItem() {
        const pos = findFreePos();
        return { ...pos, value: generateNumber() };
    }

    function spawnCorrectItem() {
        const pos = findFreePos();
        return { ...pos, value: generateCorrect() };
    }

    function spawnWrongItem() {
        const pos = findFreePos();
        return { ...pos, value: generateWrong() };
    }

    function removeRandomOfType(isCorrectType) {
        const candidates = [];
        for (let j = 0; j < items.length; j++) {
            if (isMultiple(items[j].value) === isCorrectType) candidates.push(j);
        }
        if (candidates.length === 0) return;
        const idx = candidates[Math.floor(Math.random() * candidates.length)];
        items.splice(idx, 1);
    }

    function isInDangerZone(x, y) {
        if (!gameRunning) return false;
        const relX = (x - snake.x) / GRID;
        const relY = (y - snake.y) / GRID;
        let ahead, side;
        if (snake.dx > 0)      { ahead = relX;  side = Math.abs(relY); }
        else if (snake.dx < 0) { ahead = -relX; side = Math.abs(relY); }
        else if (snake.dy > 0) { ahead = relY;  side = Math.abs(relX); }
        else if (snake.dy < 0) { ahead = -relY; side = Math.abs(relX); }
        else return false;
        if (ahead >= 0 && ahead <= 6 && side <= 4) return true;
        if (ahead < 0 && ahead >= -3 && side <= 4) return true;
        return false;
    }

    function isOccupied(x, y) {
        for (const cell of snake.cells) {
            if (cell.x === x && cell.y === y) return true;
        }
        for (const item of items) {
            if (item.x === x && item.y === y) return true;
        }
        return false;
    }

    function spawnItems() {
        items = [];
        for (let i = 0; i < NUM_ITEMS; i++) {
            items.push(spawnItem());
        }
    }

    function resetSnake() {
        snake.x = Math.floor(COLS / 2) * GRID;
        snake.y = Math.floor(ROWS / 2) * GRID;
        snake.dx = GRID;
        snake.dy = 0;
        snake.cells = [];
        snake.maxCells = 4;
    }

    function showFeedback(text, type) {
        const el = document.getElementById('feedback');
        el.textContent = text;
        el.className = 'feedback ' + type;
        clearTimeout(feedbackTimer);
        feedbackTimer = setTimeout(() => { el.textContent = ''; }, 1200);
    }

    function updateHUD() {
        document.getElementById('score').textContent = score;
        document.getElementById('snakeLength').textContent = snake.maxCells;
    }

    function gameOverCheck() {
        if (snake.maxCells <= 1) {
            gameRunning = false;
            document.getElementById('finalScore').textContent = `Skóre: ${score} | Násobky čísla ${multiplier}`;
            document.getElementById('gameOver').classList.remove('hidden');
        }
    }

    function loop() {
        requestAnimationFrame(loop);
        if (!gameRunning) return;

        if (++frameCount < speed) return;
        frameCount = 0;

        // Move snake
        snake.x += snake.dx;
        snake.y += snake.dy;

        // Wrap around
        if (snake.x < 0) snake.x = canvas.width - GRID;
        else if (snake.x >= canvas.width) snake.x = 0;
        if (snake.y < 0) snake.y = canvas.height - GRID;
        else if (snake.y >= canvas.height) snake.y = 0;

        snake.cells.unshift({ x: snake.x, y: snake.y });
        if (snake.cells.length > snake.maxCells) {
            snake.cells.pop();
        }

        // Clear
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Draw grid lines (subtle)
        ctx.strokeStyle = '#1e293b';
        ctx.lineWidth = 1;
        for (let i = 0; i <= COLS; i++) {
            ctx.beginPath();
            ctx.moveTo(i * GRID, 0);
            ctx.lineTo(i * GRID, canvas.height);
            ctx.stroke();
        }
        for (let i = 0; i <= ROWS; i++) {
            ctx.beginPath();
            ctx.moveTo(0, i * GRID);
            ctx.lineTo(canvas.width, i * GRID);
            ctx.stroke();
        }

        // Draw number items
        items.forEach(item => {
            ctx.fillStyle = '#1e3a5f';
            ctx.fillRect(item.x + 1, item.y + 1, GRID - 2, GRID - 2);

            ctx.fillStyle = '#e2e8f0';
            ctx.font = 'bold 14px Segoe UI, sans-serif';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(item.value, item.x + GRID / 2, item.y + GRID / 2);
        });

        // Draw snake
        snake.cells.forEach((cell, index) => {
            const headGlow = index === 0 ? '#22d3ee' : '#06b6d4';
            const bodyColor = index === 0 ? '#22d3ee' : '#0891b2';
            ctx.fillStyle = bodyColor;
            ctx.fillRect(cell.x + 1, cell.y + 1, GRID - 2, GRID - 2);

            if (index === 0) {
                ctx.shadowColor = '#22d3ee';
                ctx.shadowBlur = 8;
                ctx.fillStyle = headGlow;
                ctx.fillRect(cell.x + 2, cell.y + 2, GRID - 4, GRID - 4);
                ctx.shadowBlur = 0;
            }

            // Check collision with items (head only)
            if (index === 0) {
                for (let i = items.length - 1; i >= 0; i--) {
                    if (cell.x === items[i].x && cell.y === items[i].y) {
                        const eaten = items[i];
                        if (isMultiple(eaten.value)) {
                            // Correct! Grow
                            flashes.push({ x: eaten.x, y: eaten.y, color: 'green', startTime: Date.now() });
                            snake.maxCells += 1;
                            score += eaten.value;
                            correctStreak++;
                            if (correctStreak >= 3) {
                                speed = Math.max(4, Math.round(speed * 0.9));
                            }
                            showFeedback(`+${eaten.value} Správně!`, 'correct');
                            // Remove eaten correct, remove one wrong, add new correct + wrong
                            items.splice(i, 1);
                            removeRandomOfType(false);
                            items.push(spawnCorrectItem());
                            items.push(spawnWrongItem());
                        } else {
                            // Wrong! Shrink
                            flashes.push({ x: eaten.x, y: eaten.y, color: 'red', startTime: Date.now() });
                            snake.maxCells = Math.max(1, snake.maxCells - 1);
                            score = Math.max(0, score - 10);
                            correctStreak = 0;
                            speed = BASE_SPEED;
                            showFeedback(`Špatně! ${eaten.value} není násobek ${multiplier}`, 'wrong');
                            // Remove eaten wrong, remove one correct, add new wrong + correct
                            items.splice(i, 1);
                            removeRandomOfType(true);
                            items.push(spawnWrongItem());
                            items.push(spawnCorrectItem());
                        }
                        updateHUD();
                        gameOverCheck();
                        break;
                    }
                }
            }

            // Self-collision
            if (index === 0) {
                for (let i = 1; i < snake.cells.length; i++) {
                    if (cell.x === snake.cells[i].x && cell.y === snake.cells[i].y) {
                        gameRunning = false;
                        document.getElementById('finalScore').textContent = `Skóre: ${score} | Násobky čísla ${multiplier}`;
                        document.getElementById('gameOver').classList.remove('hidden');
                        break;
                    }
                }
            }
        });

        // Draw flash effects on top of everything (snake + cells)
        const now = Date.now();
        for (let i = flashes.length - 1; i >= 0; i--) {
            const f = flashes[i];
            const elapsed = now - f.startTime;
            if (elapsed > 3000) { flashes.splice(i, 1); continue; }
            const alpha = 0.85 * (1 - elapsed / 3000);
            ctx.fillStyle = f.color === 'green'
                ? `rgba(34, 197, 94, ${alpha})`
                : `rgba(239, 68, 68, ${alpha})`;
            ctx.fillRect(f.x, f.y, GRID, GRID);
        }
    }

    // Input handling
    let nextDx = null, nextDy = null;

    document.addEventListener('keydown', function(e) {
        if (['ArrowUp','ArrowDown','ArrowLeft','ArrowRight'].includes(e.key)) {
            e.preventDefault();
        }
        // Arrows + WASD
        if ((e.key === 'ArrowLeft' || e.key === 'a') && snake.dx === 0) {
            snake.dx = -GRID; snake.dy = 0;
        } else if ((e.key === 'ArrowUp' || e.key === 'w') && snake.dy === 0) {
            snake.dy = -GRID; snake.dx = 0;
        } else if ((e.key === 'ArrowRight' || e.key === 'd') && snake.dx === 0) {
            snake.dx = GRID; snake.dy = 0;
        } else if ((e.key === 'ArrowDown' || e.key === 's') && snake.dy === 0) {
            snake.dy = GRID; snake.dx = 0;
        }
    });

    function startGame() {
        document.getElementById('startScreen').classList.add('hidden');
        document.getElementById('gameOver').classList.add('hidden');
        score = 0;
        correctStreak = 0;
        speed = BASE_SPEED;
        resetSnake();
        spawnItems();
        updateHUD();
        gameRunning = true;
    }

    function restartGame() {
        pickMultiplier();
        startGame();
    }

    // Init
    pickMultiplier();
    requestAnimationFrame(loop);
    </script>
    <div style="position:fixed;bottom:8px;right:12px;font-size:11px;color:rgba(255,255,255,0.3);text-align:right;">
        {{ config('version.number') }}<br>{{ config('version.deployed_at') }}
    </div>
</body>
</html>
