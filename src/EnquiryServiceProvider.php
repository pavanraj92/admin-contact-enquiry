<?php

namespace admin\enquiries;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class EnquiryServiceProvider extends ServiceProvider
{

    public function boot()
    {
        // Load routes, views, migrations from the package  
        $this->loadViewsFrom([
            base_path('Modules/Enquiries/resources/views'), // Published module views first
            resource_path('views/admin/enquiry'), // Published views second
            __DIR__ . '/../resources/views'      // Package views as fallback
        ], 'enquiries');

        // Load published module config first (if it exists), then fallback to package config
        if (file_exists(base_path('Modules/Enquiries/config/enquiry.php'))) {
            $this->mergeConfigFrom(base_path('Modules/Enquiries/config/enquiry.php'), 'enquiry.constants');
        } else {
            // Fallback to package config if published config doesn't exist
            $this->mergeConfigFrom(__DIR__ . '/../config/enquiry.php', 'enquiry.constants');
        }

        // Also register module views with a specific namespace for explicit usage
        if (is_dir(base_path('Modules/Enquiries/resources/views'))) {
            $this->loadViewsFrom(base_path('Modules/Enquiries/resources/views'), 'enquiries-module');
        }
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // Also load migrations from published module if they exist
        if (is_dir(base_path('Modules/Enquiries/database/migrations'))) {
            $this->loadMigrationsFrom(base_path('Modules/Enquiries/database/migrations'));
        }

        // Only publish automatically during package installation, not on every request
        // Use 'php artisan enquiries:publish' command for manual publishing
        // $this->publishWithNamespaceTransformation();

        // Standard publishing for non-PHP files
        $this->publishes([
            __DIR__ . '/../config/' => base_path('Modules/Enquiries/config/'),
            __DIR__ . '/../database/migrations' => base_path('Modules/Enquiries/database/migrations'),
            __DIR__ . '/../resources/views' => base_path('Modules/Enquiries/resources/views/'),
        ], 'enquiry');

        $this->registerAdminRoutes();
    }

    protected function registerAdminRoutes()
    {
        if (!Schema::hasTable('admins')) {
            return; // Avoid errors before migration
        }

        $admin = DB::table('admins')
            ->orderBy('created_at', 'asc')
            ->first();

        $slug = $admin->website_slug ?? 'admin';

        Route::middleware('web')
            ->prefix("{$slug}/admin") // dynamic prefix
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
            });
    }

    public function register()
    {
        // Register the publish command
        if ($this->app->runningInConsole()) {
            $this->commands([
                \admin\enquiries\Console\Commands\PublishEnquiriesModuleCommand::class,
                \admin\enquiries\Console\Commands\CheckModuleStatusCommand::class,
                \admin\enquiries\Console\Commands\DebugEnquiriesCommand::class,
                \admin\enquiries\Console\Commands\TestViewResolutionCommand::class,
            ]);
        }
    }

    /**
     * Publish files with namespace transformation
     */
    protected function publishWithNamespaceTransformation()
    {
        // Define the files that need namespace transformation
        $filesWithNamespaces = [
            // Controllers
            __DIR__ . '/../src/Controllers/EnquiryManagerController.php' => base_path('Modules/Enquiries/app/Http/Controllers/Admin/EnquiryManagerController.php'),

            // Models
            __DIR__ . '/../src/Models/Enquiry.php' => base_path('Modules/Enquiries/app/Models/Enquiry.php'),

            // Requests
            __DIR__ . '/../src/Requests/UpdateEnquiryRequest.php' => base_path('Modules/Enquiries/app/Http/Requests/UpdateEnquiryRequest.php'),

            // Emails
            __DIR__ . '/../src/Emails/EnquiryReplyByAdminMail.php' => base_path('Modules/Enquiries/app/Emails/EnquiryReplyByAdminMail.php'),

            // Routes
            __DIR__ . '/routes/web.php' => base_path('Modules/Enquiries/routes/web.php'),
        ];

        foreach ($filesWithNamespaces as $source => $destination) {
            if (File::exists($source)) {
                // Create destination directory if it doesn't exist
                File::ensureDirectoryExists(dirname($destination));

                // Read the source file
                $content = File::get($source);

                // Transform namespaces based on file type
                $content = $this->transformNamespaces($content, $source);

                // Write the transformed content to destination
                File::put($destination, $content);
            }
        }
    }

    /**
     * Transform namespaces in PHP files
     */
    protected function transformNamespaces($content, $sourceFile)
    {
        // Define namespace mappings
        $namespaceTransforms = [
            // Main namespace transformations
            'namespace admin\\enquiries\\Controllers;' => 'namespace Modules\\Enquiries\\app\\Http\\Controllers\\Admin;',
            'namespace admin\\enquiries\\Models;' => 'namespace Modules\\Enquiries\\app\\Models;',
            'namespace admin\\enquiries\\Requests;' => 'namespace Modules\\Enquiries\\app\\Http\\Requests;',
            'namespace admin\\enquiries\\Emails;' => 'namespace Modules\\Enquiries\\app\\Emails;',

            // Use statements transformations
            'use admin\\enquiries\\Controllers\\' => 'use Modules\\Enquiries\\app\\Http\\Controllers\\Admin\\',
            'use admin\\enquiries\\Models\\' => 'use Modules\\Enquiries\\app\\Models\\',
            'use admin\\enquiries\\Requests\\' => 'use Modules\\Enquiries\\app\\Http\\Requests\\',
            'use admin\\enquiries\\Emails\\' => 'use Modules\\Enquiries\\app\\Emails\\',

            // Class references in routes
            'admin\\enquiries\\Controllers\\EnquiryManagerController' => 'Modules\\Enquiries\\app\\Http\\Controllers\\Admin\\EnquiryManagerController',
        ];

        // Apply transformations
        foreach ($namespaceTransforms as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        // Handle specific file types
        if (str_contains($sourceFile, 'Controllers')) {
            $content = $this->transformControllerNamespaces($content);
        } elseif (str_contains($sourceFile, 'Models')) {
            $content = $this->transformModelNamespaces($content);
        } elseif (str_contains($sourceFile, 'Requests')) {
            $content = $this->transformRequestNamespaces($content);
        } elseif (str_contains($sourceFile, 'routes')) {
            $content = $this->transformRouteNamespaces($content);
        }

        return $content;
    }

    /**
     * Transform controller-specific namespaces
     */
    protected function transformControllerNamespaces($content)
    {
        // Update use statements for models and requests
        $content = str_replace(
            'use admin\\enquiries\\Models\\Enquiry;',
            'use Modules\\Enquiries\\app\\Models\\Enquiry;',
            $content
        );
        $content = str_replace(
            'use admin\\enquiries\\Emails\\EnquiryReplyByAdminMail;',
            'use Modules\\Enquiries\\app\\Emails\\EnquiryReplyByAdminMail;',
            $content
        );

        $content = str_replace(
            'use admin\\enquiries\\Requests\\UpdateEnquiryRequest;',
            'use Modules\\Enquiries\\app\\Http\\Requests\\UpdateEnquiryRequest;',
            $content
        );

        return $content;
    }

    /**
     * Transform model-specific namespaces
     */
    protected function transformModelNamespaces($content)
    {
        // Any model-specific transformations
        $content = str_replace(
            'use admin\\admin_auth\\Models\\Admin;',
            'use Modules\\AdminAuth\\app\\Models\\Admin;',
            $content
        );
        return $content;
    }

    /**
     * Transform request-specific namespaces
     */
    protected function transformRequestNamespaces($content)
    {
        // Any request-specific transformations
        return $content;
    }

    /**
     * Transform route-specific namespaces
     */
    protected function transformRouteNamespaces($content)
    {
        // Update controller references in routes
        $content = str_replace(
            'admin\\enquiries\\Controllers\\EnquiryManagerController',
            'Modules\\Enquiries\\app\\Http\\Controllers\\Admin\\EnquiryManagerController',
            $content
        );

        return $content;
    }
}