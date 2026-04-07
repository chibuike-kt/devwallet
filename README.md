# DevWallet

DevWallet is a local payment provider sandbox that emulates real-world payment APIs with high fidelity. It allows you to develop, test, and harden payment flows against Paystack, Flutterwave, and Stripe without relying on external networks or live credentials.

Instead of mocking payment logic inside your application, DevWallet mirrors provider behavior at the HTTP layer. Your integration code does not change. Only the base URL is swapped.

## Core idea

Most teams test payments incorrectly.

They either:

* Mock SDK responses, which ignores real API behavior
* Use live test environments, which are slow, unreliable, and hard to control
* Skip failure testing entirely

DevWallet fixes this by acting as a drop-in replacement for real providers.

```
# Development
PAYSTACK_BASE_URL=http://localhost:8000/api/paystack

# Production
PAYSTACK_BASE_URL=https://api.paystack.co
```

No adapters, no wrappers, no conditionals in your codebase.

## What DevWallet simulates

DevWallet is not a mock server. It is a deterministic simulation layer that reproduces real provider behavior:

* Request and response structure identical to providers
* Authentication handling
* Transaction lifecycles
* Transfer flows
* Refund logic
* Webhook delivery with valid signatures
* Failure conditions under controlled probability

This allows you to test the full lifecycle of money movement, not just happy paths.

## Supported providers

| Provider    | Base URL              | Authentication    |
| ----------- | --------------------- | ----------------- |
| Paystack    | `/api/paystack`       | Bearer secret key |
| Flutterwave | `/api/flutterwave/v3` | Bearer secret key |
| Stripe      | `/api/stripe/v1`      | Bearer or Basic   |

Each provider namespace mirrors its real API structure and naming conventions.

## Why this matters

Payment systems fail in production in ways most teams never test:

* Intermittent provider downtime
* Delayed transfers
* Webhook race conditions
* Duplicate events
* Signature validation issues
* Partial failures across multi-step flows

DevWallet gives you control over these conditions locally so you can build systems that are resilient by design.

## Installation

```
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

Access the dashboard at:

```
http://localhost:8000
```

Credentials:

* Email: [demo@devwallet.dev](mailto:demo@devwallet.dev)
* Password: password

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

## Simulation engine

Each project includes a simulation control layer that lets you test non-ideal conditions.

### Failure injection

* Global failure rate from 0 to 100 percent
* Randomized failure based on probability
* One-shot forced failure for deterministic testing

This is critical for testing retry logic and idempotency.

### Timing control

* Instant processing
* Delayed processing around five seconds
* Timeout simulation around thirty seconds

This exposes race conditions and improper async handling in your system.

### Webhook control

You can manually trigger webhook events using real transaction data:

* charge.success
* transfer.success
* transfer.failed

This allows you to test webhook consumers without waiting for real flows.

## Webhooks

DevWallet signs webhooks using the same mechanisms as real providers.

| Provider    | Header               | Method                     |
| ----------- | -------------------- | -------------------------- |
| Paystack    | x-paystack-signature | HMAC SHA512                |
| Flutterwave | verif-hash           | Static secret              |
| Stripe      | stripe-signature     | HMAC SHA256 with timestamp |

Your verification logic should work unchanged between sandbox and production.

## Example integration

Node.js example using Paystack-compatible endpoints:

```javascript
const axios = require('axios');

const paystack = axios.create({
  baseURL: 'http://localhost:8000/api/paystack',
  headers: {
    Authorization: 'Bearer sk_test_your_key'
  }
});

const { data } = await paystack.post('/transaction/initialize', {
  email: 'customer@yourapp.com',
  amount: 50000,
  callback_url: 'http://localhost:3000/payment/callback'
});
```

Verification:

```javascript
const { data } = await paystack.get(`/transaction/verify/${reference}`);

if (data.data.status === 'success') {
  // fulfil order
}
```

Webhook handling:

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

## Switching to production

No refactor required.

```
PAYSTACK_BASE_URL=https://api.paystack.co
PAYSTACK_SECRET_KEY=sk_live_...
```

Your application logic remains unchanged.

## Architecture notes

DevWallet is designed to sit at the boundary of your system:

* It enforces HTTP level correctness
* It encourages idempotent design
* It exposes timing and failure edge cases early
* It removes dependency on external sandbox reliability

For serious systems, this should be paired with:

* Event queues for webhook processing
* Idempotency keys on all write operations
* Retry strategies with exponential backoff
* Persistent logging of all payment state transitions

## Stack

* Laravel 11
* SQLite for local development
* Tailwind CSS
* Blade templating
* spatie activity log for audit trails

## Intended users

* Fintech startups building payment infrastructure
* Marketplaces handling escrow or split payments
* Crypto on ramp and off ramp products
* Engineers who need deterministic payment testing

## Limitations

* Not a full replacement for provider certification environments
* Does not simulate banking network level failures
* Assumes correct integration semantics from the client

## Positioning

DevWallet is not another mock tool. It is a local reliability layer for payment systems.

If your system breaks when a webhook arrives late, twice, or out of order, DevWallet will surface it before production does.

---
