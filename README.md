# Bodh Gaya Taxi

This is one PHP website. Vercel serves the PHP pages and static frontend in the same project. Booking and contact enquiries are stored in MongoDB Atlas.

## Run locally

Install PHP 8.2 or later with the `mongodb` extension. From the project directory, create a local `.env` with `MONGODB_URI` and `MONGODB_DB` (see `.env.example`), then run:

```bash
php -S localhost:8000 router.php
```

Open <http://localhost:8000>. For an Apache/XAMPP subfolder installation, open `http://localhost/bodhgayataxi/`; the form URLs work in both layouts. Restart Apache after enabling a PHP extension. The form backend loads the local `.env` when it exists; hosting environment variables take precedence. Pages render without MongoDB configuration, but form submissions return a service-unavailable response until it is configured.

## Deploy on Vercel with MongoDB Atlas

1. Create a MongoDB Atlas cluster, a database user with read/write access, and an Atlas network access rule that permits connections from your deployment. Atlas IP allowlisting may require `0.0.0.0/0` if your Vercel functions have no fixed outbound IP; use a strong database password and limit that user's privileges to this database. Atlas does not need a manually created database or collection; MongoDB creates `bookings` and `contacts` on the first successful insert.
2. Copy your Atlas **Drivers** connection URI. URL-encode any reserved characters in the username/password. Keep the URI private.
3. Push this repository to GitHub. The `.gitignore` excludes `.env` files.
4. In [Vercel](https://vercel.com/new), import the repository with its root directory set to the repository root. Keep Node.js at 22.x (also declared in `package.json`).
5. In Vercel project **Settings → Environment Variables**, set `MONGODB_URI` to the Atlas connection URI and `MONGODB_DB` to `bodhgayataxi`. Apply them to Production and Preview as needed. Redeploy after adding/changing values.
6. Deploy. Check `/`, `/book_taxi.php`, and `/contact.php`. Submit one test enquiry from each form and confirm records in Atlas under `bodhgayataxi.bookings` and `bodhgayataxi.contacts`.

`vercel.json` uses the community `vercel-php` runtime. It routes existing root PHP pages through `api/index.php` and keeps static `assets/` and `images/` available. Booking and contact posts go to `api/booking.php` and `api/contact.php`. These are enquiries, not confirmed or paid bookings. A successful response means Atlas acknowledged the insert; there is no admin dashboard or email notification in this project.

## Project structure

- `*.php` and `includes/` — website pages and shared layout
- `api/index.php` — allowlisted page router for Vercel
- `api/booking.php`, `api/contact.php` — validated enquiry endpoints
- `includes/form_backend.php` — shared response and MongoDB write code
- `assets/js/book-ride.js`, `assets/js/contact.js` — form submission and feedback
- `vercel.json` — PHP function and static file routing
