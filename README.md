# Inactive User Reminder (Laravel)

This Laravel app detects users who are inactive (no login for N days) and dispatches a queued job to simulate sending them a reminder. Each reminder is recorded in the database to ensure a user is not processed more than once per day.

The “reminder send” is simulated by:
- writing a row to the `reminder_logs` table
- writing a log entry to `storage/logs/laravel.log`

## Requirements implemented

- Inactive if not logged in for **7 days** (configurable)
- A scheduled command runs daily and finds inactive users
- Dispatches a queued job per user
- The job simulates sending a reminder (logs) and records `sent_at`
- Prevents processing the same user more than once per day

## Setup

### Prerequisites

- PHP 8.2+
- Composer
- MySQL / MariaDB

1) Install dependencies

```bash
composer install
```

2) Create environment file

```bash
copy .env.example .env
php artisan key:generate
```

3) Configure MySQL in `.env`

Set at least:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inactive_user_reminder
DB_USERNAME=root
DB_PASSWORD=

# Inactivity window (days)
REMINDER_INACTIVE_DAYS=7
```

Create the database in MySQL (example):

```sql
CREATE DATABASE inactive_user_reminder;
```

#### Create the database using XAMPP (phpMyAdmin)

If you're using XAMPP on Windows:

1) Open **XAMPP Control Panel** and start:
	- **Apache**
	- **MySQL**
2) Open phpMyAdmin in your browser:
	- http://localhost/phpmyadmin
3) Click **New** (left sidebar)
4) Database name: `inactive_user_reminder`
5) Choose collation (recommended): `utf8mb4_unicode_ci`
6) Click **Create**

4) Run migrations

```bash
php artisan migrate
```

### Optional (run the app)

```bash
php artisan serve
```

## How it works

- Login tracking: on successful login, the app updates `users.last_login_at`.
- The scheduled command `app:check-inactive-users`:
	- selects users inactive for `REMINDER_INACTIVE_DAYS`
	- skips users already reminded today (based on `reminder_logs`)
	- dispatches `SendInactiveUserReminder` jobs
- The job writes a row into `reminder_logs` with `sent_at` and logs a message.

## Verify it works (end-to-end)

This section is designed for reviewers who are not familiar with Laravel.

1) Start the queue worker (leave it running)

```bash
php artisan queue:work
```

2) Create an “inactive” user (8 days ago)

```bash
php artisan tinker
```

Then paste:

```php
$u = App\Models\User::factory()->create();
$u->forceFill(['last_login_at' => now()->subDays(8)])->save();
```

Exit tinker:

```php
exit
```

3) Run the inactive-user command

```bash
php artisan app:check-inactive-users -v
```

Expected:
- it prints something like `Dispatched reminders: 1`
- the queue worker processes `App\Jobs\SendInactiveUserReminder`

4) Confirm the reminder was recorded

```bash
php artisan tinker --execute 'dump(App\Models\ReminderLog::count());'
```

Also check the log file:
- `storage/logs/laravel.log`

5) Confirm “not more than once per day”

Run the command again the same day:

```bash
php artisan app:check-inactive-users -v
```

Expected:
- `Dispatched reminders: 0`

## Run the scheduler

Laravel's scheduler needs a system cron/task to call it.

### Option A: Dev mode (recommended locally)

```bash
php artisan schedule:work
```

### Option B: Cron / Task Scheduler

Run every minute:

```bash
php artisan schedule:run
```

On Windows you can set up **Task Scheduler** to run the above command every minute.

## Run the queue worker

This project uses the database queue (`QUEUE_CONNECTION=database`).

```bash
php artisan queue:work
```

## Run the command manually

```bash
php artisan app:check-inactive-users
```
