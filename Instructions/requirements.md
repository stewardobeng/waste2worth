## 1. Project Overview
Design and develop “Waste2Worth,” a fully functional web application built with plain PHP using a modern MVC architecture and MySQL. The platform connects informal waste collectors with households, businesses, and municipalities through three interfaces: a collector portal, client portal, and admin/municipal dashboard. It must implement secure role-based authentication, interactive map-based collector discovery, pickup management, payments, analytics, SMS capability, and reward tracking. UI should be responsive, built with modern CSS and vanilla JavaScript, and integrate Leaflet.js or Google Maps for location services.

## 2. Functional Requirements
1. **User Roles & Authentication**
   - Secure registration/login for collectors, clients, administrators.
   - Password hashing using `password_hash()`.
   - Session management with CSRF protection.
   - Role-based access control enforcing permissions per interface.
2. **Collector Portal**
   - Profile setup with location coordinates, service areas, waste types, availability schedule.
   - Manage availability status (real-time updates).
   - Log pickups, upload verification photos, confirm completion.
   - View ratings, reviews, reward points/bonuses.
3. **Client Portal**
   - Search/map view of nearby collectors using proximity filtering (Leaflet.js/Google Maps).
   - Request pickup specifying waste details, preferred time, location.
   - View collector availability status.
   - Payment module to initiate and log transactions; confirm service completion (dual confirmation).
   - Rate and review collectors.
4. **Admin/Municipal Dashboard**
   - Manage users, collector profiles, service requests.
   - Analytics dashboard with Chart.js visualizations: recycling volumes, CO₂ offset, waste diverted, collector performance metrics, geographic heat maps.
   - Reward calculation engine: track verified pickups, compute monthly bonuses and recognitions.
   - Monitor payment transactions, pickups, and notifications.
5. **Service Requests & Pickups**
   - Service request creation, assignment/matching via proximity and availability.
   - Notification system for pickup requests, confirmations, status changes (AJAX updates).
   - Dual confirmation workflow for pickup verification (collector + client).
   - Completed pickups stored with timestamps, verification proof.
6. **Payments**
   - Simple payment processing workflow (e.g., mark as paid, payment method capture).
   - Transaction logging table linked to service requests.
7. **SMS Integration Capability**
   - Integrate with Twilio API (or equivalent) for SMS notifications/low-tech access (send request confirmations, updates).
8. **Search, Filtering & AJAX**
   - Real-time search/filter collectors (waste type, location, availability).
   - AJAX for seamless UI interactions (e.g., map updates, notifications).
9. **Notification System**
   - In-app alerts and optional SMS/email triggers for new requests, confirmations, rewards.
10. **Ratings & Reviews**
    - Clients rate/review collectors post-service; stored and displayed in collector profiles.
11. **Reward Engine**
    - Track verified pickups per collector; calculate monthly bonuses and recognition tiers.
12. **File Upload Handling**
    - Secure upload for profile images and verification photos.
13. **Installation & Documentation**
    - Basic installation guide and DB schema setup script.
14. **Inline Code Documentation**
    - Comment business logic, controllers, models, and services per PSR standards.

### Database Requirements
- Normalized tables for: users, collector_profiles, service_requests, completed_pickups, payments, analytics_metrics, ratings_reviews, rewards, notifications.
- Use prepared statements for all queries; proper indexing (e.g., geospatial indexes on coordinates).

## 3. Non-Functional Requirements
1. **Architecture**
   - Plain PHP with modern MVC pattern, service layer for business logic, reusable components, strict separation of concerns.
   - Environment-based configuration files (dev/test/prod).
   - Follows PSR coding standards.
2. **Security**
   - CSRF protection on forms, input validation/sanitization client & server side.
   - Password hashing via `password_hash()`.
   - Session hardening, SQL injection prevention through prepared statements.
   - Secure file upload handling (type/size validation, safe storage paths).
3. **Performance & Scalability**
   - Optimized queries with indexing.
   - AJAX updates for responsiveness.
   - Designed for real-world deployment, handling concurrent users.
4. **UI/UX**
   - Responsive design using modern CSS (Flexbox/Grid) and vanilla JS.
   - Works seamlessly across mobile and desktop.
   - Interactive map integration (Leaflet.js or Google Maps) with real-time availability indicators.
5. **Reliability & Logging**
   - Error logging mechanism.
   - Verification timestamps for auditability.
6. **Analytics & Visualization**
   - Chart.js for graphs; geographic heat maps for collection activity.
7. **Maintainability**
   - Comprehensive inline code documentation.
   - Modular services and components for ease of updates.

## 4. Dependencies and Constraints
1. **Technologies**
   - Plain PHP (no frameworks) with MVC structure.
   - MySQL database.
   - Frontend: HTML5, CSS3 (Flexbox/Grid), vanilla JS, AJAX.
   - Mapping: Leaflet.js or Google Maps API.
   - Charting: Chart.js.
   - SMS capability: Twilio API (or similar service) integration.
2. **Security & Compliance**
   - Must ensure prepared statements, CSRF tokens, secure sessions.
   - File uploads require sanitization and secure storage.
3. **Deployment Constraints**
   - Production-ready with installation guide and DB schema script.
   - Requires environment-specific configuration management.
4. **Integration Constraints**
   - Payment module is simple/logging-focused (no specific payment gateway mandated).
   - SMS integration must support collector/client notifications.
5. **Data Constraints**
   - Proper normalization of tables for users, collector profiles, service requests, pickups, payments, analytics.
   - Geolocation data stored for proximity searches; indexing required for performance.
