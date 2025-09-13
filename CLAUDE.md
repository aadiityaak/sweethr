# SweetHR Project

## Project Overview
Laravel-based HR management system with Vue.js frontend and Inertia.js for seamless SPA experience.

### Core Features
- **Employee Management**: Complete employee data management with role/position hierarchy
- **Attendance System**: Location-based attendance with GPS radius validation
- **Working Hours & Shift Management**: Flexible work schedules and shift assignments
- **Leave Management**: Employee leave requests and approval workflow
- **Role-based Access Control**: Different access levels based on employee positions

### Additional HR Features (Potential)
- **Payroll Management**: Salary calculation, overtime, deductions, payslips
- **Performance Reviews**: Employee appraisals, KPI tracking, goal setting
- **Training & Development**: Training schedules, certifications, skill tracking
- **Asset Management**: Company equipment assignment and tracking
- **Document Management**: Employee contracts, certificates, policy documents
- **Announcements**: Company news, policy updates, event notifications
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

## Tech Stack
- **Backend**: Laravel (PHP)
- **Frontend**: Vue 3 with TypeScript
- **Build Tool**: Vite
- **CSS**: TailwindCSS 4.x with custom plugins
- **Components**: Reka UI
- **Icons**: Lucide Vue Next
- **Utilities**: VueUse, Class Variance Authority
- **Maps**: Leaflet.js for interactive maps and geolocation

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
- **announcements** - Company communications
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