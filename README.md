````markdown
# DevWallet

DevWallet is a local payment provider sandbox that emulates real-world payment APIs with high fidelity. It allows you to develop, test, and harden payment flows against Paystack, Flutterwave, and Stripe without relying on external networks or live credentials.

Instead of mocking payment logic inside your application, DevWallet mirrors provider behavior at the HTTP layer. Your integration code does not change. Only the base URL is swapped.

---

## Core Idea

Most teams test payments incorrectly.

They either mock SDK responses, rely on unstable external test environments, or avoid testing failure scenarios entirely.

DevWallet acts as a drop-in replacement for real providers:

```env
# Development
PAYSTACK_BASE_URL=http://localhost:8000/api/paystack

# Production
PAYSTACK_BASE_URL=https://api.paystack.co
````

No adapters, wrappers, or conditional logic required.

---

## What DevWallet Simulates

DevWallet is not a mock server. It is a deterministic simulation layer that reproduces real provider behavior:

* Request and response structures identical to providers
* Authentication handling
* Transaction lifecycles
* Transfers and settlements
* Refund processing
* Webhook delivery with valid signatures
* Controlled failure conditions

This allows you to test full payment flows, including edge cases.

---

## Supported Providers

| Provider    | Base URL              | Authentication       |
| ----------- | --------------------- | -------------------- |
| Paystack    | `/api/paystack`       | Bearer secret key    |
| Flutterwave | `/api/flutterwave/v3` | Bearer secret key    |
| Stripe      | `/api/stripe/v1`      | Bearer or Basic auth |

Each provider namespace mirrors real API structure and naming.

---

## Why This Matters

Payment systems fail in production due to:

* Provider downtime
* Delayed or missing webhooks
* Duplicate events
* Race conditions
* Invalid signature handling
* Partial failures in multi-step flows

DevWallet allows you to simulate and control these scenarios locally.

---

## Installation

```bash
git clone <repo>
cd devwallet

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate --seed

npm install
npm run dev

php artisan serve
```

Access the dashboard:

```
http://localhost:8000
```

Credentials:

* Email: [demo@devwallet.dev](mailto:demo@devwallet.dev)
* Password: password

---

## API Coverage

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

## Simulation Engine

Each project includes simulation controls to test real-world failure scenarios.

### Failure Injection

* Configurable failure rate from 0 to 100 percent
* Randomized failures based on probability
* One-time forced failure

Used to validate retry logic and idempotency.

### Timing Control

* Instant processing
* Delayed processing around five seconds
* Timeout simulation around thirty seconds

Used to expose race conditions and async handling issues.

### Webhook Control

Trigger webhook events manually using real transaction data:

* charge.success
* transfer.success
* transfer.failed

---

## Webhooks

DevWallet signs webhooks using provider-compatible mechanisms:

| Provider    | Header               | Method                     |
| ----------- | -------------------- | -------------------------- |
| Paystack    | x-paystack-signature | HMAC SHA512                |
| Flutterwave | verif-hash           | Static secret              |
| Stripe      | stripe-signature     | HMAC SHA256 with timestamp |

Webhook verification logic works unchanged between sandbox and production.

---

## Example Integration

### Node.js (Paystack)

```javascript
const axios = require('axios');

const paystack = axios.create({
  baseURL: 'http://localhost:8000/api/paystack',
  headers: {
    Authorization: 'Bearer sk_test_your_key'
  }
});

// Initialize transaction
const { data } = await paystack.post('/transaction/initialize', {
  email: 'customer@yourapp.com',
  amount: 50000,
  callback_url: 'http://localhost:3000/payment/callback'
});
```

### Verify Transaction

```javascript
const { data } = await paystack.get(`/transaction/verify/${reference}`);

if (data.data.status === 'success') {
  // fulfil order
}
```

### Webhook Handler

```javascript
app.post('/webhooks/paystack', express.raw({ type: 'application/json' }), (req, res) => {
  const crypto = require('crypto');

  const hash = crypto
    .createHmac('sha512', process.env.PAYSTACK_SECRET_KEY)
    .update(req.body)
    .digest('hex');

  if (hash !== req.headers['x-paystack-signature']) {
    return res.sendStatus(401);
  }

  const { event, data } = JSON.parse(req.body);

  if (event === 'charge.success') {
    // handle payment
  }

  res.sendStatus(200);
});
```

---

## Switching to Production

No refactor required:

```env
PAYSTACK_BASE_URL=https://api.paystack.co
PAYSTACK_SECRET_KEY=sk_live_your_real_key
```

---

## Architecture Notes

DevWallet is designed to sit at the boundary of your system:

* Enforces HTTP-level correctness
* Encourages idempotent design
* Surfaces timing and failure edge cases early
* Removes dependency on external sandbox reliability

Recommended patterns:

* Event queues for webhook processing
* Idempotency keys for all write operations
* Retry strategies with exponential backoff
* Persistent logging of payment state transitions

---

## Stack

* Laravel 11
* SQLite for development
* Tailwind CSS
* Blade templating
* spatie/laravel-activitylog

---

## Intended Users

* Fintech startups building payment infrastructure
* Marketplaces handling escrow or split payments
* Crypto onramp and offramp systems
* Engineers testing payment reliability

---

## Limitations

* Not a replacement for provider certification environments
* Does not simulate banking network level failures
* Assumes correct integration semantics from the client

---

## Summary

DevWallet is a local reliability layer for payment systems.

It allows you to test failure scenarios, webhook behavior, and transaction flows before production, using the same integration code you already rely on.

```
