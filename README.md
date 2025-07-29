# Enquiry Management Package

A comprehensive Laravel package for managing customer enquiries in the admin panel.

## Features

- **Complete CRUD Operations**: Create, read, update, and delete enquiries
- **Status Management**: Track enquiry status (new, draft, replied, closed)
- **Email Notifications**: Automatic email replies to customers
- **Search & Filter**: Search by name/email and filter by status
- **Sortable Columns**: Sort enquiries by various fields
- **Permission-based Access**: Role-based permissions for different **actions**
- **Responsive UI**: Modern, responsive admin interface

## Installation

1. **Add the package to your Laravel project**:
   ```bash
   composer require admin/enquiry
   ```

2. **Publish the migrations**:
   ```bash
   php artisan vendor:publish --tag=enquiry-migrations
   ```

3. **Run the migrations**:
   ```bash
   php artisan migrate
   ```

4. **Register the service provider** (if not auto-discovered):
   ```php
   // config/app.php
   'providers' => [
       // ...
       admin\enquiry\Providers\EnquiryServiceProvider::class,
   ];
   ```

## Database Schema

The package creates an `enquiries` table with the following structure:

```php
Schema::create('enquiries', function (Blueprint $table) {
    $table->id();
    $table->string('first_name')->nullable();
    $table->string('last_name')->nullable();
    $table->string('name')->nullable();
    $table->string('email')->nullable();
    $table->text('message')->nullable();
    $table->text('admin_reply')->nullable();
    $table->enum('status', ['new', 'draft', 'replied', 'closed'])->default('new');
    $table->boolean('is_replied')->default(false);
    $table->timestamp('replied_at')->nullable();
    $table->foreignId('replied_by')->nullable()->constrained('admins')->nullOnDelete();
    $table->timestamps();

    $table->index('is_replied');
    $table->index('status');
});
```

## Usage

### Routes

The package automatically registers the following routes:

```php
Route::prefix('admin')->name('admin.')->middleware(['web', 'admin.auth'])->group(function () {
    Route::post('enquiries/{enquiry}/close-status', [EnquiryManagerController::class, 'closeStatus']);
    Route::resource('enquiries', EnquiryManagerController::class);
});
```

### Available Routes

- `GET /admin/enquiries` - List all enquiries
- `GET /admin/enquiries/{enquiry}` - View enquiry details
- `GET /admin/enquiries/{enquiry}/edit` - Edit/reply to enquiry
- `PUT /admin/enquiries/{enquiry}` - Update enquiry (reply or save draft)
- `DELETE /admin/enquiries/{enquiry}` - Delete enquiry
- `POST /admin/enquiries/{enquiry}/close-status` - Close enquiry status

### Permissions

The package uses the following permissions:

- `enquiry_manager_list` - View enquiry list
- `enquiry_manager_view` - View individual enquiry
- `enquiry_manager_reply` - Reply to enquiries
- `enquiry_manager_delete` - Delete enquiries

### Models

#### Enquiry Model

```php
use admin\enquiry\Models\Enquiry;

// Get all enquiries
$enquiries = Enquiry::all();

// Filter enquiries
$enquiries = Enquiry::filter('search term')->filterByStatus('new')->get();

// Get paginated enquiries
$enquiries = Enquiry::paginate(10);
```

### Controllers

#### EnquiryManagerController

The main controller handles all enquiry management operations:

- `index()` - Display listing with search and filter
- `show()` - Display individual enquiry
- `edit()` - Show reply form
- `update()` - Save reply or draft
- `destroy()` - Delete enquiry
- `closeStatus()` - Close enquiry status

### Views

The package includes the following Blade views:

- `enquiry::admin.index` - Enquiry listing page
- `enquiry::admin.show` - Enquiry details page
- `enquiry::admin.createOrEdit` - Reply form page
- `enquiry::emails.reply` - Email template for replies

### Email Notifications

When an admin replies to an enquiry, an email is automatically sent to the customer using the `EnquiryReplyByAdminMail` class.

## Configuration

### Customizing Email Templates

You can customize the email template by publishing the views:

```bash
php artisan vendor:publish --tag=enquiry-views
```

### Customizing Validation Rules

You can extend the `UpdateEnquiryRequest` class to add custom validation rules:

```php
class CustomUpdateEnquiryRequest extends UpdateEnquiryRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        $rules['admin_reply'][] = 'custom_rule';
        return $rules;
    }
}
```

## API Endpoints

The package also provides API endpoints for integration with external systems:

- `GET /api/admin/enquiries` - List enquiries (JSON)
- `GET /api/admin/enquiries/{enquiry}` - Get enquiry details (JSON)
- `PUT /api/admin/enquiries/{enquiry}` - Update enquiry (JSON)
- `DELETE /api/admin/enquiries/{enquiry}` - Delete enquiry (JSON)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a pull request

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT). 
