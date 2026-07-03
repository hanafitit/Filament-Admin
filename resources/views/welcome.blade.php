<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Учёт заказов') }}</title>

        @fonts

        <style>
            body {
                margin: 0;
                min-height: 100vh;
                display: grid;
                place-items: center;
                background: #111827;
                color: #f9fafb;
                font-family: "Instrument Sans", ui-sans-serif, system-ui, sans-serif;
            }

            main {
                width: min(92vw, 560px);
                padding: 40px;
                border: 1px solid rgba(255, 255, 255, 0.12);
                border-radius: 18px;
                background: rgba(17, 24, 39, 0.82);
                box-shadow: 0 24px 80px rgba(0, 0, 0, 0.28);
            }

            h1 {
                margin: 0 0 12px;
                font-size: clamp(32px, 6vw, 54px);
                line-height: 1;
            }

            p {
                margin: 0 0 28px;
                color: #d1d5db;
                font-size: 17px;
                line-height: 1.6;
            }

            a {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 44px;
                padding: 0 18px;
                border-radius: 10px;
                background: #f59e0b;
                color: #111827;
                font-weight: 700;
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <main>
            <h1>Учёт заказов</h1>
            <p>Единая панель для заказов, статусов, исполнителей, файлов, комментариев и финансового отчёта.</p>
            <a href="{{ url('/admin') }}">Войти в админку</a>
        </main>
    </body>
</html>
