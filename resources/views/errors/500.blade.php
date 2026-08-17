<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Erreur Serveur (500) - Carnet de Lecture</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.85);
            --primary: #ef4444;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(239, 68, 68, 0.25);
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-color);
            background-image: 
                radial-gradient(circle at 50% 30%, rgba(239, 68, 68, 0.1), transparent 70%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
            box-sizing: border-box;
            padding: 1.5rem;
        }

        .error-card {
            max-width: 580px;
            width: 100%;
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 3.5rem 2.5rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 30px rgba(239, 68, 68, 0.1);
            position: relative;
            overflow: hidden;
        }

        .error-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ef4444, #f97316);
        }

        .error-badge {
            display: inline-block;
            padding: 0.35rem 1.1rem;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 9999px;
            color: #ef4444;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 1.5rem;
        }

        .icon-box {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: center;
        }

        .icon-box svg {
            width: 64px;
            height: 64px;
            color: #ef4444;
            filter: drop-shadow(0 4px 10px rgba(239, 68, 68, 0.3));
        }

        .title {
            font-family: 'Cinzel', serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--text-main);
            margin: 0 0 1rem 0;
            letter-spacing: 0.02em;
        }

        .description {
            color: var(--text-muted);
            font-size: 1.05rem;
            line-height: 1.6;
            margin: 0 0 2.25rem 0;
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            padding: 0.85rem 1.75rem;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
            border: 1px solid transparent;
        }

        .action-btn.primary {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
        }

        .action-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.45);
        }

        .action-btn.secondary {
            background: transparent;
            color: var(--text-main);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .action-btn.secondary:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .action-btn svg {
            width: 18px;
            height: 18px;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-badge">Erreur 500</div>

        <div class="icon-box">
            <svg viewBox="0 0 256 256" fill="currentColor">
                <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216Zm-8-80V80a8,8,0,0,1,16,0v56a8,8,0,0,1-16,0Zm20,36a12,12,0,1,1-12-12A12,12,0,0,1,140,172Z"/>
            </svg>
        </div>

        <h1 class="title">Erreur Interne du Grimoire</h1>
        
        <p class="description">
            Un incident inattendu a empêché le serveur de répondre convenablement. Nos scribes travaillent à rétablir l'ordre dans la bibliothèque.
        </p>

        <div class="actions">
            <button onclick="window.location.reload();" class="action-btn primary">
                <svg viewBox="0 0 256 256" fill="currentColor">
                    <path d="M224,128a96,96,0,0,1-144.07,83.11l-14.86,14.86A8,8,0,0,1,51.43,214.3l32-32a8,8,0,0,1,11.31,0l32,32a8,8,0,0,1-11.31,11.31L101,211.19A80,80,0,1,0,48,128a8,8,0,0,1-16,0,96,96,0,1,1,192,0Z"/>
                </svg>
                <span>Réessayer</span>
            </button>

            <a href="/" class="action-btn secondary">
                <svg viewBox="0 0 256 256" fill="currentColor">
                    <path d="M219.31,108.68l-80-80a16,16,0,0,0-22.62,0l-80,80A15.87,15.87,0,0,0,32,120v96a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V160h32v56a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V120A15.87,15.87,0,0,0,219.31,108.68ZM208,208H160V152a8,8,0,0,0-8-8H104a8,8,0,0,0-8,8v56H48V120l80-80,80,80Z"/>
                </svg>
                <span>Accueil</span>
            </a>
        </div>
    </div>
</body>
</html>
