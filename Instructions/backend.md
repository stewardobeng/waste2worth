```markdown
# Waste2Worth Backend Implementation Guide  
**Version: 1.0**  
**Date: 2024-02-14**

---

## 1. API Design

| Endpoint | Method | Auth Role(s) | Description | Request Body | Response |
|----------|--------|--------------|-------------|--------------|----------|
| `/api/auth/login` | POST | Public | Authenticate user | `{ "email": "", "password": "" }` | `200 { "token": "", "role": "" }` |
| `/api/auth/logout` | POST | All | Destroy session + CSRF | `-` | `204` |
| `/api/collectors` | GET | Client/Admin | List/Filter collectors (AJAX) | Query params: `lat`, `lng`, `radius`, `waste_type`, `available` | `200 [ { collector... } ]` |
| `/api/collector/profile` | GET/PUT | Collector | View/update own profile | PUT: fields below | `200 { profile }` |
| `/api/service-requests` | POST | Client | Create pickup request | `{ collector_id, waste_types[], schedule_time, notes }` | `201 { request_id }` |
| `/api/service-requests/{id}` | PATCH | Collector/Client | Status changes & confirmations | `{ status, client_confirmed?, collector_confirmed? }` | `200` |
| `/api/payments` | POST | Client | Record payment | `{ request_id, amount, method, tx_ref }` | `201` |
| `/api/analytics/overview` | GET | Admin | Summary metrics | Query: `date_from`, `date_to` | `200 { charts data... }` |
| `/api/notifications` | GET | Collector/Client | Fetch unseen notifications | `-` | `200 [ ... ]` |
| `/api/reviews` | POST | Client | Submit collector review | `{ collector_id, rating, comments }` | `201` |

> **Notes:**  
> - All endpoints return JSON.  
> - CSRF token required for state-changing requests via custom header `X-CSRF-Token`.  
> - Use HTTP status semantics (201 created, 422 validation errors, etc.).

---

## 2. Data Models (MySQL)

### `users`
| Field | Type | Notes |
|-------|------|-------|
| `id` PK | INT | Auto increment |
| `uuid` | CHAR(36) | Public identifier |
| `role` | ENUM('collector','client','admin') | |
| `email` | VARCHAR(150) | Unique |
| `password_hash` | VARCHAR(255) | `password_hash()` |
| `phone` | VARCHAR(20) | For SMS |
| `status` | ENUM('active','suspended') | |
| `created_at` | TIMESTAMP | default CURRENT_TIMESTAMP |

### `collector_profiles`
| Field | Type | Notes |
| `user_id` FK | INT | references users.id |
| `display_name` | VARCHAR(120) |
| `lat`, `lng` | DECIMAL(10,7) |
| `service_radius_km` | TINYINT |
| `waste_types` | JSON | e.g. ["plastic", "organic"] |
| `availability_status` | ENUM('available','busy','offline') |
| `bio` | TEXT |
| `profile_image` | VARCHAR(255) | file path |
| `verification_docs` | VARCHAR(255) |

### `service_requests`
| Field | Type |
| `id` PK | INT |
| `uuid` | CHAR(36) |
| `client_id` FK | INT |
| `collector_id` FK | INT |
| `scheduled_time` | DATETIME |
| `status` | ENUM('pending','accepted','in_progress','completed','cancelled') |
| `client_confirmed` | TINYINT(1) |
| `collector_confirmed` | TINYINT(1) |
| `notes` | TEXT |
| `created_at` | TIMESTAMP |

### `completed_pickups`
| Field | Type |
| `id` PK | INT |
| `service_request_id` FK | INT |
| `verified_at` | TIMESTAMP |
| `waste_weight_kg` | DECIMAL(5,2) |
| `photos_path` | VARCHAR(255) |

### `payments`
| Field | Type |
| `id` PK | INT |
| `service_request_id` FK | INT |
| `amount` | DECIMAL(10,2) |
| `method` | ENUM('cash','mobile','card') |
| `transaction_ref` | VARCHAR(100) |
| `status` | ENUM('pending','confirmed','failed') |
| `created_at` | TIMESTAMP |

### `notifications`
| Field | Type |
| `id` PK | INT |
| `user_id` FK | INT |
| `type` | ENUM('request','status','reward') |
| `payload` | JSON |
| `is_read` | TINYINT |
| `created_at` | TIMESTAMP |

### `reviews`
| Field | Type |
| `id` | INT |
| `collector_id` FK | INT |
| `client_id` FK | INT |
| `rating` | TINYINT (1-5) |
| `comments` | TEXT |
| `created_at` | TIMESTAMP |

### `analytics_metrics`
| Field | Type |
| `id` | INT |
| `metric_date` | DATE |
| `collector_id` | INT nullable |
| `waste_weight_kg` | DECIMAL |
| `co2_offset_kg` | DECIMAL |
| `requests_completed` | INT |
| `heatmap_cell` | VARCHAR(20) |

Indexes:  
- `users.email`, `collector_profiles(lat,lng)` (spatial index).  
- `service_requests(collector_id,status)`, `payments(service_request_id)`.

---

## 3. Business Logic

1. **Registration & Onboarding**
   - Clients/Collectors register via role selection.
   - Collector profile requires geolocation, service radius, waste types; pending verification by admin.
   - SMS confirmation triggered via Twilio for phone verification.

2. **Authentication & Sessions**
   - Login -> validate credentials with `password_verify()`.
   - Store session data (`user_id`, `role`, `csrf_token`).
   - Enforce RBAC middleware per controller action.

3. **Collector Discovery**
   - AJAX search: compute Haversine distance within radius.
   - Return availability status, waste types, rating aggregates.
   - Map markers via Leaflet/Google Maps.

4. **Service Request Lifecycle**
   - Client posts request -> statuses update via PATCH.
   - Notifications + optional SMS to collector.
   - Dual confirmation: both collector & client set `*_confirmed=1` before marking as completed.

5. **Pickup Verification & Rewards**
   - Collector uploads verification photo (secure upload path, MIME check).
   - Once verified, create `completed_pickups` entry, update analytics.
   - Reward engine (cron monthly): sum verified pickups, apply bonus tiers, insert notification.

6. **Payments Module**
   - After service completion, client logs payment (manual or integrate with gateway placeholder).
   - Store transaction ref, update service_request status to `completed` if confirmations done.

7. **Analytics Dashboard**
   - Aggregate metrics (SQL GROUP BY date, collector, area).
   - Provide datasets for Chart.js (line charts, bar, heatmap).
   - Environmental impact: `co2_offset = waste_weight_kg * factor`.

8. **Notifications**
   - Stored in DB + optional email/SMS.
   - Polling endpoint or websockets (future). Response includes unread count.

---

## 4. Security

- **Authentication:** Sessions stored server-side (`$_SESSION`). Password hashing via `password_hash(PASSWORD_DEFAULT)`.
- **Authorization:** Middleware checks `$_SESSION['role']` before controller executes.
- **CSRF:** Generate random token per session, store in session; include hidden input or header `X-CSRF-Token`. Validate on POST/PUT/PATCH/DELETE.
- **Input Validation:** Server-side filter_var, custom validators; client-side forms; sanitize outputs via `htmlspecialchars`.
- **SQL Injection Prevention:** Prepared statements (PDO).
- **File Upload:** Check MIME, size, rename to UUID, store outside web root with symlink.
- **Rate Limiting:** Basic throttle on login attempts (count in cache table).
- **Transport Security:** Deploy over HTTPS; secure cookies (`HttpOnly`, `SameSite=Lax`).

---

## 5. Performance

- **DB Optimization:** Proper indexing, limit SELECT fields, pagination.
- **Caching:** Cache collector search results per geohash in Redis (optional).
- **Async Jobs:** Use cron/queue for SMS sending, reward batch processing.
- **AJAX Lazy Loading:** Map markers load per viewport.
- **Environment Config:** `.env` per environment; config loader caching.
- **Asset Optimization:** Minify CSS/JS, leverage HTTP/2.

---

## 6. Code Examples

### 6.1 PDO Connection & Repository Base
```php
// app/Core/Database.php
class Database {
    private PDO $pdo;

    public function __construct(array $config) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['database']);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
    }

    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
```

### 6.2 Login Controller Action
```php
// app/Controllers/AuthController.php
public function login(Request $request, Response $response): Response {
    $this->csrf->validate($request);

    $email = filter_var($request->input('email'), FILTER_VALIDATE_EMAIL);
    $password = $request->input('password');

    if (!$email || !$password) {
        return $response->json(['error' => 'Invalid credentials'], 422);
    }

    $user = $this->userRepo->findByEmail($email);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $this->rateLimiter->hit($request->ip());
        return $response->json(['error' => 'Invalid credentials'], 401);
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    return $response->json([
        'message' => 'Logged in',
        'role' => $user['role'],
        'csrf_token' => $_SESSION['csrf_token']
    ]);
}
```

### 6.3 Collector Search (Haversine)
```php
// app/Repositories/CollectorRepository.php
public function searchNearby(float $lat, float $lng, int $radiusKm, array $filters = []): array {
    $sql = "
        SELECT cp.*, u.uuid, 
               (6371 * acos(
                cos(radians(:lat)) * cos(radians(cp.lat)) *
                cos(radians(cp.lng) - radians(:lng)) +
                sin(radians(:lat)) * sin(radians(cp.lat))
               )) AS distance
        FROM collector_profiles cp
        JOIN users u ON u.id = cp.user_id
        WHERE u.status = 'active'
          AND cp.availability_status != 'offline'
          HAVING distance <= :radius
        ORDER BY distance ASC
        LIMIT 100;
    ";

    $params = ['lat' => $lat, 'lng' => $lng, 'radius' => $radiusKm];
    return $this->db->query($sql, $params)->fetchAll();
}
```

### 6.4 Service Request Creation
```php
// app/Services/ServiceRequestService.php
public function createRequest(int $clientId, array $data): int {
    $this->validator->validate($data, [
        'collector_id' => 'required|integer',
        'schedule_time' => 'required|datetime',
        'waste_types' => 'required|array'
    ]);

    $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();

    $sql = "INSERT INTO service_requests 
            (uuid, client_id, collector_id, scheduled_time, status, notes, created_at)
            VALUES (:uuid, :client_id, :collector_id, :scheduled_time, 'pending', :notes, NOW())";
    $params = [
        'uuid' => $uuid,
        'client_id' => $clientId,
        'collector_id' => $data['collector_id'],
        'scheduled_time' => $data['schedule_time'],
        'notes' => $data['notes'] ?? null,
    ];
    $this->db->query($sql, $params);

    $requestId = (int)$this->db->lastInsertId();
    $this->notificationService->notifyCollector($data['collector_id'], 'request', ['request_id' => $requestId]);

    return $requestId;
}
```

### 6.5 Dual Confirmation Update
```php
public function confirmPickup(int $requestId, int $userId, string $role): void {
    $column = $role === 'collector' ? 'collector_confirmed' : 'client_confirmed';

    $sql = "UPDATE service_requests SET {$column} = 1 WHERE id = :id";
    $this->db->query($sql, ['id' => $requestId]);

    $request = $this->findById($requestId);
    if ($request['collector_confirmed'] && $request['client_confirmed']) {
        $this->db->query("UPDATE service_requests SET status = 'completed' WHERE id = :id", ['id' => $requestId]);
        $this->createPickupRecord($requestId);
    }
}
```

### 6.6 Reward Calculation Cron
```php
// scripts/reward_bonus.php
$start = new DateTime('first day of last month');
$end = new DateTime('last day of last month 23:59:59');

$sql = "SELECT collector_id, COUNT(*) as pickups 
        FROM completed_pickups cp
        JOIN service_requests sr ON sr.id = cp.service_request_id
        WHERE cp.verified_at BETWEEN :start AND :end
        GROUP BY collector_id";

$collectors = $db->query($sql, ['start' => $start->format('Y-m-d H:i:s'), 'end' => $end->format('Y-m-d H:i:s')])->fetchAll();

foreach ($collectors as $c) {
    $bonus = $c['pickups'] >= 30 ? 50 : ($c['pickups'] >= 15 ? 25 : 0);
    if ($bonus > 0) {
        $db->query("INSERT INTO rewards (collector_id, amount, month) VALUES (:id, :amount, :month)", [
            'id' => $c['collector_id'],
            'amount' => $bonus,
            'month' => $start->format('Y-m')
        ]);
        $notificationService->notifyCollector($c['collector_id'], 'reward', [
            'bonus' => $bonus,
            'period' => $start->format('F Y')
        ]);
    }
}
```

### 6.7 CSRF Middleware
```php
// app/Middleware/CsrfMiddleware.php
class CsrfMiddleware {
    public function validate(Request $request): void {
        if ($request->method() === 'GET') {
            return;
        }
        $sessionToken = $_SESSION['csrf_token'] ?? null;
        $headerToken = $request->header('X-CSRF-Token');
        if (!$sessionToken || !$headerToken || !hash_equals($sessionToken, $headerToken)) {
            throw new HttpException(419, 'CSRF token mismatch');
        }
    }
}
```

---

## Installation Notes (Summary)

1. Clone repo; copy `.env.example` to `.env`.
2. Run `composer install`.
3. Create database; execute `/database/schema.sql`.
4. Configure virtual host or PHP built-in server.
5. Set writable permissions for `/storage`, `/public/uploads`.
6. Configure cron for reward script and analytics aggregator.

> **Remember:** Keep documentation inline, follow PSR-4 autoloading, and use feature-based directories (Controllers, Services, Repositories, Models).
```
