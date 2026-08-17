<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page Introuvable (404) - Carnet de Lecture</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.85);
            --primary: #c29d59;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(212, 175, 55, 0.2);
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
                radial-gradient(circle at 50% 30%, rgba(194, 157, 89, 0.12), transparent 70%),
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
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 30px rgba(194, 157, 89, 0.1);
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
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
        }

        .error-badge {
            display: inline-block;
            padding: 0.35rem 1.1rem;
            background: rgba(194, 157, 89, 0.15);
            border: 1px solid rgba(194, 157, 89, 0.3);
            border-radius: 9999px;
            color: var(--primary);
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
            color: var(--primary);
            filter: drop-shadow(0 4px 10px rgba(194, 157, 89, 0.3));
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

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            background: linear-gradient(135deg, #c29d59 0%, #a37d36 100%);
            color: #0f172a;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.85rem 2rem;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(194, 157, 89, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(194, 157, 89, 0.45);
            filter: brightness(1.1);
        }

        .action-btn svg {
            width: 18px;
            height: 18px;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-badge">Erreur 404</div>

        <div class="icon-box">
            <svg viewBox="0 0 256 256" fill="currentColor">
                <path d="M128,24A104,104,0,1,0,232,128,104.11,104.11,0,0,0,128,24Zm0,192a88,88,0,1,1,88-88A88.1,88.1,0,0,1,128,216ZM165.66,90.34a8,8,0,0,1,0,11.32l-64,64a8,8,0,0,1-11.32-11.32l64-64A8,8,0,0,1,165.66,90.34ZM176,144a12,12,0,1,1-12-12A12,12,0,0,1,176,144ZM92,112a12,12,0,1,1,12,12A12,12,0,0,1,92,112Z"/>
            </svg>
        </div>

        <h1 class="title">Page Introuvable</h1>
        
        <p class="description">
            Le parchemin que vous cherchez n'existe pas ou a été déplacé dans une autre section des archives.
        </p>

        <a href="/" class="action-btn">
            <svg viewBox="0 0 256 256" fill="currentColor">
                <path d="M219.31,108.68l-80-80a16,16,0,0,0-22.62,0l-80,80A15.87,15.87,0,0,0,32,120v96a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V160h32v56a8,8,0,0,0,8,8h64a8,8,0,0,0,8-8V120A15.87,15.87,0,0,0,219.31,108.68ZM208,208H160V152a8,8,0,0,0-8-8H104a8,8,0,0,0-8,8v56H48V120l80-80,80,80Z"/>
            </svg>
            <span>Retour à l'accueil</span>
        </a>
    </div>
</body>
</html>
