# Hustle — Trading Journal SaaS
## Complete Setup Handleiding

---

## Vereisten

| Tool | Versie |
|------|--------|
| PHP | ≥ 8.3 |
| Composer | ≥ 2.7 |
| Node.js | ≥ 20 |
| MySQL | ≥ 8.0 of PostgreSQL ≥ 15 |
| Redis | ≥ 7.0 |

---

## 1. Installatie

```bash
# Clone de repository
git clone https://github.com/jouw-org/hustle.git
cd hustle

# PHP dependencies
composer install

# Node dependencies
npm install

# Environment bestand
cp .env.example .env
php artisan key:generate
```

---

## 2. Database configureren

Maak een database aan:
```sql
CREATE DATABASE hustle CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Pas `.env` aan:
```env
DB_CONNECTION=mysql
DB_DATABASE=hustle
DB_USERNAME=root
DB_PASSWORD=jouw_wachtwoord
```

Migraties uitvoeren en seeden:
```bash
php artisan migrate --seed
```

Demo inloggegevens na seeding:
- **E-mail:** demo@hustle.app
- **Wachtwoord:** password

---

## 3. Google OAuth instellen

1. Ga naar [console.cloud.google.com](https://console.cloud.google.com)
2. Maak een nieuw project aan: "Hustle"
3. Activeer de Google+ API
4. Maak OAuth 2.0 credentials aan
5. Redirect URI: `http://localhost:8000/auth/google/callback`
6. Voeg toe aan `.env`:

```env
GOOGLE_CLIENT_ID=jouw_client_id
GOOGLE_CLIENT_SECRET=jouw_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback
```

---

## 4. Stripe instellen

1. Maak een account op [stripe.com](https://stripe.com)
2. Maak 2 subscription producten aan:
   - **Hustle Pro** — €29/maand
   - **Hustle Premium** — €59/maand
3. Kopieer de Price IDs
4. Stel webhooks in op: `https://jouw-domein.nl/stripe/webhook`
   - Events: `customer.subscription.*`, `invoice.payment_failed`

```env
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_PRICE_PRO=price_...
STRIPE_PRICE_PREMIUM=price_...
```

Lokaal webhooks testen met Stripe CLI:
```bash
stripe listen --forward-to localhost:8000/stripe/webhook
```

---

## 5. Queue & Storage

```bash
# Storage symlink
php artisan storage:link

# Queue worker starten
php artisan queue:work redis --queue=default --tries=3

# Scheduler (in productie via cron):
# * * * * * cd /pad/naar/hustle && php artisan schedule:run >> /dev/null 2>&1
```

---

## 6. Development starten

```bash
# Terminal 1: Laravel dev server
php artisan serve

# Terminal 2: Vite dev server
npm run dev

# Terminal 3: Queue worker
php artisan queue:work
```

Bezoek: **http://localhost:8000**

---

## 7. Productie deployment

### Server setup (Ubuntu 22.04 / Nginx)

```bash
# PHP-FPM
apt install php8.3-fpm php8.3-mysql php8.3-redis php8.3-zip php8.3-gd php8.3-mbstring php8.3-xml php8.3-curl

# Composer
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Node
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs
```

### Nginx configuratie

```nginx
server {
    listen 443 ssl http2;
    server_name hustle.app;

    root /var/www/hustle/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/hustle.app/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/hustle.app/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Deployment script

```bash
#!/bin/bash
cd /var/www/hustle

git pull origin main

composer install --no-dev --optimize-autoloader
npm ci && npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

php artisan queue:restart
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx

echo "✓ Hustle deployed!"
```

### Supervisor (queue workers)

```ini
[program:hustle-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hustle/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/hustle/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
supervisorctl reread
supervisorctl update
supervisorctl start hustle-worker:*
```

---

## 8. Projectstructuur

```
hustle/
├── app/
│   ├── Console/Commands/        # Artisan commands
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/            # Login, register, OAuth, Stripe webhook
│   │   │   └── Dashboard/       # Dashboard, Journal, Analytics, Calendar, AI, Subscriptions
│   │   ├── Middleware/          # HandleInertiaRequests
│   │   ├── Requests/            # Form validation (StoreTradeRequest, etc.)
│   │   └── Resources/           # API Transformers (TradeResource, etc.)
│   ├── Jobs/                    # GenerateAiInsightsJob
│   ├── Models/                  # User, Trade, Account, Strategy, Tag, AiInsight...
│   ├── Policies/                # TradePolicy, etc.
│   └── Services/                # AnalyticsService, AiInsightService, TradeService, StripeService
├── database/
│   ├── factories/               # Model factories
│   ├── migrations/              # Database schema
│   └── seeders/                 # DatabaseSeeder (90 dagen demo data)
├── resources/
│   ├── css/app.css              # TailwindCSS + custom components
│   └── js/
│       ├── app.ts               # Vue 3 + Inertia bootstrap
│       ├── components/
│       │   ├── charts/          # EquityChart, DonutChart, CalendarHeatmap
│       │   ├── common/          # Modal, StatCard, TradeTable, ToastContainer, InsightCard
│       │   ├── forms/           # TradeForm
│       │   └── layout/          # AppLayout, Sidebar, SidebarContent, Topbar
│       ├── composables/         # useFlash, useFormatters
│       ├── pages/               # Dashboard, Journal, Analytics, Calendar, AiInsights, Settings, Subscription, Auth
│       ├── stores/              # Pinia: auth, account, trade, ui
│       └── types/               # TypeScript types
└── routes/web.php               # All application routes
```

---

## 9. Feature Gating

Abonnementslimieten worden afgedwongen via `TradePolicy`:

| Feature | Free | Pro | Premium |
|---------|------|-----|---------|
| Trades/maand | 50 | Onbeperkt | Onbeperkt |
| Accounts | 1 | 5 | Onbeperkt |
| AI Inzichten | ✗ | ✓ | ✓ |
| CSV Export | ✗ | ✓ | ✓ |
| Geavanceerde Analytics | Basis | Volledig | Volledig |
| Doelen tracking | ✗ | ✓ | ✓ |
| Prioritaire AI | ✗ | ✗ | ✓ |

---

## 10. Omgevingsvariabelen overzicht

Zie `.env.example` voor alle beschikbare variabelen.

Kritieke variabelen voor productie:
- `APP_KEY` — gegenereerd via `php artisan key:generate`
- `APP_URL` — je productie URL
- `DB_*` — databaseverbinding
- `REDIS_*` — Redis voor queue en cache
- `GOOGLE_*` — Google OAuth
- `STRIPE_*` — Stripe betalingen

---

*Hustle — Gebouwd voor serieuze traders. © 2026*
