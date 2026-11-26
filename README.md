# Untitled Cinema

Fake movie theater for school.

## Stack
- Vanilla PHP (OOP, no framework)
- MariaDB
- PHPMailer (SMTP)
- TMDB API for film metadata
- Stripe (test mode)
- Docker for local development, hosted on Simply.com

## Live site
https://cinema.matmil.dk/

## Running locally
1. Copy `.env.example` to `.env` and fill in keys (TMDB, Stripe test, SMTP).
2. Run `docker compose up`.
3. Open http://localhost:8080

The database builds itself on first start from the files in `database/`, in this order:
schema, views, triggers, seed data.

No users are seeded. To get an admin account, register on the site, then run:
`UPDATE users SET role = 'admin' WHERE email = 'the-email-you-registered';`
