<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance en cours - Carnet de Lecture</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        :root {
            --bg-color: #0f172a;
            --card-bg: rgba(30, 41, 59, 0.85);
            --primary: #c29d59;
            --primary-hover: #d4af37;
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
                radial-gradient(circle at 50% 30%, rgba(194, 157, 89, 0.15), transparent 70%),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%239C92AC' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
            box-sizing: border-box;
            padding: 1.5rem;
        }

        .maintenance-card {
            max-width: 600px;
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

        .maintenance-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1.1rem;
            background: rgba(194, 157, 89, 0.15);
            border: 1px solid rgba(194, 157, 89, 0.3);
            border-radius: 9999px;
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 1.75rem;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: var(--primary);
            border-radius: 50%;
            animation: pulse-ring 1.5s cubic-bezier(0.45, 0, 0.55, 1) infinite;
        }

        @keyframes pulse-ring {
            0% {
                box-shadow: 0 0 0 0 rgba(194, 157, 89, 0.7);
            }
            70% {
                box-shadow: 0 0 0 8px rgba(194, 157, 89, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(194, 157, 89, 0);
            }
        }

        .icon-box {
            margin-bottom: 1.5rem;
            display: flex;
            justify-content: center;
        }

        .icon-box svg {
            width: 68px;
            height: 68px;
            color: var(--primary);
            filter: drop-shadow(0 4px 10px rgba(194, 157, 89, 0.3));
            animation: floating 4s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(-2deg); }
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
            font-size: 1.1rem;
            line-height: 1.6;
            margin: 0 0 2.5rem 0;
        }

        .refresh-btn {
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

        .refresh-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(194, 157, 89, 0.45);
            filter: brightness(1.1);
        }

        .refresh-btn svg {
            width: 18px;
            height: 18px;
            transition: transform 0.4s ease;
        }

        .refresh-btn:hover svg {
            transform: rotate(180deg);
        }

        .footer-note {
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 0.875rem;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <div class="maintenance-card">
        <div class="badge-status">
            <span class="pulse-dot"></span>
            Maintenance en cours
        </div>

        <div class="icon-box">
            <!-- Book / Grimoire Quill SVG -->
            <svg viewBox="0 0 256 256" fill="currentColor">
                <path d="M224,48V208a8,8,0,0,1-8,8H72a40,40,0,0,0-40,40,8,8,0,0,1-16,0V48a8,8,0,0,1,8-8H216A8,8,0,0,1,224,48ZM64,200a24,24,0,0,1,16-4.57H208V56H40V208.57A39.73,39.73,0,0,1,64,200Zm96-96a8,8,0,0,0-8,8,24,24,0,0,1-48,0,8,8,0,0,0-16,0,40,40,0,0,0,80,0A8,8,0,0,0,160,104Z" opacity="0.2"/>
                <path d="M216,40H72A48,48,0,0,0,24,88V208a8,8,0,0,0,8,8,32,32,0,0,1,32,32,8,8,0,0,0,16,0,48,48,0,0,0-16-35.74V88A32,32,0,0,1,96,56H216a8,8,0,0,0,8-8A8,8,0,0,0,216,40Zm-96,96a8,8,0,0,0-8,8,24,24,0,0,1-48,0,8,8,0,0,0-16,0,40,40,0,0,0,80,0A8,8,0,0,0,120,136Z" fill="currentColor"/>
            </svg>
        </div>

        <h1 class="title">Reliure & Entretien du Grimoire</h1>
        
        <p class="description">
            Nos archivistes enchantent actuellement la bibliothèque pour vous offrir une expérience encore plus magique. Nous serons de retour dans quelques instants.
        </p>

        <button onclick="window.location.reload();" class="refresh-btn">
            <svg viewBox="0 0 256 256" fill="currentColor">
                <path d="M224,128a96,96,0,0,1-144.07,83.11l-14.86,14.86A8,8,0,0,1,51.43,214.3l32-32a8,8,0,0,1,11.31,0l32,32a8,8,0,0,1-11.31,11.31L101,211.19A80,80,0,1,0,48,128a8,8,0,0,1-16,0,96,96,0,1,1,192,0Z"/>
            </svg>
            <span>Vérifier la disponibilité</span>
        </button>

        <div class="footer-note">
            Merci pour votre patience &bull; Le Carnet de Lecture
        </div>
    </div>
</body>
</html>
