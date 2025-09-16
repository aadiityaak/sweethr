# Face Recognition Setup - Issues and Solutions

## Overview
This document details the issues encountered during face recognition setup implementation and their solutions.

## Issues Encountered and Solutions

### 1. 404 Not Found Error
**Problem**: `POST http://127.0.0.1:8000/api/face-recognition/setup 404 (Not Found)`

**Root Cause**: API routes were not registered in `bootstrap/app.php`

**Solution**: Added API routes registration in `bootstrap/app.php`:
```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',  // ← Added this line
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
)
```

### 2. 403 Access Denied on Dashboard
**Problem**: `http://127.0.0.1:8000/dashboard 403 Access denied. Administrator privileges required.`

**Root Cause**: Single dashboard route requiring admin privileges for all users

**Solution**: Separated user and admin dashboard routes in `routes/web.php`:
```php
// User dashboard
Route::get('/dashboard', [DashboardController::class, 'welcome'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Admin dashboard
Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('admin.dashboard');
```

### 3. CSRF Token Not Found
**Problem**: `CSRF token not found. Please refresh the page.`

**Root Cause**: Missing CSRF token meta tag in layout

**Solution**: Added CSRF token meta tag in `resources/views/app.blade.php`:
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### 4. 401 Unauthorized Error
**Problem**: `POST http://127.0.0.1:8000/api/face-recognition/setup 401 (Unauthorized)` even when logged in

**Root Cause**: API routes using `auth:web` middleware but lacking session middleware

**Solution**: Added complete session middleware stack to API routes in `bootstrap/app.php`:
```php
// Add session middleware to API routes for web authentication
$middleware->api(prepend: [
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
]);
```

### 5. 419 CSRF Token Mismatch
**Problem**: `CSRF token mismatch. Please refresh the page.`

**Root Cause**: Stale CSRF token from page load not matching server session

**Solution**: Added fresh CSRF cookie fetch before API call in `useFaceRecognition.ts`:
```typescript
// Ensure CSRF cookie is available before making the request
await fetch('/sanctum/csrf-cookie', {
    credentials: 'same-origin',
});

// Then make the API request with the token
const response = await fetch('/api/face-recognition/setup', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify({ descriptors }),
    credentials: 'same-origin',
});
```

### 6. MySQL JSON Encoding Issue (Previous Session)
**Problem**: `SQLSTATE[22032]: <<Unknown error>>: 3140 Invalid JSON text`

**Root Cause**: Trying to store encrypted string in JSON column

**Solution**: Changed database column type from JSON to TEXT:
```php
// Migration: 2025_09_16_042210_change_face_descriptors_to_text_column.php
$table->text('face_descriptors')->nullable()->change();
```

### 7. Face Descriptor Validation (Previous Session)
**Problem**: Validation error - descriptors must have exactly 128 items

**Root Cause**: Face-api.js returns Float32Array, test data had wrong array size

**Solution**: Proper conversion in `FaceCapture.vue`:
```typescript
// Convert Float32Array to regular array with 128 elements
capturedDescriptors.value.push(Array.from(detection.descriptor));
```

## Key Files Modified

### 1. bootstrap/app.php
- Added API routes registration
- Added session middleware stack to API routes

### 2. resources/views/app.blade.php
- Added CSRF token meta tag

### 3. routes/web.php
- Separated user and admin dashboard routes

### 4. resources/js/composables/useFaceRecognition.ts
- Added fresh CSRF cookie fetch before API calls

### 5. Database Migration
- Changed face_descriptors column from JSON to TEXT

## Testing Commands Used
```bash
# Clear Laravel caches
php artisan config:clear
php artisan route:clear

# Test API authentication
curl -X GET "http://127.0.0.1:8000/api/face-recognition/status" \
     -H "Accept: application/json" \
     -H "X-Requested-With: XMLHttpRequest" \
     --cookie-jar cookies.txt --cookie cookies.txt -v
```

## Final Working Configuration

### API Route Middleware Stack
```php
Route::middleware(['auth:web'])->group(function () {
    Route::prefix('face-recognition')->group(function () {
        Route::post('/setup', [FaceRecognitionController::class, 'setup']);
        // ... other routes
    });
});
```

### Frontend API Call Pattern
```typescript
// 1. Fetch fresh CSRF cookie
await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });

// 2. Get CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// 3. Make authenticated API request
const response = await fetch('/api/face-recognition/setup', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
    },
    body: JSON.stringify({ descriptors }),
    credentials: 'same-origin',
});
```

## Summary
The face recognition setup now works correctly with:
- ✅ Proper API route registration
- ✅ Session sharing between web and API routes
- ✅ CSRF token validation
- ✅ User authentication via web sessions
- ✅ Proper database storage of encrypted face descriptors

## Success Response
```json
{
    "success": true,
    "message": "Pengenalan wajah berhasil disetup.",
    "data": {
        "face_recognition_enabled": true,
        "setup_at": "2025-09-16T04:22:45.878531Z"
    }
}
```