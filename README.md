# Inactive User Reminder (Laravel)

This Laravel app detects users who are inactive (no login for N days) and dispatches a queued job to simulate sending them a reminder. Each reminder is recorded in the database to ensure a user is not processed more than once per day.

## Requirements implemented

- Inactive if not logged in for **7 days** (configurable)
- A scheduled command runs daily and finds inactive users
- Dispatches a queued job per user
- The job simulates sending a reminder (logs) and records `sent_at`
- Prevents processing the same user more than once per day

## Setup

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

4) Run migrations

```bash
php artisan migrate
```

## How it works

- Login tracking: on successful login, the app updates `users.last_login_at`.
- The scheduled command `app:check-inactive-users`:
	- selects users inactive for `REMINDER_INACTIVE_DAYS`
	- skips users already reminded today (based on `reminder_logs`)
	- dispatches `SendInactiveUserReminder` jobs
- The job writes a row into `reminder_logs` with `sent_at` and logs a message.

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
