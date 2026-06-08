# 📘 THE BOOKING SYSTEM GUIDE

## For Anyone — Super Simple

---

### WHAT IS THIS?

This is a **website for taking bookings**.

Imagine a salon. The salon's customers want to book a haircut.
Instead of calling or sending WhatsApp, they open this website.
They pick a day and time. They write their name. Click "Confirm."
Done. The booking is saved.

The salon owner logs in to see all bookings.

That's all. Everything else is just extra.

---

## PART 1: WHAT THE CUSTOMER SEES

### The Booking Page

This is the main page. The customer opens it and sees:

![Booking Form]

There are boxes to fill:

| Box | What to put |
|-----|-------------|
| First Name | Ahmed |
| Last Name | Al-Ghamdi |
| Email | ahmed@email.com |
| Phone | +966 55 123 4567 |

Then below:

| What to pick | How |
|-------------|-----|
| Service | Dropdown: "Haircut - SAR 50" or "Beard trim - SAR 30" |
| Date | Click the date picker, pick a day |
| Time | Click a time button like "10:00 AM" |
| Guests | How many people (usually 1) |

Then click the big blue button: **Confirm Reservation**.

A code appears like: `A1B2C3D4`. Save this code.

### What The Customer Gets

After booking, the customer gets a **confirmation code**.
They can go to the **Lookup Page** and enter this code to:
- See their booking details
- Cancel if they changed their mind

**That's it for the customer. 3 steps:**
1. Fill name + date + time
2. Click Confirm
3. Save the code

---

## PART 2: WHAT THE BUSINESS OWNER SEES (Admin)

### How To Log In

1. Open your website
2. Go to `/admin/` at the end (like `yourdomain.com/admin/`)
3. A login page appears

![Admin Login]

4. Type:
   - **Username:** `admin`
   - **Password:** `password`
5. Click **Sign In**

**IMPORTANT:** Change the password after first login!

---

### The Dashboard (Main Page After Login)

![Dashboard]

When you log in, you see this:

**At the top:** 5 colored boxes with numbers
| Box | What it means |
|-----|---------------|
| 0 TODAY | How many bookings today |
| 2 TOTAL | All bookings ever |
| 2 PENDING | Waiting for you to confirm |
| 0 CONFIRMED | Confirmed bookings |
| 0 CANCELLED | Cancelled bookings |

**Below:** A big table with all bookings.
Each row is one booking. You can see:
- Code (like `A1B2C3D4`)
- Customer name
- Email
- Date
- Time
- Number of guests
- Status (Pending / Confirmed / Cancelled)

**To change a booking status:**
1. Find the booking in the table
2. Click the dropdown in the "Status" column
3. Pick: Pending → Confirmed → Completed → Cancelled
4. Click the **Done** button

**To search:**
1. Type a name, email, or code in the search box
2. Click **Filter**

**To see only pending:**
- Click **Pending** in the left sidebar

**To see today's bookings:**
- Click **Today's Bookings** in the left sidebar

---

## PART 3: HOW TO CHANGE SETTINGS

### Where Is Settings?

Look at the left sidebar. Click **Settings**.

![Settings Page]

### Change Business Name

1. Find "Business Name"
2. Delete the old name
3. Type your business name
4. Click **Save All Settings**

### Change Hours

1. Find "Operating Hours"
2. Each day has a row: Monday, Tuesday, etc.
3. Click the time boxes to change open/close time
4. If the day is closed: check the "Closed" box
5. Click **Save All Settings**

### Add A New Service

A "service" is what customers book.
Like "Haircut - SAR 50" or "Meeting Room - SAR 200"

1. In Settings, scroll to "Services & Pricing"
2. Click **+ Add Service**
3. Fill:
   - **Name:** What the service is called
   - **Price:** How much it costs in SAR
   - **Duration:** How many minutes it takes
   - **Category:** What type (General, Consultation, Room, etc.)
4. Click **Add Service**

### Edit Or Delete A Service

- **Edit:** Click the ✏️ button next to the service
- **Delete:** Click the 🗑️ button

### Block A Holiday

1. In Settings, find "Holidays & Date Exceptions"
2. Click **+ Add Exception**
3. Pick the date
4. Select "Blocked"
5. Write a reason (e.g. "National Day")
6. Click **Add Exception**

Now no one can book on that day.

---

## PART 4: HOW TO SELL THIS TO CLIENTS

### Who Needs This

Any business where customers book time:
- Salons & barbershops
- Small clinics
- Restaurants
- Meeting rooms
- Photographers
- Consultants
- Training centers

### What To Say To A Client

> "I have a booking website for your business. Your customers go online, pick a time, and book. No more phone calls. No more WhatsApp chaos. You get a dashboard to see everything. It's yours forever — no monthly fees."

### How Much To Charge

| What you do | How much |
|-------------|----------|
| Install + setup + 1 hour training | SAR 1,500 - 3,000 (one time) |
| Or host it for them monthly | SAR 200 - 500/month |
| Or both: setup + monthly hosting | SAR 1,000 + SAR 150/month |

### What The Client Needs To Give You

1. Their business name
2. List of services with prices (e.g. Haircut SAR 50)
3. What days/hours they're open
4. Their phone and email
5. A domain name (optional)

---

## PART 5: QUICK START — Your First Time

### Install in 10 Minutes

**You need:**
- Hosting (Hostinger / SaudiNet / any)
- The zip file I gave you

**Step 1:** Log in to your hosting cPanel

**Step 2:** Open "File Manager" → go to `public_html`

**Step 3:** Upload the zip file → Right click → Extract

**Step 4:** Open "MySQL Database Wizard"
- Create database: `booking_db`
- Create user: `booking_user`
- Password: anything strong
- Add user to database → check ALL boxes

**Step 5:** Open "phpMyAdmin"
- Click your database
- Click Import tab
- Choose `sql/schema.sql` from the files
- Click Go

**Step 6:** Edit `src/config/config.php`
- Change DB_NAME, DB_USER, DB_PASS to what you made
- Change APP_URL to your domain
- Save

**Step 7:** Open your domain in a browser. Done.

### Admin Login

Open: `yourdomain.com/admin/`
User: `admin`
Password: `password`

---

## PART 6: PROBLEMS & FIXES

| Problem | Fix |
|---------|-----|
| "I forgot the admin password" | Open phpMyAdmin → admin_users table → change password_hash to `$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi` (resets to "password") |
| "The page shows an error" | Check `config.php` — database name/user/pass must be correct |
| "No time slots showing" | Go to Settings → check Operating Hours → check the date isn't in the past |
| "Customers say the page is slow" | It's fine. That's normal. |
| "I want to change colors" | Tell me. I can do it. |

---

## QUICK CARD

```
┌─────────────────────────────────────────────┐
│            QUICK REFERENCE CARD              │
├─────────────────────────────────────────────┤
│                                             │
│  BOOKING PAGE    → yourdomain.com/          │
│  LOOKUP PAGE     → yourdomain.com/lookup.php│
│  ADMIN LOGIN     → yourdomain.com/admin/    │
│                                             │
│  ADMIN USERNAME:  admin                     │
│  ADMIN PASSWORD:  password                  │
│                                             │
│  HOW TO SEE BOOKINGS:                       │
│  1. Go to yourdomain.com/admin/             │
│  2. Log in                                  │
│  3. See all bookings in the table           │
│                                             │
│  HOW TO CHANGE SETTINGS:                    │
│  1. Log in to admin                         │
│  2. Click "Settings" in sidebar             │
│  3. Change what you want                    │
│  4. Click "Save All Settings"               │
│                                             │
│  HOW TO ADD A SERVICE:                      │
│  Settings → + Add Service → Fill → Add      │
│                                             │
│  HOW TO BLOCK A HOLIDAY:                    │
│  Settings → + Add Exception → Pick date     │
│                                             │
│  PRICE TO SELL: SAR 1,500 - 3,000           │
│  OR: SAR 200 - 500/month                    │
└─────────────────────────────────────────────┘
```

---

## One More Thing

If anything confuses you or your friend, just ask me.
I can explain it again in a different way.
I can change the colors, the text, the prices, anything.
That's what I'm here for.
