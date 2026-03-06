<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Inactive User Reminder') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <style>
            :root {
                color-scheme: light dark;
            }

            body {
                margin: 0;
                font-family: "Instrument Sans", ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif;
            }

            .wrap {
                min-height: 100vh;
                padding: 24px;
                background: #fdfdfc;
                color: #1b1b18;
            }

            .card {
                max-width: 920px;
                margin: 0 auto;
                background: #ffffff;
                border: 1px solid rgba(0, 0, 0, .12);
                border-radius: 10px;
                padding: 24px;
            }

            h1 {
                font-size: 24px;
                margin: 0 0 10px;
            }

            h2 {
                font-size: 16px;
                margin: 18px 0 8px;
            }

            p {
                margin: 0 0 12px;
                line-height: 1.55;
            }

            ol {
                margin: 0 0 12px 18px;
                padding: 0;
            }

            li {
                margin: 6px 0;
            }

            code,
            pre {
                font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            }

            pre {
                background: rgba(0, 0, 0, .06);
                padding: 10px 12px;
                border-radius: 8px;
                overflow: auto;
                margin: 8px 0 12px;
            }

            .note {
                background: rgba(245, 48, 3, .08);
                border: 1px solid rgba(245, 48, 3, .25);
                padding: 10px 12px;
                border-radius: 8px;
            }

            @media (prefers-color-scheme: dark) {
                .wrap {
                    background: #0a0a0a;
                    color: #ededec;
                }

                .card {
                    background: #161615;
                    border-color: rgba(255, 255, 255, .14);
                }

                pre {
                    background: rgba(255, 255, 255, .08);
                }

                .note {
                    background: rgba(245, 48, 3, .14);
                    border-color: rgba(255, 68, 51, .35);
                }
            }
        </style>
    @endif
</head>

<body>
    <div class="wrap">
        <main class="card">
            <h1>Inactive User Reminder (Laravel)</h1>
            <p class="note">
                Please follow the instructions in <strong>README.md</strong> (project root). This page is a quick
                checklist.
            </p>

            <h2>Setup</h2>
            <ol>
                <li>Install dependencies:</li>
            </ol>
            <pre><code>composer install</code></pre>

            <ol start="2">
                <li>Create <code>.env</code> and generate app key:</li>
            </ol>
            <pre><code>copy .env.example .env
php artisan key:generate</code></pre>

            <ol start="3">
                <li>Configure MySQL in <code>.env</code> and create the database:</li>
            </ol>
            <pre><code>CREATE DATABASE inactive_user_reminder;</code></pre>

            <ol start="4">
                <li>Run migrations:</li>
            </ol>
            <pre><code>php artisan migrate</code></pre>

            <h2>Run (optional)</h2>
            <pre><code>php artisan serve</code></pre>

            <h2>Verify (end-to-end)</h2>
            <ol>
                <li>Start the queue worker (leave it running):</li>
            </ol>
            <pre><code>php artisan queue:work</code></pre>

            <ol start="2">
                <li>Create an “inactive” user (example: last login 8 days ago):</li>
            </ol>
            <pre><code>php artisan tinker</code></pre>
            <pre><code>$u = App\Models\User::factory()->create();
$u->forceFill(['last_login_at' => now()->subDays(8)])->save();</code></pre>

            <ol start="3">
                <li>Run the inactive-user scan command:</li>
            </ol>
            <pre><code>php artisan app:check-inactive-users -v</code></pre>

            <h2>Scheduler</h2>
            <p>For local development, run:</p>
            <pre><code>php artisan schedule:work</code></pre>
            <p>In production, use cron/Task Scheduler to run every minute:</p>
            <pre><code>php artisan schedule:run</code></pre>
        </main>
    </div>
</body>

</html>
