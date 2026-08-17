<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bilan du Mois - {{ $month }}/{{ $year }}</title>
    <style>
        :root {
            /* Variables injectées depuis PHP */
            @foreach($cssVariables as $key => $value)
                {{ $key }}: {{ $value }};
            @endforeach
        }
        
        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Georgia', serif;
            margin: 0;
            padding: 40px;
            /* Pour forcer l'impression des backgrounds avec Browsershot */
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .paper-grain {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            opacity: var(--paper-grain-opacity);
            mix-blend-mode: multiply;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            z-index: -1;
        }

        .header {
            text-align: center;
            margin-bottom: 50px;
            border-bottom: 2px solid var(--text-color);
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 2.5em;
            margin: 0 0 10px 0;
            color: var(--primary-color);
        }

        .header p {
            font-size: 1.2em;
            margin: 0;
            font-style: italic;
        }

        /* Support moderne CSS Grid */
        .grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
        }

        .book-card {
            display: flex;
            gap: 20px;
            background: rgba(255, 255, 255, 0.5);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            page-break-inside: avoid; /* Évite qu'une carte soit coupée sur 2 pages */
        }

        .book-cover {
            width: 100px;
            height: 150px;
            object-fit: cover;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .book-spine-fallback {
            width: 100px;
            height: 150px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8em;
            text-align: center;
            box-shadow: inset 4px 0 10px rgba(0,0,0,0.1);
        }

        .book-info {
            flex: 1;
        }

        .book-info h3 {
            margin: 0 0 5px 0;
            font-size: 1.2em;
        }

        .book-info .author {
            margin: 0 0 15px 0;
            font-size: 1em;
            color: var(--primary-color);
            font-weight: bold;
        }

        .stats p {
            margin: 5px 0;
            font-size: 0.9em;
        }

        .rating {
            color: #f59e0b;
            font-size: 1.2em;
            margin-bottom: 10px;
        }

        .review {
            margin-top: 15px;
            font-style: italic;
            font-size: 0.9em;
            line-height: 1.5;
            color: #4b5563;
            border-left: 3px solid var(--primary-color);
            padding-left: 10px;
        }

        .awards {
            margin-top: 10px;
        }
        
        .badge {
            background: var(--primary-color);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.75em;
            display: inline-block;
            margin-right: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="paper-grain"></div>
    
    <div class="header">
        <h1>Carnet de Lecture</h1>
        <p>Bilan de {{ sprintf('%02d', $month) }} / {{ $year }} &mdash; {{ $user->name }}</p>
    </div>

    <div class="grid">
        @forelse($readings as $reading)
            <div class="book-card">
                @if($reading->book->cover_url)
                    <img src="{{ $reading->book->cover_url }}" class="book-cover" alt="Cover">
                @else
                    <div class="book-spine-fallback" style="background-color: {{ $reading->book->spine_color ?? '#8B4513' }}">
                        {{ $reading->book->title }}
                    </div>
                @endif
                
                <div class="book-info">
                    <h3>{{ $reading->book->title }}</h3>
                    <p class="author">{{ $reading->book->author }}</p>
                    
                    <div class="rating">
                        {{ str_repeat('★', $reading->rating ?? 0) }}{{ str_repeat('☆', 5 - ($reading->rating ?? 0)) }}
                    </div>

                    @if($reading->book->customAwards->isNotEmpty())
                        <div class="awards">
                            @foreach($reading->book->customAwards as $award)
                                <span class="badge">🏆 {{ $award->name }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if($reading->review)
                        <div class="review">"{{ Str::limit($reading->review, 250) }}"</div>
                    @endif
                </div>
            </div>
        @empty
            <p style="grid-column: span 2; text-align: center;">Aucun livre terminé ce mois-ci.</p>
        @endforelse
    </div>
</body>
</html>
