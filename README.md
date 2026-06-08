# 🚀 PHP Professional Reservation System

A complete, production-ready reservation/booking system built with **PHP 8+**, **MySQL/MariaDB**, and vanilla **JavaScript**. Designed for professionals — agencies, freelancers, and businesses who need a white-label booking solution for their clients.

---

## ✨ Features

### For End Users
- **Beautiful booking form** — modern, responsive UI works on all devices
- **Real-time availability** — select a date, see available time slots instantly
- **No account required** — book in seconds, get a confirmation code
- **Reservation lookup** — check or cancel your booking anytime
- **Email confirmation** — automatic notification when booked

### For Admins
- **Dashboard** — see all reservations at a glance
- **Stats cards** — today's count, pending, confirmed, cancelled
- **Status management** — update status from the dashboard instantly
- **Search & filter** — by date, status, customer name, or confirmation code
- **Pagination** — handles hundreds of reservations smoothly

### Security & Quality
- ✅ **CSRF protection** — all form submissions are token-verified
- ✅ **Input sanitization** — XSS prevention on all inputs
- ✅ **SQL injection prevention** — PDO prepared statements everywhere
- ✅ **Rate limiting** — blocks spam submissions by IP
- ✅ **Password hashing** — bcrypt with configurable cost
- ✅ **Session security** — HttpOnly, Secure, SameSite cookies
- ✅ **Validation** — client-side + server-side double validation
- ✅ **Business hours** — automatically restricts bookings outside operating times

### Architecture
- **PSR-4 autoloading** — clean, organized namespaced classes
- **MVC-inspired separation** — models, handlers, views
- **AJAX API** — slot loading and form submission without page reload
- **Singleton database** — efficient PDO connection management
- **Configurable** — environment variables or config file

---

## 📋 Requirements

| Requirement | Version |
|------------|---------|
| PHP | 8.0+ (recommended 8.2+) |
| MySQL | 5.7+ or MariaDB 10.3+ |
| Web Server | Apache (mod_rewrite) or Nginx |
| Extensions | PDO, PDO_MySQL, mbstring, JSON |

---

## 🚀 Quick Start

### 1. Install

```bash
# Clone or copy files to your web server
cp -r php-reservation-system /var/www/reservation/

# Set proper permissions
chmod -R 755 /var/www/reservation/
chmod -R 777 /var/www/reservation/public/api/  # if needed for file operations
```

### 2. Database Setup

```bash
# Create the database and tables
mysql -u root -p < /var/www/reservation/sql/schema.sql
```

Or import through phpMyAdmin or any MySQL client.

### 3. Configure

```bash
# Copy and edit the config file
cp /var/www/reservation/src/config/config.example.php /var/www/reservation/src/config/config.php

# Edit the database credentials and app settings
nano /var/www/reservation/src/config/config.php
```

Set at minimum:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'reservation_system');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('APP_URL', 'https://yourdomain.com');
define('APP_ENV', 'production');
define('APP_DEBUG', false);
```

### 4. Web Server Setup

#### Apache
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/reservation/public
    
    <Directory /var/www/reservation/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx
Copy `nginx.conf` to your sites-available directory and enable it.

### 5. Access

| Page | URL |
|------|-----|
| Booking Form | `https://yourdomain.com/` |
| Lookup | `https://yourdomain.com/lookup.php` |
| Admin Login | `https://yourdomain.com/admin/` |

### 6. Default Admin Credentials

> **⚠️ CHANGE IMMEDIATELY IN PRODUCTION**

| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `password` |

Login at `/admin/` and change the password in the database.

---

## 📁 Project Structure

```
php-reservation-system/
├── admin/
│   ├── index.php          # Admin login page
│   └── dashboard.php      # Admin dashboard
├── public/                # Web root (DocumentRoot)
│   ├── index.php          # Main booking form
│   ├── lookup.php         # Reservation lookup page
│   ├── .htaccess          # Apache rules
│   ├── api/
│   │   ├── slots.php      # AJAX: Get available time slots
│   │   └── submit.php     # AJAX: Submit reservation
│   └── assets/            # (Optional: CSS/JS/images)
├── src/
│   ├── autoload.php       # PSR-4 autoloader
│   ├── config/
│   │   ├── config.example.php  # Configuration template
│   │   └── Database.php        # PDO singleton
│   ├── handlers/
│   │   └── ReservationHandler.php  # Core business logic
│   └── includes/
│       ├── Security.php    # CSRF, sanitization, rate limiting
│       └── Validator.php   # Form validation
├── sql/
│   └── schema.sql          # Database schema + sample data
├── nginx.conf              # Nginx configuration
└── README.md               # This file
```

---

## 🔧 Configuration Options

All settings are in `src/config/config.php`:

| Setting | Default | Description |
|---------|---------|-------------|
| `TIME_SLOT_INTERVAL` | 30 | Minutes between each time slot |
| `MAX_ADVANCE_DAYS` | 90 | How far ahead bookings are allowed |
| `MIN_NOTICE_HOURS` | 2 | Minimum hours before booking |
| `MAX_GUESTS_PER_SLOT` | 20 | Maximum bookings per time slot |
| `BUSINESS_TIMEZONE` | Asia/Riyadh | Timezone for all operations |
| `BCRYPT_COST` | 12 | Password hashing strength |

Operating hours are stored in the database `settings` table. Default hours:

| Day | Hours |
|-----|-------|
| Mon–Thu | 9:00 AM – 6:00 PM |
| Fri | 10:00 AM – 4:00 PM |
| Sat | 10:00 AM – 2:00 PM |
| Sun | Closed |

---

## 📧 Email Notifications

To enable email notifications:
1. Edit `src/config/config.php` and set SMTP credentials
2. Or set environment variables:
   ```
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USER=your@email.com
   MAIL_PASS=your-app-password
   MAIL_FROM=noreply@yourdomain.com
   ```

For Gmail, you need an **App Password** (requires 2FA enabled).

---

## 🔒 Security Checklist for Production

- [ ] Change default admin password
- [ ] Set `APP_ENV` to `'production'` and `APP_DEBUG` to `false`
- [ ] Set `CSRF_TOKEN_SECRET` to a random 32+ character string
- [ ] Use HTTPS (SSL certificate)
- [ ] Secure the `/admin/` directory with additional auth if needed
- [ ] Set up regular MySQL backups
- [ ] Configure fail2ban for repeated failed admin logins
- [ ] Remove sample data from database

---

## 🎯 Use Cases

- **Service-based businesses** — consultations, appointments
- **Hospitality** — restaurant table booking, hotel reservations
- **Healthcare** — clinic appointments, doctor consultations
- **Coworking spaces** — meeting room bookings
- **Salons & spas** — service appointments
- **Agencies** — white-label booking solution for clients

---

## 🤝 Customization

Want to customize the look and feel?

1. **Colors** — edit the CSS variables in `public/index.php` (`:root` section)
2. **Services** — add/edit services through the database (`services` table)
3. **Logo** — change the logo in the header HTML
4. **Branding** — update `APP_NAME` in config
5. **Translations** — all text is inline in the PHP templates, easily editable

---

## 📝 License

MIT License — free to use, modify, and distribute for any project (personal or commercial).

Built with ❤️ for freelancers and agencies.
