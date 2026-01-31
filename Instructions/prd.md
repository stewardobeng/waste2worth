# 1. Introduction
Design and build Waste2Worth, a hyper-local waste collection web application using plain PHP with a modern MVC architecture and MySQL. The platform must connect informal waste collectors with households, businesses, and municipalities via three separate interfaces: a collector portal, a client portal, and an admin/municipal dashboard. Required capabilities include secure role-based authentication, session management with CSRF protection, responsive UI with modern CSS and vanilla JavaScript, interactive mapping (Leaflet.js or Google Maps) for proximity-based matching, SMS integration capability, payment processing, analytics dashboards, and collector rewards tracking. The codebase must follow PSR standards, include environment-based configuration, reusable components, prepared statements, logging, validation, documentation, and an installation guide with schema scripts.

# 2. Product Specifications
## 2.1 User Roles & Access Control
- **Roles**: Collectors, Clients, Administrators/Municipal staff.
- **Authentication**: PHP sessions, password hashing via `password_hash()`, CSRF tokens on forms, role-based routing and permissions.
- **Session Security**: Regenerate session IDs post-login, enforce timeouts, IP/user-agent checks.

## 2.2 Collector Portal
- Registration with profile completion (personal info, service areas, waste types, location coordinates, availability schedule).
- Availability management (toggle status, calendar view).
- Interactive map to visualize assigned areas & requests.
- Pickup logging workflow:
  - View incoming requests (AJAX refresh).
  - Accept/decline.
  - Dual verification capture (collector confirmation, upload verification photo, timestamp).
- Notifications for new requests, confirmations (onscreen + SMS capability).
- Earnings dashboard: pickups completed, payments, reward progress, ratings.
- Profile management with secure image upload.

## 2.3 Client Portal
- Registration & profile (address, location coordinates, waste preferences, payment info placeholder).
- Search for nearby collectors using interactive map + filters (waste type, availability, rating).
- Request pickup flow: select collector or auto-match, schedule time, specify waste details, upload images, submit.
- Payment module: simple payment entry/logging (status, transaction ID) and receipts.
- Service confirmation: rate collector, review, confirm completion for dual verification.
- Notifications via dashboard + SMS option.
- Service history & invoices download.

## 2.4 Admin/Municipal Dashboard
- Overview metrics: total pickups, waste diverted, CO2 offset, active collectors/clients, payment statuses.
- Collector management: approve/reject registrations, edit profiles, suspend accounts.
- Service request oversight: reassign, monitor status, resolve disputes.
- Reward engine management: configure thresholds, view monthly bonus eligibility, export reports.
- Analytics:
  - Chart.js visualizations for recycling volumes, CO2 offset, waste type breakdown.
  - Collector performance metrics (response time, completion rate, ratings).
  - Geographic heat maps of collection activity (map overlay).
  - Payments & financial summaries.
- System logs & error monitoring view.

## 2.5 Core Platform Features
- **MVC Architecture**: Controllers handle requests, Models manage data with prepared statements, Views render responsive UI.
- **Database** (normalized tables):
  - `users` (role, auth, contact).
  - `collector_profiles` (user_id FK, location lat/lng, service areas, waste types, availability, documents).
  - `service_requests` (client_id, collector_id, status, schedule, waste details, coordinates, timestamps).
  - `completed_pickups` (service_request_id, collector_confirm_ts, client_confirm_ts, verification_media path).
  - `payments` (service_request_id, amount, method, status, transaction_ref, timestamps).
  - `ratings_reviews` (service_request_id, rating, comments).
  - `analytics_metrics` (aggregated stats, period, key metrics).
  - Supporting tables (notifications, rewards, sms_logs, files).
- **Interactive Map**: Leaflet.js or Google Maps with geolocation, clustering, real-time availability indicators, proximity search.
- **AJAX Search & Filtering**: Collector discovery, request updates, notifications without page reload.
- **Notification System**: In-app alerts + SMS integration (Twilio-compatible service layer).
- **Payment Processing Module**: Basic capture/logging, status updates, admin overrides.
- **Pickup Verification**: Dual confirmation workflow, secure storage of verification media.
- **Collector Rating & Review**: Post-completion feedback influencing profiles.
- **Reward Calculation Engine**: Track verified pickups, compute monthly bonuses, display recognition badges.
- **Analytics & Impact Measurement**: Chart.js graphs, CO2 offset calculations (configurable conversion factors), waste diversion stats, downloadable reports.

## 2.6 Security & Compliance
- Prepared statements for all DB operations.
- Input validation & sanitization (client + server).
- CSRF tokens, XSS prevention (escaping outputs), file upload validation (type/size), storage outside webroot.
- Role-based authorization checks in controllers/services.
- Error logging mechanism (file-based or syslog) with rotation.
- Environment configs for DB creds, API keys, SMS providers.

## 2.7 Documentation & Deployment
- Inline code comments, README with installation steps.
- Database schema setup SQL script.
- Basic installation guide covering environment configuration, dependencies, build steps.

# 3. User Experience
## 3.1 General UI
- Clean responsive layout using Flexbox/Grid, consistent color palette, accessible typography.
- Navigation per role, with dashboard summaries and quick actions.
- Mobile-first interactions: collapsible menus, touch-friendly controls.

## 3.2 Collector Flow
1. Register → verify via email/SMS → complete profile (map pin placement, service areas).
2. Dashboard shows availability toggle, active requests list (AJAX updates).
3. Accept request → navigate via map (directions link) → log pickup with verification photo/upload.
4. Confirm completion, view client confirmation status, rate client if needed.
5. Track rewards & earnings via widgets and charts.

## 3.3 Client Flow
1. Sign up → set address on map.
2. Use map search to find collectors (filters for waste type, distance).
3. Request pickup (form + file uploads) → receive confirmation notifications.
4. Make payment via module → see receipts.
5. After service, confirm completion, rate collector, view history.

## 3.4 Admin Flow
1. Login to dashboard with metrics summary cards and charts.
2. Manage collectors/clients via tables with filtering, bulk actions.
3. Monitor live map of requests, intervene as needed.
4. Run analytics reports, export CSV/PDF.
5. Configure rewards and review notifications logs.

# 4. Implementation Requirements
## 4.1 Technology Stack
- **Backend**: Plain PHP (>=8.x) with custom MVC adhering to PSR standards.
- **Database**: MySQL with normalized schema, indexing on FK and frequently queried columns.
- **Frontend**: HTML5, CSS3 (Flexbox/Grid), vanilla JavaScript, AJAX (Fetch/XHR).
- **Mapping**: Leaflet.js or Google Maps API.
- **Charts**: Chart.js for analytics.
- **SMS**: Twilio API or compatible service integration layer.
- **Sessions & CSRF**: Native PHP sessions, custom CSRF tokens per form.

## 4.2 Architecture & Code Organization
- Directories: `/app` (Controllers, Models, Services), `/views`, `/public`, `/config`, `/storage`, `/logs`.
- Service layer for business logic (matching, rewards, notifications).
- Reusable components (forms, tables, modals).
- Environment config (`.env`-like) with loader for DB, API keys.

## 4.3 Database & Data Handling
- Use prepared statements everywhere (PDO or mysqli).
- Proper indexing (e.g., `service_requests.status`, `collector_profiles.location` with spatial indexes if possible).
- Transactions for multi-step updates (pickup verification, payments).
- Data validation (PHP filter functions, custom validation classes).

## 4.4 Security & Performance
- Password hashing via `password_hash()` with `password_verify()`.
- CSRF tokens stored in session.
- Input sanitization (HTML escaping, file validation).
- File uploads stored securely with unique names, metadata in DB.
- Rate limiting for login and sensitive endpoints.
- Caching layer optional (for map data lists) using in-memory arrays or simple caching mechanism.
- Error handling with try/catch, centralized logger, user-friendly error pages.

## 4.5 Integrations & External Services
- SMS service class with configurable provider (Twilio).
- Map API key configuration.
- Payment module (placeholder gateway integration-ready) logging transactions.
- Email notifications via SMTP configuration.

## 4.6 Testing & Deployment
- Manual test plans per feature (auth, map search, pickup flow).
- Staging environment with separate config.
- Installation guide: prerequisites (PHP extensions, composer if used for autoloading), DB setup script, config steps.
- Deployment checklist (permissions for `/logs`, `/storage`, cron jobs for scheduled tasks like reward calculation).

## 4.7 Documentation & Support
- README with architecture overview, setup, env variables, run instructions.
- Inline PHPDoc comments for classes/methods.
- Schema diagram and ERD reference.
- Basic troubleshooting section (common errors, log locations).
