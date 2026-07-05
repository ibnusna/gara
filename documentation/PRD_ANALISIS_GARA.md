# 🎯 PRD: Front-End & Interactive System Enhancements
## 🏫 GARA (Garuda Akademi) Platform · Version 2.1 · May 2026

---

> **Product Requirement Document (PRD)** compiled by the **AI Front-End Engineer & Interactive Web Architect**. This document outlines the interactive technology roadmap to deliver a high-performance, secure, and modern client-side user experience for GARA. It incorporates precise route and filename mappings for all revised components.

---

## 🗂️ Table of Contents
1. [Executive Summary](#1-executive-summary)
2. [Target Audience & Personas](#2-target-audience--personas)
3. [Scope of Features & Technical Mapping](#3-scope-of-features--technical-mapping)
4. [Functional Requirements & User Flows](#4-functional-requirements--user-flows)
5. [Non-Functional Requirements](#5-non-functional-requirements)
6. [UI/UX Design Requirements](#6-uiux-design-requirements)
7. [Key Performance Indicators (KPIs)](#7-key-performance-indicators-kpis)
8. [Milestones & Dependencies](#8-milestones--dependencies)

---

## 1. Executive Summary

### Objective
To enrich the user interface (UI) and interactive user experience (UX) of the GARA academic platform at the browser layer (client-side) while maintaining zero overhead on server performance. This includes refining Single Page Application (SPA) transits, optimizing static asset rendering, providing robust offline-first local backups, purging leftover developer/debug files (technical debt), and normalizing proctoring visibility guards to properly support mobile iOS devices.

### Problem Statement
Several critical bottlenecks and bugs exist in the current GARA frontend:
1. **Layout distortion and FOUC** (Flash of Unstyled Content) in the Pomodoro widget due to Tailwind Play CDN loaded on top of Bootstrap 5.
2. **Incompatible exam security calculations on iOS Safari**, where URL bar dynamic resizing triggers false-positive DevTools warnings on mobile devices that natively lack console docking.
3. **High server database load** from redundant search queries in classroom resources (materials and tasks).
4. **Inaccessible or tedious external material link review flows** for teachers.
5. **Risky developer debug logs and scrap scripts** left in the public web root.

### Value Proposition
This suite delivers a highly responsive, bandwidth-efficient, secure, and compatible user interface. Server search loads are completely eliminated by moving queries to browser-level instant DOM filters. Teachers benefit from rapid context-sensitive link previews, and iOS mobile students can perform exams securely and smoothly without false-positive cheating interruptions.

---

## 2. Target Audience & Personas

### Persona A: Garuda Akademi Student (iOS & PWA Mobile User)
Secondary school students who actively access classroom courses, discussions, tasks, and exams from their smartphones (with a majority operating iPhone/iPad devices running Safari). They expect low page latencies, quick visual updates, and bug-free exam experiences.

### Persona B: Garuda Akademi Teacher (Material Manager)
Educational instructors who organize syllabus plans, publish coursework resources, and moderate class discussion forums. They require instant, accessible tools to inspect external resource links without losing context.

---

## 3. Scope of Features & Technical Mapping

### In-Scope Features

#### 1. FR-001: Dynamic Client-Side Search & DOM Filter (Ruang Belajar & Tugas)
*   **Description:** Replaces traditional backend search query requests with a high-performance client-side Vanilla JavaScript filtering engine. Intercepts the search bar input event, matches characters locally, and dynamically updates card element visibility in real-time using CSS scale and opacity fade transitions. This cuts material and task HTTP/HTMX database search requests to the server to exactly 0%, saving database transaction overhead.
*   **Priority:** P0 (Critical)
*   **Affected Routes:**
    *   `http://localhost/edu/student/materi` (Route: `student.materi.index`)
    *   `http://localhost/edu/student/tugas` (Route: `student.tugas.index`)
*   **Affected Files:**
    *   `/var/www/html/edu/gara/resources/views/siswa/materi/index.blade.php` [MODIFY]
    *   `/var/www/html/edu/gara/resources/views/siswa/tugas/index.blade.php` [MODIFY]
    *   `/var/www/html/edu/assets/student/js/search_filter.js` [NEW]

#### 2. FR-002: CSS Print Styling for PDF Notes Export
*   **Description:** Applies a custom CSS print media query (`@media print`) to the student personal notes editor view. Hides non-printable assets (navigation menus, editor toolbars, header wraps, and bottom nav pills) and stretches note content to 100% printable width. Automatically injects a clean, official Garuda Akademi school letterhead ('kop surat') at the top of the printed document and formats typography sizes to fit A4 paper beautifully.
*   **Priority:** P1 (High)
*   **Affected Routes:**
    *   `http://localhost/edu/student/ruang-catatan` (Route: `student.ruang-catatan.index`)
*   **Affected Files:**
    *   `/var/www/html/edu/assets/student/css/catatan.css` [MODIFY]
    *   `/var/www/html/edu/gara/resources/views/siswa/ruang_catatan/index.blade.php` [MODIFY]

#### 3. FR-003: Interactive Link Modal & View Icon for Teacher Materials
*   **Description:** Appends a stylish 'View' icon inside the 'Link' data column of the Teacher Materials management table. Clicking the icon opens a secure Bootstrap-5 Modal asynchronouly, detailing all Google Drive, Docs, and YouTube media links assigned to that lesson. All displayed URLs are bound with `target='_blank'` and `rel='noopener noreferrer'` to secure external browsing without losing current Master table contexts.
*   **Priority:** P1 (High)
*   **Affected Routes:**
    *   `http://localhost/edu/guru/materi` (Route: `guru.materi.index`)
*   **Affected Files:**
    *   `/var/www/html/edu/gara/resources/views/guru/materi/index.blade.php` [MODIFY]

#### 4. FR-004: iOS Exam Security Failsafe Normalization
*   **Description:** Implements platform-aware user-agent checks inside GARA's core exam protection module (`protect.js`). If the platform is resolved as iOS (iPhone/iPad/iPod WebKit), the system automatically bypasses the outer-to-inner window size threshold checks ('devToolsDetector') and relaxes keyboard meta/alt blockers that conflict with Safari iOS viewport collapses. This completely mitigates false-positive 'Developer Tools Detected' alerts triggered by mobile Safari browser address/URL bar scrolling.
*   **Priority:** P0 (Critical)
*   **Affected Routes:**
    *   `http://localhost/edu/ruang-ujian/arena` (Route: `ruang-ujian.arena`)
*   **Affected Files:**
    *   `/var/www/html/edu/exam/js/protect.js` [MODIFY]

#### 5. FR-005: Server-Sent Events (SSE) Real-Time Notification & Live Feed
*   **Description:** Connects browser clients to a light persistent unidirectional server event stream using SSE (`EventSource`). Injects elegant pop-up toast overlays dynamically using Vanilla JS when new coursework is assigned, announcements are made, or discussions are updated.
*   **Priority:** P1 (High)
*   **Affected Routes:**
    *   `http://localhost/edu/student/dashboard` (Route: `student.dashboard`)
*   **Affected Files:**
    *   `/var/www/html/edu/gara/routes/web.php` [MODIFY]
    *   `/var/www/html/edu/gara/app/Http/Controllers/Siswa/NotificationController.php` [NEW]
    *   `/var/www/html/edu/assets/student/js/dashboard_v2.js` [MODIFY]

#### 6. FR-006: Keyboard Shortcuts Navigation (Power User Mode)
*   **Description:** Injects a dynamic viewport keydown listener to map shortcut binds: 'Shift + F' triggers Ruang Fokus, 'Shift + C' triggers Ruang Catatan, 'Shift + D' redirects to the Dashboard, and 'Esc' dismisses modals/pauses active Pomodoro timers.
*   **Priority:** P2 (Medium)
*   **Affected Routes:**
    *   All student dashboard and workspace routes under `/student/*`
*   **Affected Files:**
    *   `/var/www/html/edu/gara/resources/views/layouts/siswa.blade.php` [MODIFY]

#### 7. FR-007: Tailwind Play CDN Removal & CSS Framework Conflict Resolution
*   **Description:** Completely purges the Tailwind Play CSS CDN script from the student Ruang Fokus template to resolve CSS reset conflicts with global Bootstrap 5 frameworks. Translates Tailwind layout utility classes into isolated Vanilla CSS styles (`assets/student/css/fokus.css`) aligning with GARA's Custom Harmony theme.
*   **Priority:** P0 (Critical)
*   **Affected Routes:**
    *   `http://localhost/edu/student/ruang-fokus` (Route: `student.ruang-fokus.index`)
*   **Affected Files:**
    *   `/var/www/html/edu/gara/resources/views/siswa/ruang_fokus/index.blade.php` [MODIFY]
    *   `/var/www/html/edu/assets/student/css/fokus.css` [NEW]

#### 8. FR-008: Fixing HTMX SPA Lifecycle & Script Isolation
*   **Description:** Upgrades `htmx_init.js` with listener handlers on `htmx:afterSwap` to re-bind child-view specific JavaScript elements (prayer times API, trivia widgets) dynamically. Hooks `htmx:beforeSwap` to destroy active Pomodoro timer loops, preventing background browser memory leaks.
*   **Priority:** P1 (High)
*   **Affected Routes:**
    *   All dynamic student views accessed via HTMX within `/student/*`
*   **Affected Files:**
    *   `/var/www/html/edu/assets/student/js/htmx_init.js` [MODIFY]

#### 9. FR-009: Visual Gamification Upgrades for Trivia Widget
*   **Description:** Applies a premium 3D CSS flip-card transition animation when selecting trivia questions on the student dashboard. Adds a micro-confetti particle burst explosion using lightweight HTML5 Canvas rendering when a student responds correctly.
*   **Priority:** P2 (Medium)
*   **Affected Routes:**
    *   `http://localhost/edu/student/dashboard` (Route: `student.dashboard`)
*   **Affected Files:**
    *   `/var/www/html/edu/assets/student/js/dashboard_v2.js` [MODIFY]

#### 10. FR-010: Complete Purge of Public Root Debug Files & Redundant Assets
*   **Description:** Permanently deletes insecure leftovers, credentials, and debug files in the web root to eliminate data breach vulnerabilities. Purges double font imports and repeated accessibility script tags across blade templates.
*   **Priority:** P0 (Critical)
*   **Affected Routes:**
    *   All public directory levels and routing scopes
*   **Affected Files:**
    *   `/var/www/html/edu/test_tugas.php` [DELETE]
    *   `/var/www/html/edu/cookies.txt` [DELETE]
    *   `/var/www/html/edu/in_app.html` [DELETE]
    *   `/var/www/html/edu/gara/test_tugas.php` [DELETE]
    *   `/var/www/html/edu/gara/cookies.txt` [DELETE]
    *   `/var/www/html/edu/gara/in_app.html` [DELETE]

### Out-of-Scope Features
*   Synchronizing personal student learning logs/notes to the cloud server database (kept 100% offline-first inside local storage to prevent server load).
*   Implementing a dynamic custom dark/light color mode switcher (omitted to keep school corporate branding styles unified and lightweight).
*   Webcam-based hardware proctoring inside exams (avoided as it creates extreme overhead for low-end student devices).

---

## 4. Functional Requirements & User Flows

### Search DOM Filter Flow
1. Student opens `/student/materi` or `/tugas`.
2. Enters keyboard keywords in the search bar.
3. JS triggers `input` event.
4. JS compares input characters with child card titles.
5. Mismatched elements fade-out and collapse (0ms latency).
6. List updates instantly on-screen.

### Teacher Link Modal Flow
1. Teacher opens `/guru/materi`.
2. Locates a lesson row.
3. Clicks the 'View' icon in the Link column.
4. Bootstrap modal triggers, listing drive files/embeds.
5. Clicks external file link.
6. Hyperlink launches securely in a new browser tab.

### iOS Proctoring Failsafe Flow
1. Student using Safari iOS opens `/ruang-ujian/arena`.
2. JS parses user-agent and matches Apple iPhone/iPad signature.
3. Proctoring engine turns off window resizing discrepancy alert (`devToolsDetector`).
4. Student scrolls or types freely without false visibility alarms.

---

## 5. Non-Functional Requirements

### Performance
*   Search filter latency on student dashboards must average 0 - 5 milliseconds.
*   Static CSS footprint for the revised Pomodoro widget must stay under 15 KB.
*   First Input Delay (FID) on student templates must decrease below 1.5s after purging Play CDNs.

### Security
*   Insecure debug logs (`test_tugas.php`, `cookies.txt`) must be deleted from physical storage to prevent session hijacking.
*   Links inside the Teacher links modal must force `noopener noreferrer` tags to prevent reverse tab-nabbing vulnerabilities.

### Scalability
*   Since course and task searches are entirely offloaded to client-side DOM filters, GARA's concurrent student capacity scales infinitely (10,000+ searches) without putting any stress on the MySQL database layer.

### Compatibility
*   Local search and dynamic modal animations must operate flawlessly on Chrome, Safari, Firefox, Edge, and mobile WebKit.
*   Bypassed proctoring controls must support iOS mobile Safari 14+.

---

## 6. UI/UX Design Requirements
*   **Typography:** 'Inter', sans-serif.
*   **Color Accents:** Blue Royal (`hsl(220, 90%, 50%)`) & Amber Gold (`hsl(35, 95%, 50%)`).
*   **Teacher Preview Modal:** Sleek Bootstrap 5 cards with backdrop blurring (`backdrop-filter: blur(4px)`) for modern, premium visual accents.

---

## 7. Key Performance Indicators (KPIs)

*   **Database Load Reduction during Class Search:** Acheive 100% database search load reduction at the server layer.
*   **iOS False-Positive Exam Proctoring Failures:** Successfully reduce false cheating alarms to 0% for Apple Safari mobile users.
*   **Lighthouse PWA Performance Score:** Attain a score of >= 90 post-CDN purge and inline cleanup.

---

## 8. Milestones & Dependencies

### Milestones
*   **Day 1 - Day 2:** Tailwind CDN Removal & Harmony CSS Refactoring in Pomodoro.
*   **Day 3 - Day 4:** Integration of Client Search Filters & Note PDF Styles.
*   **Day 5 - Day 6:** Teacher Materials View Modals & iOS Exam Security Fixes.
*   **Day 7:** Purging Web Root Leftovers & Final QA Verification.

### Dependencies
Zero third-party API dependencies. The enhancements run cleanly on local resources using standard HTML5, ES6 Vanilla JavaScript, and Bootstrap 5 framework classes.

---
*Created with dedication for Garuda Akademi.*  
**AI Front-End Engineer & Interactive Web Architect** 🚀
