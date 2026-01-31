# Waste2Worth Frontend Implementation Guide

## 1. Component Structure
Detailed view of UI components necessary for key features across collector, client, and admin interfaces.

### Global
- **App Shell**
  - Header (logo, navigation, role switcher, notifications)
  - Side Navigation (context-aware links per role)
  - Main Content Area
  - Footer (contact, policies, language toggle)
- **Auth Components**
  - Login form (role selection, password toggle, CSRF hidden token)
  - Registration forms (role-specific fields, progressive disclosure)
  - Password reset modal

### Shared UI Elements
| Component | Description | Key Features |
|-----------|-------------|--------------|
| `NotificationBell` | Displays unread notifications | Badges, dropdown list, actions |
| `MapWidget` | Leaflet/Google Maps with markers and clustering | Real-time availability, distance filter |
| `AvailabilityBadge` | Visual status for collectors | Color-coded pill (Available/Busy/Offline) |
| `ServiceCard` | Summaries of requests/pickups | Name, waste type, status, CTAs |
| `Modal` | Reusable modal with accessible focus trapping | Confirmations, forms, previews |
| `Accordion` | Display FAQs, analytics details | Keyboard accessible open/close |
| `DataTable` | Sortable, filterable tables | Pagination, search, bulk actions |
| `Toast` | Success/error notifications | Auto-dismiss, ARIA live region |

### Role-Specific Components

#### Collector Portal
- `CollectorProfileForm` (location, services, documents upload)
- `ScheduleManager` (calendar grid with availability toggle)
- `PickupLogger` (start/complete pickup, upload verification photo)
- `RewardWidget` (progress bar toward monthly bonus)
- `Inbox` (service requests list with quick actions)

#### Client Portal
- `CollectorSearchPanel` (filters for waste type, distance, availability)
- `RequestForm` (pickup request with time slots, payment selection)
- `PaymentModule` (amount breakdown, card/mobile money input, confirmation)
- `RequestTimeline` (status progression, dual confirmation updates)
- `ReviewForm` (rating stars, comment, tags for specific feedback)

#### Admin/Municipal Dashboard
- `AnalyticsOverview` (KPIs: pickups, CO2 offset, waste diverted)
- `ChartPanel` (Chart.js with time-series, doughnuts)
- `HeatMap` (map overlay of activity)
- `CollectorRewardsManager` (list/table with manual adjustments)
- `SMSCampaignManager` (compose template, send logs)
- `AuditTrail` (logs of key events)

## 2. State Management
Use modular vanilla JS with a lightweight pub/sub pattern or simple store (e.g., using the Observer pattern) to avoid heavy frameworks.

### Data Stores
- `authStore`: user session data, roles, CSRF token.
- `collectorStore`: collectors list, profiles, availability statuses.
- `requestsStore`: service requests, pickups, notifications.
- `analyticsStore`: metrics, charts data, heat map payload.
- `uiStore`: modal visibility, toasts, loading states.

```js
// simple store example
const createStore = (initialState = {}) => {
  let state = { ...initialState };
  const listeners = new Set();
  return {
    getState: () => state,
    subscribe: fn => { listeners.add(fn); return () => listeners.delete(fn); },
    setState: updates => {
      state = { ...state, ...updates };
      listeners.forEach(fn => fn(state));
    }
  };
};

export const requestsStore = createStore({ requests: [], filters: {}, loading: false });

requestsStore.subscribe(({ requests }) => renderRequestList(requests));
```

### Data Flow Patterns
1. **Fetch → Store → Render**: AJAX fetch data (with `fetch` API), update store, trigger UI update via `subscribe`.
2. **Forms**: Validate client-side, show error messages, disable submit until valid; on success, optimistic update store.
3. **Notifications**: WebSocket or long-polling to update `requestsStore` and `notificationsStore`.
4. **Map data**: Maintain separate slice for geo-coordinates; update markers only for changed items to avoid full re-render.
5. **CSRF & sessions**: Store token in meta tag; include in `fetch` headers.

```js
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
fetch('/api/requests', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': csrfToken
  },
  body: JSON.stringify(payload)
});
```

### Caching & Local Storage
- Cache filters/preferences (e.g., selected waste types) using `localStorage`.
- Sensitive data (tokens) should remain in memory; do not store in localStorage.

### Real-time Status
- Leverage `setInterval` or WebSocket for availability updates; store last-updated timestamps to prevent stale UI.

## 3. UI/UX Guidelines

### Visual Design
- **Color Palette**: Green (sustainability) + neutrals; accent orange for CTAs.
- **Typography**: Sans-serif (e.g., “Inter”); hierarchy via weights and sizes.
- **Spacing**: 8px baseline; maintain consistent padding/margins.
- **Theme**: Light default; optional dark mode toggle with CSS variables.
```css
:root {
  --color-primary: #1E8449;
  --color-accent: #F5A623;
  --color-bg: #F7FBF7;
  --color-text: #1F2A37;
  --spacing-1: 0.5rem;
  --spacing-2: 1rem;
}
```

### Interaction Patterns
- **Maps**: Provide zoom controls, geolocation button, cluster markers. Tooltip on hover, detail panel on click.
- **Forms**: Step-by-step for long forms; inline validation messages; accessible labels. Use input masks for phone numbers.
- **Tables/Lists**: Sticky headers, hover states, quick filters.
- **Modals**: Focus trapped, close on ESC, accessible labels.

### Responsive Behavior
- Mobile-first approach.
- Breakpoints: 576px, 768px, 992px, 1200px (Bootstrap reference).
- Stack cards, convert tables to card layout on mobile.
- Side nav collapses to hamburger drawer on small screens.

### Accessibility
- Ensure color contrast >= 4.5:1.
- Semantic HTML elements.
- Keyboard navigability (tab order, ARIA attributes).
- ARIA live regions for dynamic updates (toasts, notifications).
- Alt text for images (profile photos, verification images).

### Feedback & Status
- Use skeleton loaders for data-heavy components (maps, dashboards).
- Show status badges (Pending, In Progress, Completed, Verified).
- Provide progress indicators for long operations (file uploads, map loading).

## 4. Page Layouts

### 4.1 Authentication Pages
**Login / Registration**
- Two-column layout (visual + form) on desktop, stacked on mobile.
- Role tabs for Collector / Client / Admin.
- Password toggle, remember me, forgot password link.
- CSRF hidden input.

```html
<form id="loginForm" class="auth-form">
  <input type="hidden" name="csrf_token" value="{{ csrf }}">
  <div class="tabs">
    <button data-role="collector" class="active">Collector</button>
    <button data-role="client">Client</button>
    <button data-role="admin">Admin</button>
  </div>
  <label>Email<input type="email" name="email" required></label>
  <label>Password<div class="password-field">
    <input type="password" name="password" required>
    <button type="button" class="toggle-password">Show</button>
  </div></label>
  <button type="submit" class="btn-primary">Sign In</button>
</form>
```

### 4.2 Collector Dashboard
**Layout**
- Side nav (Profile, Availability, Requests, Rewards, History).
- Main widgets:
  1. **Availability Toggle** (switch, next available slot).
  2. **Pending Requests** (list with Accept/Decline buttons, map pin preview).
  3. **Today’s Route Map** (Leaflet map with scheduled pickups).
  4. **Reward Progress** (progress bar, stats).
  5. **Activity Feed** (recent pickups, ratings).

```html
<section class="grid">
  <div class="card availability-card">
    <label class="switch">
      <input type="checkbox" id="availabilityToggle">
      <span>Available for pickups</span>
    </label>
    <p>Next slot: Today 2–4 PM</p>
  </div>
  <div id="pendingRequests" class="card list-card"></div>
  <div id="routeMap" class="card map-card"></div>
  <div id="rewardWidget" class="card stats-card"></div>
</section>
```

### 4.3 Client Portal
**Home / Find Collectors**
- Search bar with location autocompletion.
- Filters (waste type, distance slider, rating).
- Map (right) + list (left) layout on desktop; map toggle on mobile.
- Collector cards with availability badge, rating, “Request Pickup” CTA.

**Request Flow**
1. Select collector (modal or dedicated page).
2. Fill request form (waste details, photos, preferred time).
3. Confirm payment method.
4. Show timeline (Requested → Accepted → En Route → Completed).
5. Post-service review form.

### 4.4 Admin Dashboard
**Overview page**
- KPI cards (Total pickups, CO2 offset, Waste diverted, Active collectors).
- Charts row (line chart for pickups over time, bar chart for waste types).
- Heat map section.
- Reward approvals table.
- SMS campaign panel (compose, send history).

**Analytics Detail**
- Filters (date range, region, waste type).
- Chart.js components with drill-down.
- Export buttons (CSV, PDF via backend).

### 4.5 Notifications & Messaging
- Notification drawer accessible from header.
- Each notification card with icon, description, time, action buttons.
- Real-time updates (WebSocket) push new cards; highlight until seen.

### 4.6 Verification & File Upload
- File upload component for verification photos:
  - Drag & drop area with preview thumbnail.
  - Progress bar per file.
  - Validation (file type, size).
```html
<div class="upload-area" data-state="idle">
  <input type="file" id="verificationPhoto" accept="image/*" hidden>
  <label for="verificationPhoto">
    <span class="icon-upload"></span>
    <p>Drag & drop or click to upload verification photo</p>
  </label>
  <div class="preview" aria-live="polite"></div>
</div>
```

### 4.7 Rating & Review Modal
- Star rating (1–5) with keyboard accessibility.
- Tags for quick feedback (Cleanliness, Timely, Communication).
- Textarea with character count.
- Submit button disabled until rating selected.

### 4.8 Payment Confirmation
- Summary card with line items (service fee, incentives, taxes).
- Payment method selection (card, mobile money, cash).
- Confirmation modal with OTP (if required).
- Display transaction ID on success.

---

**Implementation Tips**
- Modularize CSS using BEM or utility classes; consider SCSS for variables/mixins if build pipeline allows.
- Use CSS Grid for dashboards, Flexbox for cards and forms.
- Vanilla JS modules (`type="module"`) to organize code per component.
- Lazy-load heavy assets (maps, charts) after primary content.
- Document each component with inline JS/CSS comments for maintainability.
