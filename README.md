# DevWallet — Payment Provider Sandbox

DevWallet is a local sandbox that simulates real payment providers.
Change one line in your app — the base URL — and your existing
integration code works against DevWallet instead of the real provider.
```
# Development
PAYSTACK_BASE_URL=http://localhost:8000/api/paystack

# Production
PAYSTACK_BASE_URL=https://api.paystack.co
```

---

## Supported providers

| Provider | Base URL | Auth |
|---|---|---|
| Paystack | `/api/paystack` | `Bearer sk_test_...` |
| Flutterwave | `/api/flutterwave/v3` | `Bearer sk_test_...` |
| Stripe | `/api/stripe/v1` | `Bearer sk_test_...` or Basic auth |

---

## Quick start
```bash
git clone <repo>
cd devwallet
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run dev
php artisan serve
```

Login at `http://localhost:8000` with:
- **Email:** demo@devwallet.dev
- **Password:** password

---

## API coverage

### Paystack
```
POST /api/paystack/transaction/initialize
GET  /api/paystack/transaction/verify/:reference
GET  /api/paystack/transaction
GET  /api/paystack/transaction/:id
POST /api/paystack/refund
GET  /api/paystack/refund
POST /api/paystack/transfer
GET  /api/paystack/transfer/verify/:reference
GET  /api/paystack/balance
POST /api/paystack/customer
GET  /api/paystack/customer/:email_or_code
```

### Flutterwave
```
POST /api/flutterwave/v3/payments
GET  /api/flutterwave/v3/transactions/:id/verify
GET  /api/flutterwave/v3/transactions
POST /api/flutterwave/v3/transfers
GET  /api/flutterwave/v3/transfers/:id
GET  /api/flutterwave/v3/balances/:currency
GET  /api/flutterwave/v3/balances
```

### Stripe
```
POST /api/stripe/v1/payment_intents
GET  /api/stripe/v1/payment_intents/:id
POST /api/stripe/v1/payment_intents/:id/confirm
POST /api/stripe/v1/payment_intents/:id/cancel
GET  /api/stripe/v1/payment_intents
POST /api/stripe/v1/refunds
GET  /api/stripe/v1/refunds/:id
GET  /api/stripe/v1/refunds
POST /api/stripe/v1/transfers
GET  /api/stripe/v1/transfers/:id
GET  /api/stripe/v1/balance
```

---

## Simulation controls

Every project has a **Simulation** panel where you can:

- **Set a failure rate** (0–100%) — percentage of transactions that fail automatically
- **Force the next transaction to fail** — one-shot, clears after use
- **Set transfer processing speed** — Instant / Slow (~5s) / Timeout (~30s)
- **Fire webhooks manually** — trigger `charge.success`, `transfer.success`,
  or `transfer.failed` against your registered endpoints using real transaction data

---

## Webhooks

Webhooks are delivered with provider-accurate signatures:

| Provider | Header | Algorithm |
|---|---|---|
| Paystack | `x-paystack-signature` | HMAC-SHA512 |
| Flutterwave | `verif-hash` | Static secret |
| Stripe | `stripe-signature` | HMAC-SHA256 + timestamp |

---

## Stack

- Laravel 11
- SQLite (development)
- Tailwind CSS
- Blade
- spatie/laravel-activitylog
