# GARA Architecture & Technical Documentation (Hardened v2.0)

## 1. Executive System Overview
GARA (Garuda Akademi) is an enterprise hybrid education platform consisting of a Flutter mobile client and a Laravel 12 API backend. The system provides secure digital exam environments, role-based LMS access, authenticated file transfers, and offline-first student productivity tooling.

## 2. Directory Structure
```text
eduaps/
├── garaz/gara/                            # Laravel 12 Backend & Sanctum API
│   ├── app/Http/Controllers/Api/          # API Controllers
│   │   ├── ExamStatusController.php       # Exam status & pintu asesmen check
│   │   ├── MobileAuthController.php       # Native login & sanctum tokens
│   │   ├── MobileDataController.php       # Student sync data
│   │   ├── MobileHandoffController.php    # One-Time Ticket Issuer & Consumer
│   │   └── NotificationApiController.php  # Notification polling API
│   ├── app/Http/Controllers/Guru/         # Teacher Controllers (Google OAuth)
│   │   └── GoogleAuthController.php       # Deep-link OAuth bridge
│   ├── routes/api.php                     # Sanctum Authenticated API Routes
│   ├── routes/web.php                     # Web Routes & Ticket Consumer Endpoint
│   └── tests/Feature/                     # PHPUnit Feature Test Suites
└── lib/                                   # Flutter Native Client (^3.5.0)
    ├── main.dart                          # Application Bootstrap & Token Migration
    ├── models/                            # Domain Data Models (auth, note, focus, mapel)
    ├── screens/                           # Screen UI Pages (dashboard, login, editor)
    ├── services/                          # Application & Persistence Services
    │   ├── auth_service.dart              # Authentication Facade
    │   ├── secure_token_storage.dart      # FlutterSecureStorage Hardware Keystore
    │   ├── local_database_service.dart    # SQLite Offline Database Engine
    │   ├── notes_service.dart             # Normalized Note SQLite Operations
    │   ├── focus_service.dart             # Pomodoro Focus SQLite Operations
    │   ├── in_app_oauth_service.dart      # Custom Tabs / SafariVC OAuth
    │   ├── authenticated_download_service.dart # Cookie-authenticated downloader
    │   └── notification_polling_manager.dart # Adaptive Background Polling
    ├── utils/                             # Constants, Error Taxonomy & Configurations
    │   ├── app_config.dart                # Base URL & Domain Whitelist Engine
    │   ├── gara_error_mapper.dart         # ERR-xxx Production Error Masker
    │   └── app_constants.dart             # UI Colors & Preference Keys
    └── widgets/                           # Reusable UI & Dashboard Components
        ├── hybrid_wrapper.dart            # Secure InAppWebView Sandbox Container
        ├── gara_error_view.dart           # Production Error Boundary Widget
        └── dashboard/                     # Modular Dashboard (<200 lines per file)
```

## 3. Core Architectural Subsystems

### 3.1 One-Time Short-Lived Handoff Ticket (`EP-01`)
- Client requests ticket via `POST /api/mobile/handoff-ticket` using Sanctum Bearer token.
- Backend validates role-based route prefixes and generates a 64-hex-character ticket stored in Laravel Cache with a strict 15-second TTL.
- WebView loads `/auth/webview-handoff?ticket=<ticket>`, where `Cache::pull()` atomically validates and burns the ticket, generating an `HttpOnly`, `Secure` session.
- Query tokens (`token=`) are strictly rejected with HTTP 400.

### 3.2 Secure Credential Storage (`EP-01`)
- Token storage uses `FlutterSecureStorage` with hardware-backed keystore/keychain.
- Cold start runs `SecureTokenStorage.migrateLegacyTokenIfNeeded()` to purge plaintext tokens from `SharedPreferences`.

### 3.3 Exam Kiosk & Hardware Sandbox (`EP-02`)
- MethodChannel `com.lms.gara/security` enforces `FLAG_SECURE`, `startLockTask`, and system bar immersion on Android (`MainActivity.kt`).
- iOS (`AppDelegate.swift`) observes `UIScreen.capturedDidChangeNotification` and `willResignActiveNotification` to prevent recording or multitasking.
- WebView disables mixed content, file URL access, zoom, and context menus during exams. External navigation is restricted to `AppConfig.allowedDomains`.

### 3.4 Decoupled Infrastructure & Lifecycle Polling (`EP-03`)
- Base URLs are injected via `--dart-define=GARA_BASE_URL=...`.
- Legacy `InfinityBypassEngine` is disabled by default (`ENABLE_INFINITY_BYPASS=false`).
- `DashboardPage` uses `NotificationPollingManager` to pause polling when inactive and resume with exponential backoff (180s - 600s).
- `SholatWidget` enforces a strict 4-second timeout with offline cache fallbacks.

### 3.5 Offline-First SQLite Storage Engine (`EP-04`)
- Replaces monolithic `SharedPreferences` JSON blobs with `gara_offline.db`.
- Normalized schema:
  - `notes`: `(id, title, category, created_at, updated_at)`
  - `note_blocks`: `(id, note_id, sort_order, type, content, checked)` with foreign key cascade.
  - `focus_meta`: Singleton settings, streaks, and plant points.
  - `focus_history`: Daily completed session logs.
- Automatic idempotent migration (`sqlite_migration_v1_done`).

### 3.6 Google Workspace In-App OAuth (`EP-07`)
- Google OAuth is handled outside restricted WebViews using Chrome Custom Tabs and Safari View Controller (`flutter_custom_tabs`).
- Completes with deep-link redirect `garamobile://oauth/google-done` without requiring GCP console modifications.

### 3.7 Authenticated In-App File Download Manager (`EP-08`)
- Downloads triggered in WebView extract active session cookies and stream files directly via `http.Client`.
- Follows up to 5 redirect hops, parses `Content-Disposition` for sanitized filenames, saves to standard Downloads, and opens via `open_filex`.

### 3.8 Responsive Layout & Question Editor (`EP-09`)
- `HeroCard` is decomposed into modular sub-components (`HeroSubjectBadge`, `HeroPlayButton`, `HeroProgressBar`) $\le 200$ lines.
- Teacher manual input styles incorporate mobile responsive overrides (`@media (max-width: 768px)`), releasing sticky positioning in `.soal-nav-panel` to prevent overlapping `.soal-editor`.

### 3.9 Error Taxonomy & Production Masking (`EP-10`)
- Sanitizes unhandled exceptions into deterministic codes (`ERR-NET-001`, `ERR-AUTH-001`, `ERR-SRV-500`).
- Replaces raw stack traces and HTML error bodies with user-friendly `GaraErrorView` with one-tap error code copying.

## 4. Key File Governance ($\le 200$ Lines Policy)
- [SecureTokenStorage](lib/services/secure_token_storage.dart)
- [HandoffTicketService](lib/services/handoff_ticket_service.dart)
- [InAppOAuthService](lib/services/in_app_oauth_service.dart)
- [AuthenticatedDownloadService](lib/services/authenticated_download_service.dart)
- [GaraErrorMapper](lib/utils/gara_error_mapper.dart)
- [GaraErrorView](lib/widgets/gara_error_view.dart)
- [HeroCard](lib/widgets/dashboard/hero_card.dart)
- [HeroPlayButton](lib/widgets/dashboard/hero_play_button.dart)
- [HeroProgressBar](lib/widgets/dashboard/hero_progress_bar.dart)
- [HeroSubjectBadge](lib/widgets/dashboard/hero_subject_badge.dart)
- [MobileHandoffController](garaz/gara/app/Http/Controllers/Api/MobileHandoffController.php)
