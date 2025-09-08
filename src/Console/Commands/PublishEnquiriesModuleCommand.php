<?php

namespace admin\enquiries\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishEnquiriesModuleCommand extends Command
{
    protected $signature = 'enquiries:publish {--force : Force overwrite existing files}';
    protected $description = 'Publish Enquiries module files with proper namespace transformation';

    public function handle()
    {
        $this->info('Publishing Enquiries module files...');

        // Check if module directory exists
        $moduleDir = base_path('Modules/Enquiries');
        if (!File::exists($moduleDir)) {
            File::makeDirectory($moduleDir, 0755, true);
        }

        // Publish with namespace transformation
        $this->publishWithNamespaceTransformation();

        // Publish other files
        $this->call('vendor:publish', [
            '--tag' => 'enquiry',
            '--force' => $this->option('force')
        ]);

        // Update composer autoload
        $this->updateComposerAutoload();

        $this->info('Enquiries module published successfully!');
        $this->info('Please run: composer dump-autoload');
    }

    protected function publishWithNamespaceTransformation()
    {
        $basePath = dirname(dirname(__DIR__)); // Go up to packages/admin/enquiries/src

        $filesWithNamespaces = [
            // Controllers
            $basePath . '/Controllers/EnquiryManagerController.php' => base_path('Modules/Enquiries/app/Http/Controllers/Admin/EnquiryManagerController.php'),

            // Models
            $basePath . '/Models/Enquiry.php' => base_path('Modules/Enquiries/app/Models/Enquiry.php'),

            // Requests
            $basePath . '/Requests/UpdateEnquiryRequest.php' => base_path('Modules/Enquiries/app/Http/Requests/UpdateEnquiryRequest.php'),

            // Emails
            $basePath . '/Emails/EnquiryReplyByAdminMail.php' => base_path('Modules/Enquiries/app/Emails/EnquiryReplyByAdminMail.php'),

            // Routes
            $basePath . '/routes/web.php' => base_path('Modules/Enquiries/routes/web.php'),
        ];

        foreach ($filesWithNamespaces as $source => $destination) {
            if (File::exists($source)) {
                File::ensureDirectoryExists(dirname($destination));

                $content = File::get($source);
                $content = $this->transformNamespaces($content, $source);

                File::put($destination, $content);
                $this->info("Published: " . basename($destination));
            } else {
                $this->warn("Source file not found: " . $source);
            }
        }
    }

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
            $content = str_replace('use admin\\enquiries\\Models\\Enquiry;', 'use Modules\\Enquiries\\app\\Models\\Enquiry;', $content);
            $content = str_replace('use admin\\enquiries\\Requests\\UpdateEnquiryRequest;', 'use Modules\\Enquiries\\app\\Http\\Requests\\UpdateEnquiryRequest;', $content);
            $content = str_replace(
                'use admin\\enquiries\\Emails\\EnquiryReplyByAdminMail;',
                'use Modules\\Enquiries\\app\\Emails\\EnquiryReplyByAdminMail;',
                $content
            );
        } elseif (str_contains($sourceFile, 'Models')) {
            $content = str_replace(
                'use admin\\admin_auth\\Models\\Admin;',
                'use Modules\\AdminAuth\\app\\Models\\Admin;',
                $content
            );
        }

        return $content;
    }

    protected function updateComposerAutoload()
    {
        $composerFile = base_path('composer.json');
        $composer = json_decode(File::get($composerFile), true);

        // Add module namespace to autoload
        if (!isset($composer['autoload']['psr-4']['Modules\\Enquiries\\'])) {
            $composer['autoload']['psr-4']['Modules\\Enquiries\\'] = 'Modules/Enquiries/app/';

            File::put($composerFile, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->info('Updated composer.json autoload');
        }
    }
}