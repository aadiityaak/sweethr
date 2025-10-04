# Sistem HR Management Project

## Project Overview

Laravel-based HR management system with Vue.js frontend and Inertia.js for seamless SPA experience.

### Core Features

- **Employee Management**: Complete employee data management with role/position hierarchy
- **Attendance System**: Location-based attendance with GPS radius validation
- **Working Hours & Shift Management**: Flexible work schedules and shift assignments
- **Leave Management**: Employee leave requests and approval workflow
- **Role-based Access Control**: Different access levels based on employee positions
- **Announcement System**: Company announcements with banner carousel and modal details ✅

### Additional HR Features (Potential)

- **Payroll Management**: Salary calculation, overtime, deductions, payslips
- **Performance Reviews**: Employee appraisals, KPI tracking, goal setting
- **Training & Development**: Training schedules, certifications, skill tracking
- **Asset Management**: Company equipment assignment and tracking
- **Document Management**: Employee contracts, certificates, policy documents
- **Announcements**: Company news, policy updates, event notifications ✅
- **Employee Self-Service**: Profile updates, document requests, salary slips
- **Reports & Analytics**: Attendance reports, leave balance, performance metrics
- **Shift Management**: Work schedules, shift swapping, overtime tracking
- **Recruitment**: Job postings, candidate tracking, interview scheduling
- **Employee Directory**: Contact information, org chart, team structure
- **Time Tracking**: Project time logging, billable hours (for consulting firms)
- **Expense Management**: Business expense claims and reimbursements
- **Holiday Calendar**: Company holidays, team calendars, event planning

### Attendance System Specifications

- **Location-based Check-in/out**: Employees can only clock in/out within specified radius of office locations
- **GPS Integration**: Uses device GPS to validate employee location
- **Map Integration**: Leaflet.js for interactive maps showing office locations and attendance radius
- **Multi-location Support**: Support for multiple office locations with different radius settings
- **Face Recognition Integration**: Real-time face verification during check-in using face-api.js
    - Face photo capture and storage for attendance verification
    - Confidence-based matching with stored face descriptors
    - Admin interface for viewing captured face photos in attendance records
    - Fallback mechanisms for mandatory vs optional face verification

### Working Hours & Shift Management

- **Flexible Work Schedules**: Support for different working hour patterns
- **Shift Types**: Morning, afternoon, night shifts with custom time ranges
- **Shift Assignment**: Assign employees to specific shifts (daily/weekly/monthly)
- **Shift Swapping**: Allow employees to request shift changes with approval
- **Overtime Tracking**: Automatic calculation of overtime hours
- **Break Time Management**: Track lunch breaks and rest periods
- **Holiday/Weekend Shifts**: Special rates for holiday work
- **Shift Patterns**: Recurring shift schedules (e.g., 2 days on, 1 day off)

#### Example Shift Types:

- **Regular (9-5)**: 09:00 - 17:00 (8 hours)
- **Early Shift**: 06:00 - 14:00 (8 hours)
- **Late Shift**: 14:00 - 22:00 (8 hours)
- **Night Shift**: 22:00 - 06:00 (8 hours)
- **Part-time**: Flexible 4-6 hour shifts

### Announcement System ✅

- **Admin Interface**: Full CRUD for announcements with rich text editor
- **Categorization**: Announcements organized by categories (Umum, Penting, Event, etc.)
- **Priority Levels**: Low, Normal, High, Urgent with visual indicators
- **Image Support**: Upload and display banner images for announcements
- **Publication Control**: Schedule announcements with publish/expire dates
- **Employee Display**: Banner carousel on employee home page with auto-play
- **Modal Details**: Click-to-view full announcement details in popup modal
- **Responsive Design**: Mobile-optimized carousel and modal components

## Tech Stack

- **Backend**: Laravel (PHP)
- **Frontend**: Vue 3 with TypeScript
- **Build Tool**: Vite
- **CSS**: TailwindCSS 4.x with custom plugins
- **Components**: Reka UI
- **Icons**: Lucide Vue Next
- **Utilities**: VueUse, Class Variance Authority
- **Maps**: Leaflet.js for interactive maps and geolocation
- **Face Recognition**: face-api.js for browser-based face detection and verification

## Development Commands

```bash
# Start development server
npm run dev

# Build for production
npm run build

# Build with SSR
npm run build:ssr

# Code formatting
npm run format
npm run format:check

# Linting
npm run lint

# Type checking
vue-tsc --noEmit
```

## Project Structure

- `resources/js/app.ts` - Main frontend entry point
- `resources/js/ssr.ts` - Server-side rendering entry
- `vite.config.ts` - Vite configuration with Laravel plugin

## Key Dependencies

- **@inertiajs/vue3** - For Laravel-Vue integration
- **@tailwindcss/vite** - TailwindCSS integration
- **@laravel/vite-plugin-wayfinder** - Laravel route helpers
- **reka-ui** - UI component library
- **leaflet** - Interactive maps for location-based features (to be added)

## Database Schema (Planned)

### Core Tables

- **users** - Employee accounts with authentication
- **roles/positions** - Job roles and hierarchy levels
- **departments** - Organizational departments
- **office_locations** - Office coordinates and attendance radius
- **work_shifts** - Shift definitions (times, break duration, overtime rules)
- **employee_shifts** - Employee shift assignments and schedules
- **attendances** - Check-in/out records with GPS coordinates and shift validation
- **shift_swaps** - Shift change requests between employees
- **leave_requests** - Employee leave applications
- **leave_types** - Types of leave (sick, annual, etc.)

### Extended Tables (Future Considerations)

- **payrolls** - Salary records and calculations
- **performance_reviews** - Employee evaluations
- **training_records** - Training completion and certifications
- **company_assets** - Equipment and asset assignments
- **documents** - Employee document storage
- **announcements** - Company communications ✅
- **announcement_categories** - Announcement categorization ✅
- **shifts** - Work schedule management
- **expenses** - Business expense claims
- **holidays** - Company calendar and events

## Platform Notes

- Supports both Linux and Windows development environments
- Optional dependencies include platform-specific binaries for optimal performance

## Code Style

- TypeScript strict mode
- Prettier for formatting with Tailwind plugin
- ESLint with Vue and TypeScript rules
- Import organization with prettier-plugin-organize-imports

## Known Issues & Solutions

### Shift Display Mismatch in Admin Panel (RESOLVED)

**Issue**: Shift displayed in admin attendance panel didn't match the shift selected by user during check-in.

**Root Cause**: Admin panel was retrieving shift from `employee_shifts` assignment instead of using the `work_shift_id` saved during check-in.

**Solution**:
1. Updated [Admin/AttendanceController.php](app/Http/Controllers/Admin/AttendanceController.php) to prioritize `work_shift_id` from attendance record
2. Created `attendance:populate-shift-id` command to backfill existing attendance data

**Files Modified**:
- `app/Http/Controllers/Admin/AttendanceController.php` - Use `work_shift_id` from attendance record first, fallback to assignment
- `app/Console/Commands/PopulateAttendanceWorkShiftId.php` - Command to migrate existing data

**Migration Command**:
```bash
# Preview changes
php artisan attendance:populate-shift-id --dry-run

# Apply changes
php artisan attendance:populate-shift-id
```

**Key Lesson**: Always use the actual saved data (user's selection) rather than deriving it from relationships, especially when users can choose from multiple options.

---

### Face Photo Storage Issue (RESOLVED)

**Issue**: Face photos were being captured and saved to file system but not recorded in database (face_photo_path remained null).

**Root Cause**: The `face_photo_path` field was missing from the `$fillable` array in the `Attendance` model, preventing Laravel from saving this field during mass assignment operations.

**Solution**: Added `'face_photo_path'` to the `$fillable` array in `app/Models/Attendance.php`.

**Files Modified**:

- `app/Models/Attendance.php` - Added `face_photo_path` to fillable fields
- `app/Http/Controllers/AttendanceController.php` - Enhanced with debugging logs for troubleshooting

**Key Lesson**: When using mass assignment methods like `updateOrCreate()`, all fields must be explicitly listed in the model's `$fillable` array, even if the database column exists and data is being passed correctly.

## Caching Strategy

### Network-First Approach (CRITICAL)

**Strategy**: All data should use **Network-First, Cache-Fallback** strategy to prevent stale data issues.

**Problem**: Cache-First strategy causes data to not update after modifications (create/update/delete operations). Users see outdated data even after changes.

**Solution Implemented**:

1. **Service Worker (`public/sw.js`)**:
    - All requests (API and pages) use **Network-First** strategy
    - Cache is ONLY used when offline (network fails)
    - When online, always fetch fresh data from server
    - Cache updates in background for offline fallback

2. **Backend Controllers**:
    - Add cache-control headers to prevent browser/proxy caching:

    ```php
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    ```

3. **Frontend (Inertia.js)**:
    - Use `preserveState: false` on mutations (delete/update) to force fresh data reload
    - Use `preserveScroll: false` to reset scroll position after mutations
    - Use `router.reload()` instead of `window.location.reload()` for better UX

**Why This Matters**:

- ✅ Users expect real-time data updates
- ✅ Prevents confusion (deleted items still showing, updates not reflecting)
- ✅ Offline support maintained through cache fallback
- ✅ Performance acceptable as network is typically fast
- ⚠️ **IMPORTANT**: Do NOT cache data except when offline (no internet connection)

**Files Modified**:

- `public/sw.js` - Simplified to Network-First for ALL requests
- Various controllers - Added cache-control headers where needed
- Frontend pages - Use `preserveState: false` for mutations

**Implementation Pattern**:

```javascript
// Service Worker - Network First
fetch(request)
    .then((response) => {
        // Cache for offline fallback
        cache.put(request, response.clone());
        return response;
    })
    .catch(() => {
        // Only use cache if offline
        return cache.match(request);
    });
```

## Admin UI Standards

### Standard Admin Layout Pattern

All admin pages should follow this standardized layout for consistency and better UX:

#### Container Structure

```vue
<AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-auto w-full px-4 py-6 sm:px-6 lg:px-8">
        <!-- Content goes here -->
    </div>
</AppLayout>
```

#### Header Section

```vue
<!-- Header -->
<div class="mb-8">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Page Title
            </h1>
            <p class="mt-1 text-gray-600 dark:text-gray-400">
                Page description or subtitle
            </p>
        </div>
        <div class="flex gap-3">
            <!-- Action buttons if needed -->
        </div>
    </div>
</div>
```

#### Overview Cards

```vue
<!-- Overview Cards -->
<div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="p-6">
            <div class="flex items-center">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[color]-500/10">
                    <Icon class="h-6 w-6 text-[color]-600 dark:text-[color]-400" />
                </div>
                <div class="ml-4 flex-1">
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Label</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">Value</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500">Subtitle</p>
                </div>
            </div>
        </div>
    </div>
</div>
```

#### Main Content Cards

```vue
<!-- Main Content -->
<div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
            <Icon class="mr-2 h-5 w-5 text-[color]-600 dark:text-[color]-400" />
            Section Title
        </h3>
    </div>
    <div class="p-6">
        <!-- Section content -->
    </div>
</div>
```

### Design System Guidelines

- **Max Width**: `w-full max-w-[1200px]` for consistent content width
- **Spacing**: Consistent `mb-8` for major sections, `gap-6` for grids
- **Cards**: Use `shadow-sm ring-1 ring-gray-900/5` for subtle shadows
- **Colors**: Semantic color coding (green=success, red=error, blue=info, orange=warning)
- **Typography**: `text-3xl font-bold` for page titles, proper hierarchy
- **Dark Mode**: Always include dark mode variants
- **Icons**: Use Lucide icons consistently, sized `h-6 w-6` for main icons
- **Responsive**: Mobile-first approach with `sm:` and `lg:` breakpoints

### Applied To Pages

- ✅ `admin/Attendance/Show.vue` - Complete redesign with maps
- ✅ `admin/Attendance/Index.vue` - Updated layout and cards
- ✅ `admin/Dashboard.vue` - Updated container structure
- ✅ `admin/Announcement/Index.vue` - Full CRUD with standardized layout
- ✅ `admin/Announcement/Create.vue` - Create form with image upload
- ✅ `admin/Announcement/Edit.vue` - Edit form with existing data
- 🔄 Other admin pages - To be standardized

## Feature Progress Tracking

See `RENCANA_FITUR.md` for detailed feature roadmap with time estimates:

- **Total Features**: 50 planned
- **Completed**: 8 features (16%) - 24 work days
- **Remaining**: 42 features (84%) - 135 work days
- **Total Estimate**: 159 work days (~32 weeks / 8 months)

### Recent Completed Features:

- ✅ Announcement System (2 days) - Banner carousel with modal details
- ✅ Face Recognition (6 days) - Real-time face verification
- ✅ Leave Management (3 days) - Annual leave requests
- ✅ Employee Management (4 days) - CRUD with role-based access

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to enhance the user's satisfaction building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3.12
- inertiajs/inertia-laravel (INERTIA) - v2
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/wayfinder (WAYFINDER) - v0
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- @inertiajs/vue3 (INERTIA) - v2
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove it works. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure - don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

## Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan

- Use the `list-artisan-commands` tool when you need to call an Artisan command to double check the available parameters.

## URLs

- Whenever you share a project URL with the user you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain / IP, and port.

## Tinker / Debugging

- You should use the `tinker` tool when you need to execute PHP to debug code or query Eloquent models directly.
- Use the `database-query` tool when you only need to read from the database.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before any other approaches. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation specific for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- The 'search-docs' tool is perfect for all Laravel related packages, including Laravel, Inertia, Livewire, Filament, Tailwind, Pest, Nova, Nightwatch, etc.
- You must use this tool to search for Laravel-ecosystem documentation before falling back to other approaches.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic based queries to start. For example: `['rate limiting', 'routing rate limiting', 'routing']`.
- Do not add package names to queries - package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

- You can and should pass multiple queries at once. The most relevant results will be returned first.

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit"
3. Quoted Phrases (Exact Position) - query="infinite scroll" - Words must be adjacent and in that order
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit"
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms

=== php rules ===

## PHP

- Always use curly braces for control structures, even if it has one line.

### Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - <code-snippet>public function \_\_construct(public GitHub $github) { }</code-snippet>
- Do not allow empty `__construct()` methods with zero parameters.

### Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<code-snippet name="Explicit Return Types and Method Params" lang="php">
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
</code-snippet>

## Comments

- Prefer PHPDoc blocks over comments. Never use comments within the code itself unless there is something _very_ complex going on.

## PHPDoc Blocks

- Add useful array shape type definitions for arrays when appropriate.

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

=== inertia-laravel/core rules ===

## Inertia Core

- Inertia.js components should be placed in the `resources/js/Pages` directory unless specified differently in the JS bundler (vite.config.js).
- Use `Inertia::render()` for server-side routing instead of traditional Blade views.
- Use `search-docs` for accurate guidance on all things Inertia.

<code-snippet lang="php" name="Inertia::render Example">
// routes/web.php example
Route::get('/users', function () {
    return Inertia::render('Users/Index', [
        'users' => User::all()
    ]);
});
</code-snippet>

=== inertia-laravel/v2 rules ===

## Inertia v2

- Make use of all Inertia features from v1 & v2. Check the documentation before making any changes to ensure we are taking the correct approach.

### Inertia v2 New Features

- Polling
- Prefetching
- Deferred props
- Infinite scrolling using merging props and `WhenVisible`
- Lazy loading data on scroll

### Deferred Props & Empty States

- When using deferred props on the frontend, you should add a nice empty state with pulsing / animated skeleton.

### Inertia Form General Guidance

- The recommended way to build forms when using Inertia is with the `<Form>` component - a useful example is below. Use `search-docs` with a query of `form component` for guidance.
- Forms can also be built using the `useForm` helper for more programmatic control, or to follow existing conventions. Use `search-docs` with a query of `useForm helper` for guidance.
- `resetOnError`, `resetOnSuccess`, and `setDefaultsOnSuccess` are available on the `<Form>` component. Use `search-docs` with a query of 'form component resetting' for guidance.

=== laravel/core rules ===

## Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using the `list-artisan-commands` tool.
- If you're creating a generic PHP class, use `artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `list-artisan-commands` to check the available options to `php artisan make:model`.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

### Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

### Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

### Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

### URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

### Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

### Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] <name>` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

### Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

## Laravel 12

- Use the `search-docs` tool to get version specific documentation.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

### Laravel 12 Structure

- No middleware files in `app/Http/Middleware/`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- **No app\Console\Kernel.php** - use `bootstrap/app.php` or `routes/console.php` for console configuration.
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available and do not require manual registration.

### Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 11 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

## Laravel Pint Code Formatter

- You must run `vendor/bin/pint --dirty` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test`, simply run `vendor/bin/pint` to fix any formatting issues.

=== phpunit/core rules ===

## PHPUnit Core

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit <name>` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should test all of the happy paths, failure paths, and weird paths.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files, these are core to the application.

### Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test`.
- To run all tests in a file: `php artisan test tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --filter=testName` (recommended after making a change to a related file).

=== inertia-vue/core rules ===

## Inertia + Vue

- Vue components must have a single root element.
- Use `router.visit()` or `<Link>` for navigation instead of traditional links.

<code-snippet name="Inertia Client Navigation" lang="vue">

    import { Link } from '@inertiajs/vue3'
    <Link href="/">Home</Link>

</code-snippet>

=== inertia-vue/v2/forms rules ===

## Inertia + Vue Forms

<code-snippet name="`<Form>` Component Example" lang="vue">

<Form
    action="/users"
    method="post"
    #default="{
        errors,
        hasErrors,
        processing,
        progress,
        wasSuccessful,
        recentlySuccessful,
        setError,
        clearErrors,
        resetAndClearErrors,
        defaults,
        isDirty,
        reset,
        submit,
  }"
>
    <input type="text" name="name" />

    <div v-if="errors.name">
        {{ errors.name }}
    </div>

    <button type="submit" :disabled="processing">
        {{ processing ? 'Creating...' : 'Create User' }}
    </button>

    <div v-if="wasSuccessful">User created successfully!</div>

</Form>

</code-snippet>

=== tailwindcss/core rules ===

## Tailwind Core

- Use Tailwind CSS classes to style HTML, check and use existing tailwind conventions within the project before writing your own.
- Offer to extract repeated patterns into components that match the project's conventions (i.e. Blade, JSX, Vue, etc..)
- Think through class placement, order, priority, and defaults - remove redundant classes, add classes to parent or child carefully to limit repetition, group elements logically
- You can use the `search-docs` tool to get exact examples from the official documentation when needed.

### Spacing

- When listing items, use gap utilities for spacing, don't use margins.

        <code-snippet name="Valid Flex Gap Spacing Example" lang="html">
            <div class="flex gap-8">
                <div>Superior</div>
                <div>Michigan</div>
                <div>Erie</div>
            </div>
        </code-snippet>

### Dark Mode

- If existing pages and components support dark mode, new pages and components must support dark mode in a similar way, typically using `dark:`.

=== tailwindcss/v4 rules ===

## Tailwind 4

- Always use Tailwind CSS v4 - do not use the deprecated utilities.
- `corePlugins` is not supported in Tailwind v4.
- In Tailwind v4, you import Tailwind using a regular CSS `@import` statement, not using the `@tailwind` directives used in v3:

<code-snippet name="Tailwind v4 Import Tailwind Diff" lang="diff"

- @tailwind base;
- @tailwind components;
- @tailwind utilities;

* @import "tailwindcss";
  </code-snippet>

### Replaced Utilities

- Tailwind v4 removed deprecated utilities. Do not use the deprecated option - use the replacement.
- Opacity values are still numeric.

| Deprecated | Replacement |
|------------+--------------|
| bg-opacity-_ | bg-black/_ |
| text-opacity-_ | text-black/_ |
| border-opacity-_ | border-black/_ |
| divide-opacity-_ | divide-black/_ |
| ring-opacity-_ | ring-black/_ |
| placeholder-opacity-_ | placeholder-black/_ |
| flex-shrink-_ | shrink-_ |
| flex-grow-_ | grow-_ |
| overflow-ellipsis | text-ellipsis |
| decoration-slice | box-decoration-slice |
| decoration-clone | box-decoration-clone |

=== tests rules ===

## Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test` with a specific filename or filter.
  </laravel-boost-guidelines>
