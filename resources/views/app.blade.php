<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Carnet de Lecture</title>

        <!-- Configuration Vite pour charger le CSS et le JS compilé -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <!-- Point de montage de l'application VueJS -->
        <div id="app"></div>
    </body>
</html>
