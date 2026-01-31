```markdown
# Waste2Worth Security Implementation Guide

## 1. Security Overview

### Security Requirements Summary
- **Regulated Data:** PII (names, locations), service & payment metadata.
- **Protect Assets:** User credentials, service requests, payment records, analytics data, profile photos, SMS tokens.
- **Operational Integrity:** Accurate pickups, trusted notifications, reward calculations, and municipal analytics.

### Threat Model Overview
| Asset | Threat Actors | Risks |
| --- | --- | --- |
| User accounts (collectors, clients, admins) | Credential stuffing, brute force attackers | Account takeover, fraud |
| Service requests & pickups | Malicious users, tampering clients | False pickups, reward fraud, data corruption |
| Payment/transaction info | Malicious internal/external actors | Payment tampering, financial loss |
| Admin dashboard & analytics | Insider abuse, compromised accounts | Data leakage, configuration tampering |
| SMS/notifications | API key leakage, spoofing | Phishing, service interruption |

### Compliance Considerations
- **GDPR:** Collect minimal PII, allow data deletion/export, privacy notice, consent for location data.
- **PCI-DSS (lite):** If storing only transaction metadata (no card data), ensure PCI-aware payment provider, secure transmission, logging.
- **Local privacy laws:** Data retention policies, consent for SMS communications.

---

## 2. Authentication & Authorization

### Recommended Authentication
- **Session-based authentication** (PHP native sessions) with secure cookies (`HttpOnly`, `SameSite=Lax`/`Strict`, `Secure`).

### Password Security
- Use `password_hash($password, PASSWORD_ARGON2ID)` (fallback to `PASSWORD_DEFAULT` if Argon not available).
- Enforce strong passwords: min 12 chars, mix of character sets.
- Rate-limit login attempts (e.g., 5 attempts per 15 minutes per IP + username).
- Password reset flow:
  1. Email a time-limited (15 min) signed token.
  2. Store hashed token in DB (`password_reset_tokens` with expiry).
  3. Force re-login after completion.

```php
$hash = password_hash($password, PASSWORD_ARGON2ID);
if (!password_verify($input, $hash)) { /* reject */ }
```

### Session Management
- Regenerate session ID on login (`session_regenerate_id(true)`).
- Store minimal user data in session (user_id, role, CSRF token).
- Server-side session store; configure `session.cookie_secure=1`, `session.cookie_httponly=1`.
- Implement idle timeout (e.g., 20 minutes) and absolute timeout (e.g., 12 hours).
- Log session creation/destruction.

### RBAC Implementation
- Table: `roles` (collector, client, admin), `user_roles`.
- Middleware/service layer to enforce role permissions per controller action.
- Example check:

```php
function authorize(array $allowedRoles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowedRoles, true)) {
        http_response_code(403);
        exit('Forbidden');
    }
}
```

### MFA Considerations
- Optional TOTP (e.g., Google Authenticator) for admins/municipal accounts.
- Offer SMS-based OTP for collectors with low-tech devices (limit attempts, expire after 2 minutes).

---

## 3. Input Validation & Data Sanitization

### Validation Rules
| Input | Validation |
| --- | --- |
| Names | UTF-8 letters, spaces, hyphen; length 2-80 |
| Emails | RFC-compliant, unique |
| Passwords | Complexity rules, confirm match |
| Coordinates | Decimal lat (-90..90), lng (-180..180) |
| Service area radius | Numeric 0.1–50 km |
| Pickup notes | Max 1000 chars, sanitize HTML |
| File uploads | Image/JPEG/PNG, max 2MB, scan/verify mime |

### SQL Injection Prevention
- Use PDO with prepared statements everywhere.

```php
$stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
```

- No string concatenation with user input.
- Least-privileged DB user.

### XSS Prevention
- Escape output contextually (`htmlspecialchars($value, ENT_QUOTES, 'UTF-8')`).
- Strip/allowlist fields that accept text (e.g., reviews) via HTML purifier or custom allowlist (<b>, <i> etc.).
- Use CSRF tokens for AJAX POSTs.
- Use Content Security Policy (CSP) to restrict scripts.

### CSRF Protection
- Generate per-session token stored in session.
- Include hidden input in forms, header in AJAX requests.
- Verify token for all state-changing requests.

```php
if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    throw new Exception('CSRF validation failed');
}
```

### File Upload Security
- Store outside web root, random filenames, verify MIME (`finfo_file`), image re-encode (GD/Imagick).
- Set max file size at PHP and application level.
- Scan metadata to remove EXIF GPS if not needed.
- Serve via PHP script with authorization.

### Validation Helper Example

```php
function sanitizeText(string $input, int $maxLen = 255): string {
    $trimmed = trim($input);
    if (mb_strlen($trimmed) > $maxLen) {
        throw new InvalidArgumentException('Input too long');
    }
    return htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
}
```

---

## 4. API Security

### API Authentication
- Internal AJAX: rely on session + CSRF token.
- External APIs (e.g., mobile app) use OAuth2 or signed tokens per client; keep minimal.
- SMS/Notification endpoints secured with signed secret headers.

### Rate Limiting
- Implement per-IP rate limiting (Redis or DB). Example: `/auth/login` max 10/min.
- For APIs, use middleware storing counts with TTL.

### Request Validation & Sanitization
- JSON inputs validated against schema.
- Reject unexpected fields; enforce content-type headers.

### CORS Configuration
- Only allow trusted origins (client app domain).
- Include `Access-Control-Allow-Credentials` if session cookies needed.
- Reject wildcard `*` if credentials used.

### Security Headers
- `Content-Security-Policy: default-src 'self'; img-src 'self' data: https://maps.gstatic.com; script-src 'self' https://cdn.leafletjs.com`
- `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload`
- `X-Frame-Options: DENY`
- `X-Content-Type-Options: nosniff`
- `Referrer-Policy: strict-origin-when-cross-origin`

### API Versioning Security
- Maintain `v1`, `v2` endpoints; deprecate insecure versions with sunset notice.
- Apply same security config to all versions; avoid leaving old endpoints unpatched.

---

## 5. Data Protection

### Encryption at Rest
- Use MySQL TDE or file-system encryption for backups.
- Encrypt sensitive columns (e.g., API keys) using libsodium (`sodium_crypto_secretbox`).
- For photos, store on encrypted volume.

### Encryption in Transit
- Enforce HTTPS/TLS 1.2+ everywhere; redirect HTTP to HTTPS.
- Use HSTS; disable weak ciphers; use Let’s Encrypt or managed certificates.

### Sensitive Data Handling
- Store hashed passwords only.
- Payment data: only store transaction references, not card numbers.
- Mask phone numbers when displaying to non-authorized roles.
- Hash API keys before storing; rotate regularly.

### Environment Variables & Secrets
- Store secrets in `.env` outside web root; load via secure config library.
- Different credentials per environment.
- Limit access permissions on config files (`600` owner only).

### Data Masking & Anonymization
- For analytics dashboards, aggregate data; avoid exposing raw PII.
- Provide anonymized dataset for municipal reporting (replace names with IDs, generalize coordinates).

---

## 6. Vulnerability Prevention (OWASP Top 10)

### Injection
- Prepared statements, input validation, ORM-like abstraction; no dynamic SQL.
- Validate map coordinates before use in queries.

### Broken Authentication
- Strong password policy, rate limiting, MFA for admins, session hardening.

### Sensitive Data Exposure
- TLS, encryption at rest, secure storage of tokens, avoid logging PII.

### XXE Prevention
- Disable external entity loading in XML parsers (if using). Example:

```php
$dom = new DOMDocument();
$dom->loadXML($xml, LIBXML_NOENT | LIBXML_DTDLOAD); // AVOID
// Instead:
$dom->loadXML($xml, LIBXML_NONET);
```

### Broken Access Control
- Centralized authorization checks, route middleware, deny-by-default.
- Verify record ownership before actions (service request must belong to client).

### Security Misconfiguration
- Remove default admin accounts.
- Disable directory listing, display_errors off, configure PHP `open_basedir`.
- Keep PHP, Apache/Nginx, DB patched.

### XSS
- Encode output, CSP, sanitize inputs, avoid inline JS.

### Insecure Deserialization
- Avoid PHP `unserialize` on user data. Use JSON.

### Using Components with Known Vulnerabilities
- Track dependencies (Leaflet, Chart.js) via `npm` or manual version list; run `npm audit` equivalent.
- Keep composer packages updated.

### Insufficient Logging & Monitoring
- Log logins, failed logins, CRUD actions, role changes, reward payouts.
- Centralize logs, monitor anomalies.

---

## 7. Secure Coding Guidelines

### Code Review Checklist
- All inputs validated and sanitized?
- Prepared statements used?
- Authorization checks on controller actions?
- Sensitive data not logged?
- Error handling and CSRF tokens implemented?

### Anti-Patterns to Avoid
- Mixing business logic in controllers without service layer.
- Reusing SQL queries with string concatenation.
- Hardcoding secrets/API keys.
- Disabling SSL verification for APIs.

### Secure Error Handling
- User-facing errors: generic messages (“An error occurred, please try again.”).
- Log detailed stack traces to secure log files only.

### Logging Best Practices
- Log timestamp, user ID, action, IP.
- Do not log passwords, session IDs, CSRF tokens, payment info.
- Use log rotation and protection (`chmod 640`).

### Debug Mode Security
- Disable `display_errors` in production.
- Condition debug features on environment variable and IP allowlist.
- Prevent exposure of phpinfo or debug routes.

---

## 8. Security Testing

### Security Testing Checklist
- ✅ Authentication & session tests
- ✅ Input validation (fuzzing, boundary tests)
- ✅ Authorization (horizontal/vertical privilege escalation)
- ✅ CSRF validation on forms, AJAX
- ✅ File upload tests (polyglot files, oversized files)
- ✅ Map/proximity search injection tests
- ✅ Payment and reward calculation tampering

### Penetration Testing
- Quarterly external pen test covering portals and admin dashboard.
- Include SMS spoofing, MITM scenarios, reward manipulation.

### Automated Scanning Tools
- **Static:** PHPStan, Psalm with security-focused rules, SonarQube.
- **Dynamic:** OWASP ZAP, Burp Suite.
- **Dependency:** `composer audit`, `npm audit` for front-end libs.

### Dependency Scanning
- Integrate into CI pipeline; fail build if high severity vulnerability.
- Maintain SBOM (Software Bill of Materials) for compliance.

---

## 9. Deployment Security

### HTTPS Enforcement
- Redirect HTTP to HTTPS (`301`).
- Use Let’s Encrypt automation, monitor certificates.

### Server Hardening
- Update OS/packages regularly.
- Disable unused services, close ports.
- Use `ufw`/`iptables`; allow only 80/443 (and SSH with key auth).
- Separate DB server; restrict access to app subnet.

### Environment Isolation
- Separate dev/staging/prod databases & credentials.
- No real data in dev; use anonymized seeds.
- CI/CD pipeline should deploy from clean artifacts.

### Firewall & Network Security
- WAF (ModSecurity) for OWASP CRS.
- Security groups restricting outbound/inbound.
- Monitor unusual traffic.

### Backup & Recovery
- Encrypted automatic backups (daily DB, weekly full).
- Secure storage (offsite), test restores quarterly.
- Document recovery runbook.

---

## 10. Incident Response

### Security Incident Response Plan
1. **Detection:** Monitor logs, IDS alerts, anomaly detection on analytics.
2. **Containment:** Disable affected accounts, isolate servers, revoke tokens.
3. **Eradication:** Patch vulnerabilities, rotate secrets.
4. **Recovery:** Restore from clean backups, validate system integrity.
5. **Lessons Learned:** Post-incident review, update policies.

### Breach Notification
- Notify stakeholders and regulators per GDPR within 72 hours if PII affected.
- Inform users via email/SMS about compromised data, recommend password resets.

### Security Monitoring & Alerting
- Central log aggregation (ELK/Splunk).
- Alerts for:
  - Multiple failed logins
  - Sudden spike in reward payouts
  - Unusual geolocation access
  - Configuration changes
- Keep audit trails for at least 1 year.

---

**Implementation Note:** Use environment-specific config (e.g., `/config/app.php`) for secrets, integrate service layer for collectors/clients/admins, enforce MVC separation, and ensure documentation of all security controls inline in code for maintainability.
```
