<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>DevWallet — API Documentation</title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-full bg-white font-sans antialiased">

  {{-- Top bar --}}
  <header class="h-14 border-b border-slate-100 flex items-center justify-between px-8 sticky top-0 z-30 bg-white">
    <div class="flex items-center gap-6">
      <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
        <div class="w-6 h-6 rounded-md bg-brand-500 flex items-center justify-center">
          <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
          </svg>
        </div>
        <span class="font-bold text-slate-900 text-sm">DevWallet</span>
      </a>
      <span class="text-slate-200">|</span>
      <span class="text-sm text-slate-500 font-medium">API Documentation</span>
    </div>
    <div class="flex items-center gap-4">
      <span class="flex items-center gap-1.5 text-xs text-emerald-600 bg-emerald-50
                     px-2.5 py-1 rounded-full font-medium">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
        Sandbox
      </span>
      @auth
      <a href="{{ route('dashboard') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
        Dashboard →
      </a>
      @else
      <a href="{{ route('login') }}" class="text-sm text-brand-600 hover:text-brand-700 font-medium">
        Sign in →
      </a>
      @endauth
    </div>
  </header>

  <div class="flex" style="height: calc(100vh - 3.5rem)">

    {{-- Sidebar nav --}}
    <aside class="w-56 border-r border-slate-100 flex-shrink-0 overflow-y-auto py-6">
      <nav class="space-y-0.5 px-3">

        <p class="px-3 py-1 text-[10px] font-semibold text-slate-400 uppercase tracking-widest">
          Getting started
        </p>
        <a href="#introduction" class="docs-link active">Introduction</a>
        <a href="#authentication" class="docs-link">Authentication</a>

        <p class="px-3 py-1 text-[10px] font-semibold text-slate-400 uppercase tracking-widest mt-4">
          Providers
        </p>
        <a href="#paystack" class="docs-link">
          <span class="w-2 h-2 rounded-full bg-[#00C3F7] inline-block mr-1.5"></span>
          Paystack
        </a>
        <a href="#flutterwave" class="docs-link">
          <span class="w-2 h-2 rounded-full bg-[#F5A623] inline-block mr-1.5"></span>
          Flutterwave
        </a>
        <a href="#stripe" class="docs-link">
          <span class="w-2 h-2 rounded-full bg-[#635BFF] inline-block mr-1.5"></span>
          Stripe
        </a>

        <p class="px-3 py-1 text-[10px] font-semibold text-slate-400 uppercase tracking-widest mt-4">
          Features
        </p>
        <a href="#webhooks" class="docs-link">Webhooks</a>
        <a href="#simulation" class="docs-link">Simulation</a>

      </nav>
    </aside>

    {{-- Content --}}
    <main class="flex-1 overflow-y-auto" id="docs-main">
      <div class="max-w-3xl mx-auto px-10 py-10 space-y-16">

        {{-- Introduction --}}
        <section id="introduction">
          <h1 class="text-2xl font-bold text-slate-900 mb-3">Introduction</h1>
          <p class="text-slate-500 text-sm leading-relaxed mb-5">
            DevWallet is a local sandbox that simulates real payment providers.
            Change one line in your app — the base URL — and your existing
            integration works against DevWallet instead of the real provider.
          </p>

          <x-docs-code language="bash">
            # Development
            PAYSTACK_BASE_URL=http://localhost:8000/api/paystack

            # Production
            PAYSTACK_BASE_URL=https://api.paystack.co
          </x-docs-code>

          <p class="text-slate-500 text-sm leading-relaxed mt-5 mb-6">
            Your request bodies, response handlers, and webhook processors require
            zero changes. DevWallet returns exact response shapes, fires
            correctly-signed webhooks, and simulates edge cases on demand.
          </p>

          <x-docs-table :headers="['Provider', 'Base URL', 'Auth style']" :rows="[
                    ['Paystack',    '/api/paystack',        'Bearer token'],
                    ['Flutterwave', '/api/flutterwave/v3',  'Bearer token'],
                    ['Stripe',      '/api/stripe/v1',       'Bearer or Basic auth'],
                ]" />
        </section>

        {{-- Authentication --}}
        <section id="authentication">
          <h2 class="text-xl font-bold text-slate-900 mb-3">Authentication</h2>
          <p class="text-slate-500 text-sm leading-relaxed mb-5">
            Every project has its own API keys. Generate one from the
            <strong>API Keys</strong> page in your project sidebar.
            Keys are prefixed with <code class="docs-inline-code">sk_test_</code>.
          </p>

          <h3 class="docs-h3">Bearer token (Paystack & Flutterwave)</h3>
          <x-docs-code language="bash">
            Authorization: Bearer sk_test_your_key_here
          </x-docs-code>

          <h3 class="docs-h3 mt-6">Basic auth (Stripe)</h3>
          <p class="text-slate-500 text-sm leading-relaxed mb-3">
            Stripe's official SDKs use HTTP Basic auth with the key as the username.
            Both styles work with DevWallet.
          </p>
          <x-docs-code language="bash">
            # curl — note the trailing colon (empty password)
            curl -u sk_test_your_key: http://localhost:8000/api/stripe/v1/balance

            # stripe-node SDK
            const stripe = require('stripe')('sk_test_your_key', {
            host: 'localhost',
            protocol: 'http',
            port: 8000,
            });
          </x-docs-code>

          <div class="mt-5 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800">
            <strong>Provider lock:</strong> Keys are project-scoped. A Paystack project key
            cannot call Flutterwave endpoints. DevWallet returns a clear error with the
            correct base URL.
          </div>
        </section>

        {{-- Paystack --}}
        <section id="paystack">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#00C3F7]/20 flex items-center justify-center">
              <span class="text-[#00C3F7] font-bold text-sm">P</span>
            </div>
            <h2 class="text-xl font-bold text-slate-900">Paystack</h2>
          </div>

          <x-docs-base-url url="http://localhost:8000/api/paystack" />

          <h3 class="docs-h3 mt-6">Transactions</h3>
          <x-docs-endpoints :endpoints="[
                    ['POST', '/transaction/initialize',          'Initialize a transaction'],
                    ['GET',  '/transaction/verify/:reference',   'Verify & complete a transaction'],
                    ['GET',  '/transaction',                     'List all transactions'],
                    ['GET',  '/transaction/:id',                 'Fetch a transaction'],
                ]" />

          <h3 class="docs-h3 mt-6">Initialize a transaction</h3>
          <x-docs-code language="bash">
            POST /api/paystack/transaction/initialize
            Authorization: Bearer sk_test_your_key

            {
            "email": "customer@email.com",
            "amount": 50000,
            "currency": "NGN",
            "reference": "my-unique-ref-001"
            }
          </x-docs-code>
          <x-docs-code language="json">
            {
            "status": true,
            "message": "Authorization URL created",
            "data": {
            "authorization_url": "http://localhost:8000/api/paystack/checkout/my-unique-ref-001",
            "access_code": "bXktdW5pcXVlLXJlZi0wMDE=",
            "reference": "my-unique-ref-001"
            }
            }
          </x-docs-code>

          <h3 class="docs-h3 mt-6">Verify a transaction</h3>
          <p class="text-slate-500 text-sm leading-relaxed mb-3">
            Calling verify on an initialized transaction auto-completes it.
            The response includes full authorization (card) details.
          </p>
          <x-docs-code language="bash">
            GET /api/paystack/transaction/verify/my-unique-ref-001
            Authorization: Bearer sk_test_your_key
          </x-docs-code>
          <x-docs-code language="json">
            {
            "status": true,
            "message": "Verification successful",
            "data": {
            "id": 42,
            "status": "success",
            "reference": "my-unique-ref-001",
            "amount": 50000,
            "currency": "NGN",
            "paid_at": "2024-11-01T14:22:10+00:00",
            "channel": "card",
            "customer": {
            "email": "customer@email.com",
            "customer_code": "CUS_abc123"
            },
            "authorization": {
            "authorization_code": "AUTH_xyzabc",
            "card_type": "visa",
            "last4": "4081",
            "exp_month": "12",
            "exp_year": "2030",
            "bank": "TEST BANK",
            "reusable": true
            }
            }
            }
          </x-docs-code>

          <h3 class="docs-h3 mt-6">Transfers</h3>
          <x-docs-endpoints :endpoints="[
                    ['POST', '/transfer',                   'Initiate a transfer'],
                    ['GET',  '/transfer/verify/:reference', 'Verify transfer status'],
                    ['GET',  '/transfer',                   'List transfers'],
                ]" />

          <h3 class="docs-h3 mt-6">Other endpoints</h3>
          <x-docs-endpoints :endpoints="[
                    ['POST', '/refund',                  'Refund a transaction'],
                    ['GET',  '/refund/:reference',       'Get a refund'],
                    ['GET',  '/balance',                 'Account balance'],
                    ['GET',  '/balance/ledger',          'Balance ledger'],
                    ['POST', '/customer',                'Create a customer'],
                    ['GET',  '/customer/:email_or_code', 'Fetch a customer'],
                    ['GET',  '/customer',                'List customers'],
                ]" />
        </section>

        {{-- Flutterwave --}}
        <section id="flutterwave">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#F5A623]/20 flex items-center justify-center">
              <span class="text-[#F5A623] font-bold text-sm">F</span>
            </div>
            <h2 class="text-xl font-bold text-slate-900">Flutterwave</h2>
          </div>

          <x-docs-base-url url="http://localhost:8000/api/flutterwave/v3" />

          <h3 class="docs-h3 mt-6">Payments</h3>
          <x-docs-endpoints :endpoints="[
                    ['POST', '/payments',                   'Initiate a payment'],
                    ['GET',  '/transactions/:id/verify',    'Verify & complete'],
                    ['GET',  '/transactions',               'List transactions'],
                ]" />

          <h3 class="docs-h3 mt-6">Initiate a payment</h3>
          <x-docs-code language="bash">
            POST /api/flutterwave/v3/payments
            Authorization: Bearer sk_test_your_key

            {
            "tx_ref": "hooli-tx-1920bbtytty",
            "amount": "100",
            "currency": "NGN",
            "redirect_url": "https://yourapp.com/callback",
            "customer": {
            "email": "customer@email.com",
            "name": "Yemi Desola",
            "phonenumber": "08012345678"
            }
            }
          </x-docs-code>
          <x-docs-code language="json">
            {
            "status": "success",
            "message": "Hosted Link",
            "data": {
            "link": "http://localhost:8000/api/flutterwave/v3/checkout/hooli-tx-1920bbtytty"
            }
            }
          </x-docs-code>

          <h3 class="docs-h3 mt-6">Verify a transaction</h3>
          <p class="text-slate-500 text-sm leading-relaxed mb-3">
            Pass the transaction ID or <code class="docs-inline-code">tx_ref</code>.
            Initialized transactions auto-complete on verify.
            Status maps to <code class="docs-inline-code">successful</code> (not <code class="docs-inline-code">success</code>) to match real Flutterwave.
          </p>
          <x-docs-code language="bash">
            GET /api/flutterwave/v3/transactions/hooli-tx-1920bbtytty/verify
          </x-docs-code>

          <h3 class="docs-h3 mt-6">Transfers & balances</h3>
          <x-docs-endpoints :endpoints="[
                    ['POST', '/transfers',           'Send a transfer'],
                    ['GET',  '/transfers/:id',       'Fetch a transfer'],
                    ['GET',  '/transfers',           'List transfers'],
                    ['GET',  '/balances/:currency',  'Balance by currency'],
                    ['GET',  '/balances',            'All currency balances'],
                ]" />
        </section>

        {{-- Stripe --}}
        <section id="stripe">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-8 h-8 rounded-lg bg-[#635BFF]/20 flex items-center justify-center">
              <span class="text-[#635BFF] font-bold text-sm">S</span>
            </div>
            <h2 class="text-xl font-bold text-slate-900">Stripe</h2>
          </div>

          <x-docs-base-url url="http://localhost:8000/api/stripe/v1" />

          <h3 class="docs-h3 mt-6">Payment intents</h3>
          <x-docs-endpoints :endpoints="[
                    ['POST', '/payment_intents',              'Create a payment intent'],
                    ['GET',  '/payment_intents/:id',          'Retrieve'],
                    ['POST', '/payment_intents/:id/confirm',  'Confirm & complete'],
                    ['POST', '/payment_intents/:id/cancel',   'Cancel'],
                    ['GET',  '/payment_intents',              'List'],
                ]" />

          <h3 class="docs-h3 mt-6">Create & confirm a payment intent</h3>
          <x-docs-code language="bash">
            POST /api/stripe/v1/payment_intents
            Authorization: Bearer sk_test_your_key

            {
            "amount": 2000,
            "currency": "usd",
            "payment_method_types": ["card"],
            "receipt_email": "customer@email.com"
            }
          </x-docs-code>
          <x-docs-code language="json">
            {
            "id": "pi_3OqP2KLkdIwHu7ix0nfVXmXa",
            "object": "payment_intent",
            "amount": 2000,
            "currency": "usd",
            "status": "requires_confirmation",
            "client_secret": "pi_3OqP2K_secret_abc123",
            "livemode": false
            }
          </x-docs-code>
          <x-docs-code language="bash">
            POST /api/stripe/v1/payment_intents/pi_3OqP2KLkdIwHu7ix0nfVXmXa/confirm
          </x-docs-code>
          <x-docs-code language="json">
            {
            "id": "pi_3OqP2KLkdIwHu7ix0nfVXmXa",
            "status": "succeeded",
            "amount": 2000,
            "charges": {
            "data": [{
            "paid": true,
            "payment_method_details": {
            "card": { "brand": "visa", "last4": "4242" },
            "type": "card"
            }
            }]
            }
            }
          </x-docs-code>

          <h3 class="docs-h3 mt-6">Refunds & transfers</h3>
          <x-docs-endpoints :endpoints="[
                    ['POST', '/refunds',        'Refund a payment intent'],
                    ['GET',  '/refunds/:id',    'Retrieve a refund'],
                    ['GET',  '/refunds',        'List refunds'],
                    ['POST', '/transfers',      'Create a transfer'],
                    ['GET',  '/transfers/:id',  'Retrieve a transfer'],
                    ['GET',  '/balance',        'Retrieve balance'],
                ]" />
        </section>

        {{-- Webhooks --}}
        <section id="webhooks">
          <h2 class="text-xl font-bold text-slate-900 mb-3">Webhooks</h2>
          <p class="text-slate-500 text-sm leading-relaxed mb-5">
            DevWallet fires webhooks to your registered endpoints with
            provider-accurate signatures. Your existing webhook verification
            code works without any changes.
          </p>

          <x-docs-table :headers="['Provider', 'Signature header', 'Algorithm']" :rows="[
                    ['Paystack',    'x-paystack-signature', 'HMAC-SHA512'],
                    ['Flutterwave', 'verif-hash',           'Static secret comparison'],
                    ['Stripe',      'stripe-signature',     'HMAC-SHA256 + timestamp'],
                ]" />

          <h3 class="docs-h3 mt-6">Verify a Paystack webhook</h3>
          <x-docs-code language="javascript">
            const crypto = require('crypto');

            app.post('/webhooks/paystack', express.raw({type: 'application/json'}), (req, res) => {
            const hash = crypto
            .createHmac('sha512', process.env.PAYSTACK_SECRET_KEY)
            .update(JSON.stringify(req.body))
            .digest('hex');

            if (hash !== req.headers['x-paystack-signature']) {
            return res.status(401).send('Invalid signature');
            }

            const { event, data } = req.body;

            if (event === 'charge.success') {
            // Payment confirmed — fulfil the order
            }

            res.sendStatus(200);
            });
          </x-docs-code>

          <h3 class="docs-h3 mt-6">Events</h3>
          <x-docs-table :headers="['Event', 'Fired when']" :rows="[
                    ['charge.success',    'Transaction is verified successfully'],
                    ['transfer.success',  'Transfer completes processing'],
                    ['transfer.failed',   'Transfer fails (force-fail or failure rate)'],
                ]" />
        </section>

        {{-- Simulation --}}
        <section id="simulation">
          <h2 class="text-xl font-bold text-slate-900 mb-3">Simulation</h2>
          <p class="text-slate-500 text-sm leading-relaxed mb-5">
            Every project has a Simulation panel — the feature real providers
            don't give you. Test edge cases without changing your integration code.
          </p>

          <h3 class="docs-h3">Failure rate</h3>
          <p class="text-slate-500 text-sm leading-relaxed mb-5">
            Set 0–100%. At 30%, roughly 3 in 10 verify calls return a failed
            transaction. Use this to test your error handling and retry UI.
          </p>

          <h3 class="docs-h3">Force next fail</h3>
          <p class="text-slate-500 text-sm leading-relaxed mb-5">
            Arms a one-shot failure. The next verify or confirm call returns
            a failure, then the flag clears automatically.
          </p>

          <h3 class="docs-h3">Transfer processing speed</h3>
          <x-docs-table :headers="['Setting', 'Delay', 'Use case']" :rows="[
                    ['Instant', '~0ms',  'Normal flow testing'],
                    ['Slow',    '~5s',   'Test loading states and pending UI'],
                    ['Timeout', '~30s',  'Test retry logic and timeout handling'],
                ]" />

          <h3 class="docs-h3 mt-6">Manual webhook trigger</h3>
          <p class="text-slate-500 text-sm leading-relaxed mb-5">
            Fire a <code class="docs-inline-code">charge.success</code>,
            <code class="docs-inline-code">transfer.success</code>, or
            <code class="docs-inline-code">transfer.failed</code> to your
            registered endpoints right now — using a real recent transaction
            as the payload. No API call required.
          </p>

          <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800">
            All simulation state resets instantly from the Simulation panel.
            Your API keys and transaction history are unaffected.
          </div>
        </section>

      </div>
    </main>

  </div>

  {{-- Scroll spy --}}
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const sections = document.querySelectorAll('section[id]');
      const navLinks = document.querySelectorAll('.docs-link');
      const main = document.getElementById('docs-main');

      main.addEventListener('scroll', function() {
        let current = '';
        sections.forEach(section => {
          if (section.offsetTop - main.scrollTop <= 80) {
            current = section.getAttribute('id');
          }
        });
        navLinks.forEach(link => {
          link.classList.toggle(
            'active',
            link.getAttribute('href') === '#' + current
          );
        });
      });

      navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute('href'));
          if (target) main.scrollTo({
            top: target.offsetTop - 24,
            behavior: 'smooth'
          });
        });
      });
    });
  </script>

</body>

</html>
