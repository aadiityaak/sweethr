# Leave Exchange Seeder (Tukar Libur)

This seeder creates sample data for leave exchange requests ("tukar libur") in the SweetHR application.

## Files Created

1. **database/seeders/LeaveExchangeSeeder.php** - Main seeder class
2. **database/factories/LeaveExchangeFactory.php** - Factory for generating leave exchange data
3. **app/Console/Commands/SeedLeaveExchange.php** - Custom command to run the seeder

## Usage

### Option 1: Using the custom command (recommended)

```bash
# Run the seeder
php artisan seed:leave-exchange

# Run the seeder and clear existing data first
php artisan seed:leave-exchange --fresh
```

### Option 2: Using the standard seeder

```bash
# Run only the leave exchange seeder
php artisan db:seed --class=LeaveExchangeSeeder

# Run all seeders (includes leave exchange)
php artisan db:seed
```

### Option 3: Using the factory

```php
// In Tinker or your code
use Database\Factories\LeaveExchangeFactory;

// Create a single leave exchange request
$leaveExchange = LeaveExchangeFactory::new()->create();

// Create multiple leave exchange requests
$leaveExchanges = LeaveExchangeFactory::new()->count(10)->create();

// Create with specific states
$pendingExchange = LeaveExchangeFactory::new()->pending()->create();
$approvedExchange = LeaveExchangeFactory::new()->approved()->create();
$rejectedExchange = LeaveExchangeFactory::new()->rejected()->create();

// Create with specific leave type
$annualLeaveExchange = LeaveExchangeFactory::new()->annualLeave()->create();
$personalLeaveExchange = LeaveExchangeFactory::new()->personalLeave()->create();

// Create for upcoming dates
$upcomingExchange = LeaveExchangeFactory::new()->upcoming()->create();

// Create for past dates
$pastExchange = LeaveExchangeFactory::new()->past()->create();
```

## Data Structure

The seeder creates leave exchange requests with the following characteristics:

### Status Types

- **pending**: Awaiting approval
- **approved**: Approved by admin
- **rejected**: Rejected by admin

### Leave Types

- **Annual Leave (ANNUAL)**: For vacation and personal time
- **Personal Leave (PERSONAL)**: For personal matters and emergencies

### Sample Data Includes

- 18 total leave exchange records
- Mix of pending, approved, and rejected requests
- Various realistic reasons for leave exchange
- Historical data (past 3 months)
- Upcoming requests (future dates)
- Proper admin approval/rejection notes

### Example Reasons

- "Tukar libur untuk menghadiri acara pernikahan saudara di Bandung"
- "Tukar libur karena ada keperluan keluarga yang mendadak di luar kota"
- "Tukar libur untuk mengurus dokumen penting di kantor imigrasi"
- "Tukar libur untuk liburan keluarga ke Yogyakarta"
- "Tukar libur untuk menghadiri wisuda anak di universitas"

## Database Integration

The seeder integrates with the existing database structure:

- Uses `LeaveRequest` model
- References `LeaveType` (ANNUAL, PERSONAL)
- References `User` model for employees and admins
- Follows existing approval workflow
- Maintains proper foreign key relationships

## Testing

To verify the seeder is working correctly:

```bash
# Check total leave exchange records
php artisan tinker --execute="App\Models\LeaveRequest::where('reason', 'like', '%tukar libur%')->count()"

# View sample records
php artisan tinker --execute="App\Models\LeaveRequest::where('reason', 'like', '%tukar libur%')->take(5)->get(['id', 'reason', 'status'])"
```

## Notes

- The seeder automatically clears existing leave exchange data when using the `--fresh` option
- All data is in Indonesian language to match the application's context
- The seeder creates realistic sample data suitable for testing and development
- The factory provides flexible ways to generate test data programmatically
