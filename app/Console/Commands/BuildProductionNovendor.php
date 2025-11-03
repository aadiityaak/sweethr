<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class BuildProductionNovendor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:production-novendor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build for production without vendor directory';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting production build without vendor...');

        // 1. Build frontend assets
        $this->info('📦 Building frontend assets...');
        $result = Process::timeout(300) // 5 minutes timeout
            ->run('npm run build');

        if (!$result->successful()) {
            $this->error('❌ Frontend build failed!');
            $this->line($result->errorOutput());
            return 1;
        }

        $this->info('✅ Frontend assets built successfully');

        // 2. Optimize composer autoloader
        $this->info('🔧 Optimizing composer autoloader...');
        $result = Process::timeout(120) // 2 minutes timeout
            ->run('composer dump-autoload --optimize --no-dev');

        if (!$result->successful()) {
            $this->error('❌ Composer optimization failed!');
            $this->line($result->errorOutput());
            return 1;
        }

        $this->info('✅ Composer autoloader optimized');

        // 3. Cache configuration
        $this->info('⚙️ Caching configuration...');
        $this->call('config:cache');

        // 4. Cache routes
        $this->info('🛣️ Caching routes...');
        $this->call('route:cache');

        // 5. Cache views
        $this->info('👁️ Caching views...');
        $this->call('view:cache');

        // 6. Create production-ready zip (optional)
        if ($this->confirm('Do you want to create a production zip file?')) {
            $this->createProductionZip();
        }

        $this->info('🎉 Production build completed successfully!');
        $this->info('📝 Build excludes vendor directory for deployment');

        return 0;
    }

    /**
     * Create a production-ready zip file
     */
    private function createProductionZip()
    {
        $this->info('📦 Creating production zip...');

        $zipName = 'sweethr-production-' . date('Y-m-d-H-i-s') . '.zip';
        $excludePatterns = [
            'node_modules/*',
            'vendor/*',
            '.git/*',
            '.env*',
            'storage/logs/*',
            'storage/framework/cache/*',
            'storage/framework/sessions/*',
            'storage/framework/testing/*',
            'storage/framework/views/*',
            'bootstrap/cache/*',
            '*.log',
            '.DS_Store',
            'Thumbs.db'
        ];

        // Create exclude parameters for zip command
        $excludeParams = collect($excludePatterns)
            ->map(fn($pattern) => "-x '{$pattern}'")
            ->implode(' ');

        $command = "cd .. && zip -r {$zipName} sweethr {$excludeParams}";

        $result = Process::run($command);

        if ($result->successful()) {
            $this->info("✅ Production zip created: ../{$zipName}");
            $this->info('📏 Zip size: ' . $this->formatBytes(filesize("../{$zipName}")));
        } else {
            $this->error('❌ Failed to create zip file');
            $this->line($result->errorOutput());
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
