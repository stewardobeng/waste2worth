```markdown
# Waste2Worth REST API Documentation

## 1. API Overview
Waste2Worth is a hyper-local waste collection platform connecting informal collectors with households, businesses, and municipalities. This REST API powers the platform’s collector, client, and admin interfaces built on plain PHP (modern MVC) and MySQL.

- **Base URL (Production)**: `https://api.waste2worth.org/v1`
- **Content Type**: `application/json; charset=utf-8`
- **Authentication**: Token-based (JWT) issued after login, required for all endpoints except `/auth/login` and `/auth/register`. Include token in the `Authorization` header:  
  `Authorization: Bearer <jwt-token>`

All requests must include a valid CSRF token when made from first-party web clients. CSRF token is issued during session initialization via `/auth/csrf-token`.

---

## 2. Endpoints

### 2.1 Authentication

#### `POST /auth/register`
Register a new user (collector or client).

- **Body Parameters**
  | Field            | Type   | Required | Description                               |
  |------------------|--------|----------|-------------------------------------------|
  | `full_name`      | string | ✓        | User's full name                          |
  | `email`          | string | ✓        | Unique email                              |
  | `password`       | string | ✓        | Minimum 8 characters                      |
  | `role`           | string | ✓        | One of `collector`, `client`              |
  | `phone`          | string | ✓        | E.164 formatted phone number              |
  | `preferred_lang` | string |          | ISO 639-1 code                            |

- **Request Example**

```http
POST /auth/register
Content-Type: application/json

{
  "full_name": "Sifa Mutoni",
  "email": "sifa.mutoni@example.com",
  "password": "StrongPass#2024",
  "role": "collector",
  "phone": "+250788123456",
  "preferred_lang": "en"
}
```

- **Responses**
  - `201 Created`
  ```json
  {
    "message": "Registration successful. Please verify your phone via SMS code.",
    "user_id": 1815
  }
  ```
  - `400 Bad Request`
  ```json
  {
    "error": "ValidationError",
    "details": {
      "email": "Email already registered"
    }
  }
  ```

---

#### `POST /auth/login`
Login and receive JWT + session details.

- **Body Parameters**
  | Field      | Type   | Required | Description             |
  |------------|--------|----------|-------------------------|
  | `email`    | string | ✓        | Registered email        |
  | `password` | string | ✓        | Account password        |
  | `role`     | string | ✓        | `collector`, `client`, `admin` |

- **Response**
  - `200 OK`
  ```json
  {
    "token": "eyJhbGciOiJIUzI1NiIs...",
    "token_type": "Bearer",
    "expires_in": 3600,
    "user": {
      "id": 1815,
      "full_name": "Sifa Mutoni",
      "role": "collector",
      "collector_profile_complete": false
    }
  }
  ```
  - `401 Unauthorized`
  ```json
  {
    "error": "InvalidCredentials",
    "message": "Email or password is incorrect."
  }
  ```

---

#### `GET /auth/csrf-token`
Fetch CSRF token for browser-based sessions.

```
GET /auth/csrf-token
Authorization: Bearer <token>

200 OK
{
  "csrf_token": "0b6c59a5c8f228..."
}
```

---

### 2.2 Collector Profile

#### `GET /collectors/me`
Fetch logged-in collector profile.

- **Authorization**: Collector role

- **Response**
```json
{
  "collector_id": 1815,
  "user": {
    "full_name": "Sifa Mutoni",
    "email": "sifa.mutoni@example.com",
    "phone": "+250788123456"
  },
  "location": {
    "lat": -1.9441,
    "lng": 30.0619,
    "service_radius_km": 5
  },
  "waste_types": ["plastics", "organics"],
  "availability_status": "available",
  "profile_image_url": "https://cdn.../collectors/1815.jpg",
  "rating": 4.87,
  "reviews_count": 56
}
```

---

#### `PUT /collectors/me`
Update profile, service areas, waste types.

- **Body Parameters** (partial updates allowed)
  | Field                    | Type               | Description |
  |--------------------------|--------------------|-------------|
  | `location.lat`           | float              | Latitude    |
  | `location.lng`           | float              | Longitude   |
  | `service_radius_km`      | integer            | 1–30 km     |
  | `waste_types`            | array[string]      | e.g. `["plastics","metals"]` |
  | `availability_status`    | string             | `available`, `busy`, `offline` |
  | `service_areas`          | array[geojson]     | Optional polygons |
  | `bio`                    | string (max 500)   |             |

- **Request Example**
```json
{
  "location": {"lat": -1.947, "lng": 30.089},
  "service_radius_km": 8,
  "waste_types": ["plastics", "organics", "glass"],
  "availability_status": "available"
}
```

- **Responses**
  - `200 OK`
  ```json
  {
    "message": "Profile updated successfully",
    "updated_at": "2024-05-12T09:45:18Z"
  }
  ```
  - `422 Unprocessable Entity`
  ```json
  {
    "error": "ValidationError",
    "details": {
      "service_radius_km": "Must be between 1 and 30"
    }
  }
  ```

---

### 2.3 Collector Availability & Verification

#### `POST /collectors/me/availability`
Toggle availability in real time.

- **Body**
  | Field      | Type   | Required |
  |------------|--------|----------|
  | `status`   | string | ✓ (`available`, `busy`, `offline`) |

- **Response**
```json
{
  "status": "available",
  "updated_at": "2024-05-12T10:05:11Z"
}
```

---

#### `POST /collectors/me/verification-photo`
Upload verification photo at pickup site.

- **Headers**: `Content-Type: multipart/form-data`
- **Body**
  | Field      | Type     | Description                     |
  |------------|----------|---------------------------------|
  | `pickup_id`| integer  | Associated pickup               |
  | `photo`    | file jpg/png max 5MB |

- **Response**
```json
{
  "message": "Photo uploaded successfully",
  "photo_url": "https://cdn.../pickups/9921/collector.jpg"
}
```

---

### 2.4 Client Collector Discovery

#### `GET /collectors/search`
Search collectors by location and filters (AJAX-friendly).

- **Query Params**
  | Param            | Type   | Required | Description |
  |------------------|--------|----------|-------------|
  | `lat`            | float  | ✓        | Current latitude |
  | `lng`            | float  | ✓        | Current longitude |
  | `radius_km`      | int    |          | Default 5 km |
  | `waste_type`     | string |          | Filter by waste type |
  | `availability`   | string |          | `available`, `busy` |
  | `min_rating`     | float  |          | 1–5 |

- **Success**
```json
{
  "total": 2,
  "collectors": [
    {
      "collector_id": 1815,
      "name": "Sifa Mutoni",
      "distance_km": 1.2,
      "availability": "available",
      "waste_types": ["plastics", "organics"],
      "rating": 4.9,
      "location": {"lat": -1.9441, "lng": 30.0619}
    },
    {
      "collector_id": 1940,
      "name": "Eric Tuyisenge",
      "distance_km": 3.8,
      "availability": "busy",
      "waste_types": ["plastics", "glass"],
      "rating": 4.6,
      "location": {"lat": -1.949, "lng": 30.058}
    }
  ]
}
```

---

### 2.5 Service Requests

#### `POST /service-requests`
Client creates pickup request.

- **Body**
  | Field             | Type         | Required | Description |
  |-------------------|--------------|----------|-------------|
  | `collector_id`    | int          | ✓        |
  | `scheduled_at`    | datetime ISO | ✓        |
  | `pickup_address`  | string       | ✓        |
  | `geo.lat`         | float        | ✓        |
  | `geo.lng`         | float        | ✓        |
  | `waste_types`     | array[string]| ✓        |
  | `estimated_weight_kg` | float    |          |
  | `notes`           | string       |          |
  | `requires_sms_confirmation` | boolean |     |

- **Example Request**
```json
{
  "collector_id": 1815,
  "scheduled_at": "2024-05-12T15:00:00Z",
  "pickup_address": "12 Main St, Kigali",
  "geo": {"lat": -1.943, "lng": 30.059},
  "waste_types": ["plastics", "organics"],
  "estimated_weight_kg": 8.5,
  "notes": "Please call 10 minutes before arrival.",
  "requires_sms_confirmation": true
}
```

- **Responses**
  - `201 Created`
  ```json
  {
    "request_id": 6021,
    "status": "pending",
    "collector_eta_minutes": 20,
    "sms_notified": true
  }
  ```
  - `409 Conflict`
  ```json
  {
    "error": "CollectorUnavailable",
    "message": "Collector is already booked for the selected time."
  }
  ```

---

#### `GET /service-requests/{id}`
Fetch request details.

- **Path Param**: `id` (int)
- **Response**
```json
{
  "request_id": 6021,
  "client_id": 9004,
  "collector_id": 1815,
  "status": "confirmed",
  "scheduled_at": "2024-05-12T15:00:00Z",
  "pickup_address": "12 Main St, Kigali",
  "waste_types": ["plastics", "organics"],
  "estimated_weight_kg": 8.5,
  "notes": "Please call 10 minutes before arrival.",
  "confirmation": {
    "client_confirmed_at": "2024-05-12T15:05:33Z",
    "collector_confirmed_at": "2024-05-12T14:55:11Z"
  },
  "payment_status": "pending"
}
```

---

#### `PATCH /service-requests/{id}/status`
Update request status (`pending`, `confirmed`, `in_progress`, `completed`, `canceled`).

- **Body**
  | Field    | Type   | Required |
  |----------|--------|----------|
  | `status` | string | ✓        |
  | `reason` | string |          | required if canceling |

- **Response**
```json
{
  "request_id": 6021,
  "status": "in_progress",
  "updated_by": 1815,
  "updated_at": "2024-05-12T14:55:11Z"
}
```

---

### 2.6 Pickup Verification

#### `POST /pickups/{id}/verify`
Dual confirmation by collector and client.

- **Body**
  | Field                | Type    | Required | Description |
  |----------------------|---------|----------|-------------|
  | `actor_role`         | string  | ✓        | `collector` or `client` |
  | `pin_code`           | string  |          | Optional 6-digit code   |
  | `signature_base64`   | string  |          | Optional digital sign    |
  | `notes`              | string  |          |                          |

- **Response**
```json
{
  "pickup_id": 9921,
  "collector_confirmed": true,
  "client_confirmed": false,
  "verification_status": "pending-client",
  "timestamp": "2024-05-12T15:10:04Z"
}
```

When both confirm, status becomes `verified`, triggering payment workflow.

---

### 2.7 Payments

#### `POST /payments`
Initiate payment for a completed pickup.

- **Body**
  | Field            | Type   | Required |
  |------------------|--------|----------|
  | `pickup_id`      | int    | ✓        |
  | `amount`         | float  | ✓        |
  | `currency`       | string | ✓ (ISO 4217) |
  | `method`         | string | ✓ (`mobile_money`, `card`, `cash`) |
  | `transaction_reference` | string | optional external reference |

- **Response**
```json
{
  "payment_id": 4102,
  "pickup_id": 9921,
  "status": "processing",
  "method": "mobile_money",
  "amount": 4.50,
  "currency": "USD",
  "created_at": "2024-05-12T15:15:21Z"
}
```

Errors include insufficient balance, duplicate transaction.

---

#### `GET /payments/{id}`
Retrieve payment + transaction logs.

```json
{
  "payment_id": 4102,
  "pickup_id": 9921,
  "status": "completed",
  "amount": 4.5,
  "currency": "USD",
  "method": "mobile_money",
  "transaction_log": [
    {"status": "processing", "timestamp": "2024-05-12T15:15:21Z"},
    {"status": "completed", "timestamp": "2024-05-12T15:16:02Z", "provider_reference": "MOMO-884421"}
  ]
}
```

---

### 2.8 Notifications & SMS

#### `GET /notifications`
List notifications for current user (pagination supported).

- **Query**: `page`, `per_page`
- **Response**
```json
{
  "page": 1,
  "per_page": 20,
  "notifications": [
    {
      "id": 7001,
      "type": "pickup_request",
      "title": "Pickup requested",
      "message": "Client Jane Doe scheduled pickup for 15:00.",
      "status": "unread",
      "created_at": "2024-05-12T13:50:00Z"
    }
  ]
}
```

---

#### `POST /notifications/sms`
Send SMS via Twilio (admin only or system triggered).

- **Body**
  | Field     | Type   | Required |
  |-----------|--------|----------|
  | `user_id` | int    | ✓        |
  | `message` | string | ✓ (max 320 chars) |

- **Response**
```json
{
  "message_id": "SM5d8d9c42c",
  "status": "queued"
}
```

---

### 2.9 Ratings & Reviews

#### `POST /collectors/{id}/reviews`
Client leaves rating after completed pickup.

- **Body**
  | Field      | Type   | Required |
  |------------|--------|----------|
  | `rating`   | float  | ✓ (1–5)  |
  | `comment`  | string |          |
  | `pickup_id`| int    | ✓        |

- **Response**
```json
{
  "review_id": 3105,
  "collector_id": 1815,
  "rating": 5,
  "comment": "Quick and professional!",
  "created_at": "2024-05-12T17:00:05Z"
}
```

---

### 2.10 Analytics (Admin)

#### `GET /admin/analytics/overview`
Aggregated metrics for dashboard.

- **Authorization**: Admin role

- **Response**
```json
{
  "time_range": "2024-05-01/2024-05-12",
  "totals": {
    "pickups_completed": 382,
    "waste_diverted_kg": 1420.5,
    "co2_offset_kg": 358.2
  },
  "collector_performance": [
    {"collector_id": 1815, "pickups": 42, "avg_rating": 4.9},
    {"collector_id": 1940, "pickups": 35, "avg_rating": 4.7}
  ],
  "heatmap_points": [
    {"lat": -1.944, "lng": 30.061, "count": 22},
    {"lat": -1.952, "lng": 30.048, "count": 18}
  ]
}
```

---

#### `GET /admin/collectors/rewards`
Reward calculation for verified pickups.

- **Query Params**: `month` (YYYY-MM)
- **Response**
```json
{
  "month": "2024-04",
  "collectors": [
    {
      "collector_id": 1815,
      "verified_pickups": 65,
      "bonus_amount": 35.00,
      "tier": "Gold"
    },
    {
      "collector_id": 1940,
      "verified_pickups": 48,
      "bonus_amount": 20.00,
      "tier": "Silver"
    }
  ]
}
```

---

## 3. Authentication & Authorization
1. **Login** via `/auth/login` with valid credentials and role; receive JWT (1-hour expiry) + refresh token (if implemented).
2. **Send JWT** in `Authorization: Bearer` header for each request.
3. **Role-Based Access Control**:
   - `collector`: manage profile, availability, pickups assigned.
   - `client`: search collectors, create requests, pay, review.
   - `admin`: system-wide analytics, reward management, SMS broadcast, user moderation.
4. **Session & CSRF**: Browser clients must fetch CSRF token via `/auth/csrf-token` and include it in `X-CSRF-Token` header or form field for state-changing requests.
5. **Password Security**: Passwords hashed server-side using `password_hash()` (bcrypt/argon2).

---

## 4. Rate Limiting
- **Default**: 120 requests per minute per IP/user combination.
- **Burst**: short spikes up to 200 RPM tolerated for <10 seconds.
- **Exceeded Response**: `429 Too Many Requests`
```json
{
  "error": "RateLimitExceeded",
  "retry_after": 30
}
```

---

## 5. Error Handling
| HTTP Status | Error Code             | Description                                   |
|-------------|------------------------|-----------------------------------------------|
| 400         | `ValidationError`      | Input data invalid                             |
| 401         | `InvalidCredentials`   | Login failed or token missing                  |
| 401         | `TokenExpired`         | JWT expired                                    |
| 403         | `Forbidden`            | Insufficient role/permissions                  |
| 404         | `NotFound`             | Resource not found                             |
| 409         | `Conflict`             | Resource state conflict (double booking, etc.) |
| 422         | `UnprocessableEntity`  | Business rules violation                       |
| 429         | `RateLimitExceeded`    | Too many requests                              |
| 500         | `ServerError`          | Unexpected failure (logged server-side)        |

**Error payload format**
```json
{
  "error": "ValidationError",
  "message": "One or more fields are invalid.",
  "details": {
    "email": "Email already registered"
  },
  "timestamp": "2024-05-12T09:50:44Z",
  "request_id": "req_7f9d9d2"
}
```

---

## 6. Examples (Workflows)

### 6.1 Client Schedules Pickup
1. **Login** → POST `/auth/login`
2. **Search collectors** → GET `/collectors/search?lat=-1.94&lng=30.06&radius_km=5`
3. **Create service request** → POST `/service-requests` (collector_id from step 2)
4. **Monitor status** → GET `/service-requests/{id}`
5. **Confirm pickup completion** → POST `/pickups/{id}/verify` (client role)
6. **Pay collector** → POST `/payments`
7. **Leave review** → POST `/collectors/{id}/reviews`

### 6.2 Collector Handles Request
1. **Login** → POST `/auth/login` (role collector)
2. **Update availability** → POST `/collectors/me/availability`
3. **Receive notifications** → GET `/notifications`
4. **Confirm pickup** → PATCH `/service-requests/{id}/status` status `confirmed`
5. **Upload verification photo** → POST `/collectors/me/verification-photo`
6. **Verify pickup** → POST `/pickups/{id}/verify` (collector role)

### 6.3 Admin Rewards Calculation
1. **Admin login** → POST `/auth/login` role admin
2. **View analytics** → GET `/admin/analytics/overview`
3. **Calculate monthly rewards** → GET `/admin/collectors/rewards?month=2024-04`
4. **Send SMS notification** → POST `/notifications/sms` for top performers

---

For installation, configuration, and schema setup, refer to the project’s installation guide and `database/schema.sql` script included in the repository. All API responses follow the documented formats, and server-side logging captures request IDs for traceability.
```
```
