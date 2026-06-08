# PHP Reservation System — Complete Guide

## For Your Friend (The One Who Sells & Sets It Up)

---

## PART 1: WHAT IS THIS?

A **booking website** you install for businesses so their customers can book online.

**Example:** You sell this to a salon in Jeddah. The salon's customers go to `salon-name.com/book`, pick a date and time, fill their name, click Confirm. You (the salon owner) log in at `salon-name.com/admin/` and see all bookings in one dashboard.

No monthly fees. No commission. The client owns it forever.

---

## PART 2: HOW TO INSTALL (For Hosting)

### Step 1 — Buy Hosting

Tell your friend to buy shared hosting. Cheap options:
- **Hostinger** — SAR 10-15/month
- **SaudiNet** — SAR 15-30/month
- **STC Hosting** — SAR 20-40/month

Make sure it has: **PHP 8.0+**, **MySQL**, **cPanel**.

### Step 2 — Upload Files

1. Log in to cPanel (the hosting control panel)
2. Open **File Manager**
3. Go to `public_html` folder
4. Click **Upload** → choose the zip file
5. Right-click the zip → **Extract**
6. Move everything from the extracted folder into `public_html`

### Step 3 — Create Database

1. In cPanel, open **MySQL Database Wizard**
2. Create a database name (e.g. `booking_db`)
3. Create a database user + password (e.g. `booking_user` / `StrongPass123!`)
4. Click "Add User to Database" → check **ALL PRIVILEGES**
5. Click "Make Changes"

### Step 4 — Import Database Schema

1. In cPanel, open **phpMyAdmin**
2. Click your database name on the left
3. Click the **Import** tab at the top
4. Click "Choose File" → select `sql/schema.sql` from the files
5. Click **Go**

### Step 5 — Edit Config

1. In File Manager, go to `src/config/config.php`
2. Right-click → **Edit**
3. Change these lines:
```
define('DB_HOST', 'localhost');
define('DB_NAME', 'booking_db');          ← your database name
define('DB_USER', 'booking_user');        ← your database user
define('DB_PASS', 'StrongPass123!');      ← your database password
define('APP_URL', 'https://salon-name.com');  ← your domain
define('APP_ENV', 'production');
define('APP_DEBUG', false);
```
4. Click **Save**

### Step 6 — Done!

| Page | URL |
|------|-----|
| Booking Form | `https://salon-name.com/` |
| Lookup Page | `https://salon-name.com/lookup.php` |
| Admin Login | `https://salon-name.com/admin/` |

**Admin Login:**
- Username: `admin`
- Password: `password`

⚠️ **Change the password immediately after first login!**

---

## PART 3: HOW TO USE (For Your Friend & The Client)

### For The Business Owner (Client)

#### Dashboard Overview
- **Stats cards** at the top: Today's bookings, Total, Pending, Confirmed, Cancelled
- **Table** below: All reservations with code, customer name, date, time, status
- **Search bar**: Search by name, email, or confirmation code
- **Status filter**: Show only Pending / Confirmed / Cancelled
- **Date filter**: Show bookings for a specific day

#### How to Manage Bookings
1. Log in to `/admin/`
2. See all bookings in the table
3. To change a booking's status: use the dropdown in the Status column
4. Click **Done** to save the change
5. To search: type name/email/code in search box → click Filter

#### How to Change Settings
1. Go to **Settings** in the sidebar
2. Change:
   - **Business Name** — appears on the booking form
   - **Email / Phone** — contact info
   - **Operating Hours** — set each day's open/close time, or mark a day "Closed"
   - **Max Days Ahead** — how far customers can book
   - **Min Notice** — how many hours before booking
   - **Slot Interval** — time between available slots (30min default)
3. Click **Save All Settings**

#### How to Add/Edit Services
- In Settings, scroll to "Services & Pricing"
- Click **+ Add Service**
- Fill: Name, Description, Category, Duration, Max Capacity, Price
- Click **Add Service**
- To edit: click **Edit** next to the service
- To delete: click the trash icon

#### How to Block Holidays
- In Settings, scroll to "Holidays & Date Exceptions"
- Click **+ Add Exception**
- Pick the date, select "Blocked", add a reason
- The system will not show any slots on that day

---

## PART 4: HOW TO SELL (Pricing Strategy)

### Who To Sell To

| Business Type | Example | Price Range |
|--------------|---------|-------------|
| 💇 Salons & barbershops | Men's salon, ladies' salon | SAR 1,500 - 3,000 |
| 🏥 Small clinics | Dental clinic, physiotherapy | SAR 2,000 - 4,000 |
| 🍽️ Restaurants & cafes | Small to medium | SAR 1,500 - 3,000 |
| 📅 Conference rooms | Coworking spaces | SAR 1,500 - 2,500 |
| 🎓 Training centers | Small institutes | SAR 2,000 - 3,000 |
| 📸 Photographers | Wedding, portrait | SAR 1,000 - 2,000 |
| 🏢 Real estate | Property viewings | SAR 1,500 - 2,500 |

### Three Pricing Models

#### Option 1: One-Time Payment (Best)
- **SAR 2,000** per client
- Includes: installation, setup, 1 hour training
- Client owns it forever
- Your friend makes SAR 10,000+ after 5 clients

#### Option 2: Monthly Subscription
- **SAR 300/month** per client
- Includes: hosting + maintenance + support
- Your friend hosts it on his own server
- After 10 clients = SAR 3,000/month passive income

#### Option 3: Hybrid (Recommended)
- **SAR 1,000 setup** + **SAR 150/month**
- Client pays once, plus small monthly for hosting
- Best of both worlds

### What To Say To Clients

> **"I have a professional online booking system for your business. Your customers can book online 24/7 — pick a date and time, book instantly. You get a full admin panel to manage everything. No more WhatsApp chaos, no missed calls, no double bookings. No monthly subscription fees like Calendly or SimplyBook. You pay once and it's yours."**

### Objection Handling

**Client: "Can't I just use WhatsApp?"**
> "WhatsApp works until you get 10+ messages a day. Then you lose track, miss messages, double-book clients. This keeps everything organized in one place. Your customers can book at 2 AM while you're sleeping."

**Client: "Isn't this complicated?"**
> "I set it up for you in 20 minutes. After that, you just log in and see your bookings. That's it."

**Client: "I already use Calendly"**
> "Calendly charges you $15-30 every month forever. This is a one-time payment. After 6 months, you've saved more than you paid."

**Client: "Can I change the prices / hours myself?"**
> "Yes. There's a Settings page. You change everything yourself — no need to call me."

---

## PART 5: WHAT YOUR FRIEND NEEDS FOR EACH CLIENT

Before installing, ask the client for:
1. **Business name** (what appears on the booking form)
2. **List of services** with prices (e.g. Haircut SAR 50, Beard trim SAR 30)
3. **Operating hours** for each day
4. **Phone number and email**
5. **A domain name** (optional — can use a subdomain)
6. **Any holidays** coming up

Then:
1. Install the system (20 minutes)
2. Log in to Settings → change business name, hours, services
3. Give client the admin login
4. Show them how to use it (10 minutes)
5. Done.

---

## PART 6: WHAT YOU CAN ADD LATER (Upsells)

After the client is happy, offer:

| Feature | What It Does | What To Charge |
|---------|-------------|----------------|
| WhatsApp notifications | Automatically send customer a WhatsApp when they book | +SAR 500 |
| Email confirmations | Send email with booking details | Free (already built) |
| Custom domain | Point their own domain to the system | Included |
| Mobile app | A simple app that opens the booking page | +SAR 1,000 |
| Reports & analytics | Monthly booking stats (most popular times, total revenue) | +SAR 500 |
| Multi-branch | One system for multiple locations | +SAR 1,000 |

---

## PART 7: SUPPORT — WHAT TO DO IF SOMETHING BREAKS

**"The booking form shows an error"**
→ Check database connection in `config.php`
→ Make sure MySQL is running

**"I can't log in to admin"**
→ Reset password in phpMyAdmin: open `admin_users` table → edit the hash
→ Or use SQL: `UPDATE admin_users SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE username = 'admin'`
(This resets password to "password")

**"Time slots aren't showing"**
→ Check operating hours in Settings
→ Make sure the date isn't in the past
→ Check if the date is blocked as a holiday

**"Client wants to change the color/logo"**
→ The colors are in `public/index.php` in the `<style>` section
→ Logo is in the `<header>` HTML — replace the text with an image tag
→ Or (for future) I can add logo upload in Settings

---

## Quick Reference Card

```
┌─────────────────────────────────────┐
│          QUICK REFERENCE            │
├─────────────────────────────────────┤
│                                     │
│  INSTALL                            │
│  1. Upload files to hosting         │
│  2. Create MySQL database           │
│  3. Import sql/schema.sql           │
│  4. Edit src/config/config.php      │
│  5. Open domain.com/admin/          │
│                                     │
│  ADMIN LOGIN                        │
│  URL:  /admin/                      │
│  User: admin                        │
│  Pass: password                     │
│                                     │
│  PAGES                              │
│  Booking:  domain.com/              │
│  Lookup:   domain.com/lookup.php    │
│  Admin:    domain.com/admin/        │
│                                     │
│  SETTINGS (in admin panel)          │
│  - Business name, email, phone      │
│  - Operating hours per day          │
│  - Services & prices                │
│  - Holidays & exceptions            │
│  - Booking rules (max days, etc)    │
│                                     │
│  PRICING                            │
│  Sell for SAR 1,500 - 3,000 one-time│
│  Or SAR 200 - 500/month             │
│  Or SAR 1,000 + SAR 150/month       │
│                                     │
└─────────────────────────────────────┘
```
