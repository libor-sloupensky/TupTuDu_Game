<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TupTuDu - Edukační hry</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 48px;
        }

        header h1 {
            font-size: 42px;
            color: white;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        header p {
            font-size: 18px;
            color: rgba(255,255,255,0.8);
            margin-top: 8px;
        }

        .games-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 24px;
        }

        .game-card {
            background: white;
            border-radius: 20px;
            padding: 32px 24px;
            text-align: center;
            text-decoration: none;
            color: #333;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .game-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.25);
        }

        .game-icon {
            font-size: 56px;
            margin-bottom: 16px;
        }

        .game-card h2 {
            font-size: 20px;
            margin-bottom: 8px;
        }

        .game-card p {
            font-size: 14px;
            color: #888;
            line-height: 1.4;
        }

        .game-card .badge {
            display: inline-block;
            margin-top: 12px;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-ready {
            background: #dcfce7;
            color: #16a34a;
        }

        .badge-soon {
            background: #fef3c7;
            color: #d97706;
        }

        footer {
            text-align: center;
            margin-top: 48px;
            color: rgba(255,255,255,0.5);
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>TupTuDu</h1>
            <p>Edukační hry pro chytré hlavy</p>
        </header>

        <div class="games-grid">
            <a href="/quiz" class="game-card">
                <div class="game-icon">🧮</div>
                <h2>Matematický kvíz</h2>
                <p>Sčítání a odčítání do 100. Jak rychle dokážeš odpovídat?</p>
                <span class="badge badge-ready">Hrát</span>
            </a>

            <a href="/snake" class="game-card">
                <div class="game-icon">🐍</div>
                <h2>Násobkový had</h2>
                <p>Lov násobky malé násobilky a vyhýbej se špatným číslům!</p>
                <span class="badge badge-soon">Připravujeme</span>
            </a>
        </div>

        <footer>
            TupTuDu &copy; 2026
        </footer>
    </div>
</body>
</html>
