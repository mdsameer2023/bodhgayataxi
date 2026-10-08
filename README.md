# Bodh Gaya Taxi — Taxi Booking Enquiry Website

A PHP website for Bodh Gaya taxi services, airport transfers, local and outstation cabs, car rental, and tours. Visitors can send booking and contact enquiries. PHP validates each form and saves accepted enquiries to MongoDB Atlas.

## Application URLs

- PHP development server: <http://localhost:8000>
- Local Apache/XAMPP subfolder: <http://localhost/bodhgayataxi/>
- Production: the URL assigned to the project after its Vercel deployment

Frontend pages and PHP endpoints are served from **one Vercel project**. There is no separate React application or Render backend.

## Tech Stack

| Layer | Technology |
| --- | --- |
| Frontend | PHP-rendered HTML, CSS, JavaScript, Bootstrap, jQuery, and bundled theme assets |
| Backend | PHP 8.2+ locally; PHP 8.3 through the `vercel-php@0.7.4` community runtime on Vercel |
| Database | MongoDB Atlas, accessed through the PHP `mongodb` extension and `MongoDB\Driver` |
| Deployment | Vercel Functions for PHP pages and form endpoints; Vercel serves static assets |

Node.js 22.x is declared in `package.json` for the Vercel runtime build. It is not a separate Node.js application server.

## Architecture

```text
Visitor
  |
  +--> PHP pages: index.php, services, taxi, booking, contact
  |      +--> includes/ for shared page sections
  |      +--> assets/ and images/ for frontend files
  |
  +--> Booking or contact form
         +--> jQuery AJAX POST
                +--> PHP endpoint in api/
                       +--> validation in PHP
                       +--> MongoDB Atlas
                              +--> bookings or contacts collection
```

On Vercel, `api/index.php` allows the public root-level PHP pages and loads them from the project root. `vercel.json` routes those pages and the two form endpoints. The frontend calls the endpoints on the same website origin.

## Request Flow

```text
Visitor fills a form -> JavaScript sends POST -> PHP checks method and fields
  -> MongoDB acknowledges insert -> PHP returns JSON message
  -> Page shows success and resets the form
```

Invalid input returns HTTP 422. Missing database configuration or a failed database write returns HTTP 503; the form then displays an error and keeps the visitor's input. A success message is sent only after MongoDB acknowledges the write.

## Main Workflows

1. **Browse services:** Visitors view the home page and individual taxi, rental, transfer, tour, and information pages. Shared header, footer, and page sections live in `includes/`.
2. **Booking enquiry:** The home page and `/book_taxi.php` collect name, email, vehicle/package selection, passenger count, pickup, destination, ride date, and time. The backend validates these values and inserts a record into `bookings` with `status: "new"` and a creation timestamp.
3. **Contact enquiry:** `/contact.php` collects first name, last name, email, phone, and message. The backend validates them and inserts a record into `contacts` with `status: "new"` and a creation timestamp.

Both forms include a hidden `website` field to reject filled spam submissions. The backend imposes a 16 KB request-body limit and accepts POST only.

## Database

The database name comes from `MONGODB_DB` (normally `bodhgayataxi`). Collections are created by MongoDB on their first successful insert.

| Collection | Stored fields |
| --- | --- |
| `bookings` | `name`, `email`, `vehicle`, `passengers`, `start_destination`, `end_destination`, `ride_date`, `ride_time`, `status`, `created_at` |
| `contacts` | `first_name`, `last_name`, `email`, `phone`, `message`, `status`, `created_at` |

The ride date is entered as `DD/MM/YYYY` and time as `HH:mm`. A booking is an **enquiry**; submitting it does not confirm a cab or take payment.

## API Endpoints

| Method | Path | Result |
| --- | --- | --- |
| `GET` | `/` or an allowed root `*.php` page | Rendered website page |
| `POST` | `/api/booking.php` | Validates and saves a booking enquiry; JSON response |
| `POST` | `/api/contact.php` | Validates and saves a contact enquiry; JSON response |

The form endpoints return HTTP 405 for other methods. The PHP page router returns 404 for pages outside its allowlist.

## Setup

### Requirements

- PHP 8.2 or later with the `mongodb` extension enabled
- A web server such as Apache or PHP's development server
- A reachable MongoDB Atlas cluster and a database user with write access

### MongoDB Atlas

1. In Atlas, create or select a cluster. Create a **Database Access** user with a strong password and read/write access to the intended database.
2. Configure **Network Access** to allow connections from the environment running PHP. Vercel functions without fixed outbound IPs may need a broad Atlas IP access rule such as `0.0.0.0/0`; restrict the database user's privileges and keep its password private.
3. Under **Connect → Drivers**, copy the `mongodb+srv://...` connection string. Replace its username and password. URL-encode reserved characters in either value.
4. Use `bodhgayataxi` as `MONGODB_DB`, or choose another database name. Successful submissions create `bookings` and `contacts` there.

### Environment Variables

Copy `.env.example` to `.env` for local development and replace its placeholders:

```dotenv
MONGODB_URI=mongodb+srv://USERNAME:PASSWORD@YOUR-CLUSTER.mongodb.net/?retryWrites=true&w=majority
MONGODB_DB=bodhgayataxi
```

`MONGODB_URL` is accepted as a fallback when `MONGODB_URI` is absent. The local form backend loads `.env`; environment variables set by the host take precedence. `.env` is Git-ignored. Never commit the real connection string or put it in frontend JavaScript.

### Run Locally

From the project root:

```bash
php -S localhost:8000 router.php
```

Open <http://localhost:8000>. With Apache/XAMPP, place the project under its document root and open <http://localhost/bodhgayataxi/>. Restart Apache after enabling the MongoDB PHP extension. The local `router.php` and `.htaccess` prevent direct access to local secret files.

## Deploy on Vercel

1. Commit and push the project to GitHub. Include `api/`, `includes/`, `assets/`, `images/`, `vercel.json`, and `package.json`. Keep `.env` out of Git.
2. At [vercel.com/new](https://vercel.com/new), import the GitHub repository. Select **Other** as the framework and the repository root (`./`) as the root directory. Use Node.js 22.x.
3. In **Project → Settings → Environment Variables**, add `MONGODB_URI` and `MONGODB_DB` for **Production**. Add them to **Preview** too if preview deployments should accept enquiries.
4. Deploy or redeploy after setting the variables. Check `/`, `/book_taxi.php`, and `/contact.php` on the assigned URL.
5. Submit clearly labelled test enquiries. In Atlas **Data Explorer**, open the database named by `MONGODB_DB` and inspect `bookings` and `contacts`. A live insert test is required to confirm the deployed site's database access.

Vercel uses the community PHP runtime configured in `vercel.json`. The production deployment has not been verified until its live URL and form submissions have been checked. See [Vercel PHP runtime documentation](https://github.com/vercel-community/php) and [Atlas connection documentation](https://www.mongodb.com/docs/atlas/connect-to-database-deployment/).

## Checks

Syntax-check PHP files locally with `php -l` and check the form scripts with `node --check assets/js/book-ride.js` and `node --check assets/js/contact.js`. Submit one valid test enquiry through each form and confirm it appears in Atlas. During project verification, 34 PHP files passed syntax checks, 22 public pages rendered through the PHP router, and local booking/contact writes were read back from Atlas. Those checks do not replace a live Vercel deployment test.

## Project Structure

```text
bodhgayataxi/
|-- index.php and other root PHP pages
|-- api/
|   |-- index.php           # Vercel page router
|   |-- booking.php         # Booking enquiry endpoint
|   `-- contact.php         # Contact enquiry endpoint
|-- includes/
|   |-- form_backend.php    # Validation, responses, MongoDB write
|   `-- ...                 # Shared page sections
|-- assets/
|   |-- js/book-ride.js
|   |-- js/contact.js
|   `-- ...                 # Styles, scripts, fonts, images
|-- images/
|-- .env.example
|-- .htaccess
|-- router.php             # Local PHP development router
|-- vercel.json            # PHP runtime and routes
|-- package.json           # Node version for Vercel build
`-- README.md
```

## Design Decisions and Known Limitations

- PHP renders both frontend pages and backend form responses in one deployment. No cross-origin API configuration is needed.
- The PHP MongoDB driver writes directly to Atlas; there is no Mongoose or Composer dependency.
- The website has no admin dashboard, authentication, email notification, online payment, or automatic booking confirmation. Enquiries are reviewed in Atlas Data Explorer.
- The site stores `created_at` as a MongoDB UTC timestamp and keeps the visitor-entered ride date/time as strings.
- Vercel functions do not provide permanent writable file storage. Persist any future uploads in an external storage service.

## Troubleshooting

| Problem | Check |
| --- | --- |
| Page works but a form returns HTTP 503 | Verify `MONGODB_URI`, `MONGODB_DB`, MongoDB extension, Atlas user permissions, and Atlas Network Access. Redeploy after changing Vercel variables. |
| Booking returns HTTP 422 | Check all required fields, vehicle selection, and a valid future date in `DD/MM/YYYY` format. |
| Form shows success but record is not visible | Refresh Atlas Data Explorer and open the database named by `MONGODB_DB`; bookings go to `bookings`, contacts to `contacts`. |
| PHP pages fail on Vercel | Check the deployment logs, Node.js 22.x, `vercel.json`, and that all `api/` files were pushed. |
| Local forms fail after PHP installation | Ensure the local PHP `mongodb` extension matches the PHP version, thread safety, and architecture; restart Apache. |
