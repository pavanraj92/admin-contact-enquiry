<?php

namespace admin\enquiries\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;

class TestViewResolutionCommand extends Command
{
    protected $signature = 'enquiries:test-views';
    protected $description = 'Test view resolution for Enquiries module';

    public function handle()
    {
        $this->info('🔍 Testing View Resolution for Enquiries Module...');

        // Test views to check
        $testViews = [
            'admin.index',
            'admin.createOrEdit',
            'admin.show',
        ];

        foreach ($testViews as $viewName) {
            $this->info("\n📄 Testing view: {$viewName}");

            // Test different namespaces
            $namespaces = [
                'enquiries-module::' . $viewName => 'Module View',
                'enquiry::' . $viewName => 'Package View',
            ];

            foreach ($namespaces as $fullPath => $description) {
                try {
                    if (View::exists($fullPath)) {
                        $this->info("  ✅ {$description}: EXISTS - {$fullPath}");

                        // Get the actual file path
                        try {
                            $finder = app('view')->getFinder();
                            $path = $finder->find($fullPath);
                            $this->line("     File: {$path}");
                            $this->line("     Modified: " . date('Y-m-d H:i:s', filemtime($path)));
                        } catch (\Exception $e) {
                            $this->line("     Path resolution failed: {$e->getMessage()}");
                        }
                    } else {
                        $this->warn("  ❌ {$description}: NOT FOUND - {$fullPath}");
                    }
                } catch (\Exception $e) {
                    $this->error("  ❌ {$description}: ERROR - {$e->getMessage()}");
                }
            }
        }

        // Test the dynamic resolution method
        $this->info("\n🎯 Testing Dynamic View Resolution:");
        $controller = new \Modules\Enquiries\app\Http\Controllers\Admin\EnquiryManagerController();

        foreach ($testViews as $viewName) {
            try {
                $reflection = new \ReflectionClass($controller);
                $method = $reflection->getMethod('getViewPath');
                $method->setAccessible(true);

                $resolvedPath = $method->invoke($controller, $viewName);
                $this->info("  📍 {$viewName} → {$resolvedPath}");

                if (View::exists($resolvedPath)) {
                    $this->info("    ✅ Resolved view exists");
                } else {
                    $this->error("    ❌ Resolved view does not exist");
                }
            } catch (\Exception $e) {
                $this->error("  ❌ Error testing {$viewName}: {$e->getMessage()}");
            }
        }

        $this->info("\n📋 View Loading Order:");
        $this->info("1. enquiries-module:: (Module views - highest priority)");
        $this->info("2. enquiry:: (Package views - fallback)");

        $this->info("\n💡 Tips:");
        $this->info("- Edit views in Modules/Enquiries/resources/views/ to use module views");
        $this->info("- Module views will automatically take precedence over package views");
        $this->info("- If module view doesn't exist, it will fallback to package view");
    }
}
