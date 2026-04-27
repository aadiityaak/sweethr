<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BuildProductionStructure extends Command
{
    protected $signature = 'build:production-structure';

    protected $description = 'Create production build structure with Laravel and public_html folders';

    public function handle(): int
    {
        $this->info('Building production structure...');
        $this->newLine();

        $distPath = base_path('dist');
        $laravelPath = $distPath . '/laravel';
        $publicHtmlPath = $distPath . '/public_html';

        $this->prepareDist($distPath, $laravelPath, $publicHtmlPath);

        // Create directories
        File::makeDirectory($laravelPath, 0755, true, true);
        File::makeDirectory($publicHtmlPath, 0755, true, true);

        $this->info('✓ Created dist directories');

        // Copy Laravel files (excluding public directory)
        $this->copyLaravelFiles($laravelPath);
        $this->info('✓ Copied Laravel files (excluded: .env files, node_modules, tests)');

        // Copy public files to public_html
        $this->copyPublicFiles($publicHtmlPath);
        $this->info('✓ Copied public files to public_html (excluded: store folder)');

        // Create public directory in Laravel folder and copy build assets
        $this->createLaravelPublicBuild($laravelPath, $publicHtmlPath);
        $this->info('✓ Created Laravel public/build directory');

        // Modify index.php for production structure
        $this->modifyIndexPhpForProduction($publicHtmlPath);
        $this->info('✓ Modified index.php for production structure');

        // Create production.zip
        $this->createZip($distPath);
        $this->info('✓ Created production.zip');

        $this->newLine();
        $this->info('✅ Production build completed successfully!');
        $this->info('📁 Files created in: ' . $distPath);
        $this->info('📦 Production archive: ' . $distPath . '/production.zip');
        $this->newLine();
        $this->warn('⚠️  Security Note:');
        $this->warn('   - No .env files included (configure manually on server)');
        $this->warn('   - store/ folder excluded from public_html');

        return self::SUCCESS;
    }

    private function copyLaravelFiles(string $destination): void
    {
        $excludePaths = [
            'public',
            'node_modules',
            '.git',
            '.env',
            '.env.example',
            '.env.production',
            '.env.local',
            '.env.testing',
            'dist',
            'storage/app',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/testing',
            'storage/framework/views',
            'tests',
            'phpunit.xml',
            'vite.config.js',
            'package.json',
            'package-lock.json',
            'bun.lock',
            '.gitignore',
            '.editorconfig',
            '.styleci.yml',
            'README.md',
        ];

        $basePath = base_path();

        if (PHP_OS_FAMILY === 'Windows') {
            $result = Process::run(array_merge(
                [
                    'robocopy',
                    $basePath,
                    $destination,
                    '/MIR',
                    '/MT:16',
                    '/R:1',
                    '/W:1',
                    '/NFL',
                    '/NDL',
                    '/NJH',
                    '/NJS',
                ],
                $this->robocopyExcludeDirs($excludePaths),
                $this->robocopyExcludeFiles($excludePaths),
            ));

            if ($result->exitCode() !== null && $result->exitCode() <= 7) {
                $this->ensureStorageDirectories($destination);

                return;
            }
        }

        foreach (File::allFiles($basePath) as $file) {
            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);

            // Skip excluded paths
            $shouldSkip = false;
            foreach ($excludePaths as $excludePath) {
                if (str_starts_with($relativePath, $excludePath . '/') || $relativePath === $excludePath) {
                    $shouldSkip = true;
                    break;
                }
            }

            if ($shouldSkip) {
                continue;
            }

            $destinationFile = $destination . DIRECTORY_SEPARATOR . $relativePath;
            $destinationDir = dirname($destinationFile);

            if (! File::exists($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true);
            }

            File::copy($file->getPathname(), $destinationFile);
        }

        $this->ensureStorageDirectories($destination);
    }

    private function copyPublicFiles(string $destination): void
    {
        $publicPath = base_path('public');

        if (! File::exists($publicPath)) {
            return;
        }

        // Exclude paths that should not be copied
        $excludePaths = ['store'];

        if (PHP_OS_FAMILY === 'Windows') {
            $result = Process::run([
                'robocopy',
                $publicPath,
                $destination,
                '/MIR',
                '/MT:16',
                '/R:1',
                '/W:1',
                '/NFL',
                '/NDL',
                '/NJH',
                '/NJS',
                '/XD',
                'store',
                '/XF',
                'hot',
                'mix-manifest.json',
            ]);

            if ($result->exitCode() !== null && $result->exitCode() <= 7) {
                return;
            }
        }

        foreach (File::allFiles($publicPath) as $file) {
            $relativePath = str_replace($publicPath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);

            // Skip excluded paths
            $shouldSkip = false;
            foreach ($excludePaths as $excludePath) {
                if (str_starts_with($relativePath, $excludePath . '/') || $relativePath === $excludePath) {
                    $shouldSkip = true;
                    break;
                }
            }

            if ($shouldSkip) {
                continue;
            }

            $destinationFile = $destination . DIRECTORY_SEPARATOR . $relativePath;
            $destinationDir = dirname($destinationFile);

            if (! File::exists($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true);
            }

            File::copy($file->getPathname(), $destinationFile);
        }

        // Copy .htaccess explicitly
        $htaccess = $publicPath . '/.htaccess';
        if (File::exists($htaccess)) {
            File::copy($htaccess, $destination . '/.htaccess');
        }

        // Remove development files from production build
        $devFiles = ['hot', 'mix-manifest.json'];
        foreach ($devFiles as $devFile) {
            $devFilePath = $destination . '/' . $devFile;
            if (File::exists($devFilePath)) {
                File::delete($devFilePath);
            }
        }

        // Remove store directory if it was copied (double check)
        $storeDir = $destination . '/store';
        if (File::exists($storeDir)) {
            File::deleteDirectory($storeDir);
        }
    }

    private function modifyIndexPhpForProduction(string $publicHtmlPath): void
    {
        $indexPhpPath = $publicHtmlPath . '/index.php';

        if (! File::exists($indexPhpPath)) {
            $this->error('index.php not found in public_html directory');

            return;
        }

        $content = File::get($indexPhpPath);

        // Replace paths to point to ../laravel/ instead of ../
        $content = str_replace(
            "require __DIR__.'/../vendor/autoload.php';",
            "require __DIR__.'/../laravel/vendor/autoload.php';",
            $content
        );

        $content = str_replace(
            "require_once __DIR__.'/../bootstrap/app.php';",
            "require_once __DIR__.'/../laravel/bootstrap/app.php';",
            $content
        );

        $content = str_replace(
            "file_exists(\$maintenance = __DIR__.'/../storage/framework/maintenance.php')",
            "file_exists(\$maintenance = __DIR__.'/../laravel/storage/framework/maintenance.php')",
            $content
        );

        File::put($indexPhpPath, $content);
    }

    private function createLaravelPublicBuild(string $laravelPath, string $publicHtmlPath): void
    {
        $laravelPublicPath = $laravelPath . '/public';
        $laravelBuildPath = $laravelPublicPath . '/build';
        $publicHtmlBuildPath = $publicHtmlPath . '/build';

        // Create public directory in Laravel folder
        File::makeDirectory($laravelPublicPath, 0755, true, true);
        File::makeDirectory($laravelBuildPath, 0755, true, true);

        // Copy build directory from public_html to laravel/public
        if (File::exists($publicHtmlBuildPath)) {
            // Copy manifest.json
            if (File::exists($publicHtmlBuildPath . '/manifest.json')) {
                File::copy($publicHtmlBuildPath . '/manifest.json', $laravelBuildPath . '/manifest.json');
            }

            // Copy assets directory
            $assetsSource = $publicHtmlBuildPath . '/assets';
            $assetsDestination = $laravelBuildPath . '/assets';

            if (File::exists($assetsSource)) {
                File::makeDirectory($assetsDestination, 0755, true, true);

                // Copy all files in assets directory
                foreach (File::allFiles($assetsSource) as $file) {
                    $relativePath = str_replace($assetsSource . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $destinationFile = $assetsDestination . DIRECTORY_SEPARATOR . $relativePath;
                    $destinationDir = dirname($destinationFile);

                    if (! File::exists($destinationDir)) {
                        File::makeDirectory($destinationDir, 0755, true);
                    }

                    File::copy($file->getPathname(), $destinationFile);
                }
            }
        }
    }

    private function createZip(string $distPath): void
    {
        $zipFile = $distPath . '/production.zip';

        if (File::exists($zipFile)) {
            File::delete($zipFile);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            // Add laravel directory
            $this->addDirectoryToZip($zip, realpath($distPath . '/laravel'), 'laravel');

            // Add public_html directory
            $this->addDirectoryToZip($zip, realpath($distPath . '/public_html'), 'public_html');

            $zip->close();
        }
    }

    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipDir): void
    {
        if (! $dir || ! is_dir($dir)) {
            return;
        }

        $realDir = realpath($dir);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($realDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            $filePath = $file->getRealPath();

            // Calculate relative path from base directory
            $relativePath = str_replace($realDir, '', $filePath);
            $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);
            $relativePath = str_replace('\\', '/', $relativePath);

            // Skip sensitive files and folders (double security check)
            $excludePatterns = ['.env', 'store/'];
            $shouldSkip = false;
            foreach ($excludePatterns as $pattern) {
                if (str_contains($relativePath, $pattern)) {
                    $shouldSkip = true;
                    break;
                }
            }

            if ($shouldSkip) {
                continue;
            }

            // Create zip path
            $zipPath = $relativePath ? $zipDir . '/' . $relativePath : $zipDir;

            if ($file->isDir()) {
                if ($zipPath !== $zipDir) { // Don't add the root directory
                    $zip->addEmptyDir($zipPath);
                }
            } elseif ($file->isFile()) {
                $zip->addFile($filePath, $zipPath);
                if (method_exists($zip, 'setCompressionName')) {
                    $zip->setCompressionName($zipPath, ZipArchive::CM_STORE);
                }
            }
        }
    }

    private function ensureStorageDirectories(string $destination): void
    {
        $storageDirs = [
            'app/public',
            'framework/cache/data',
            'framework/sessions',
            'framework/views',
            'logs',
        ];

        foreach ($storageDirs as $dir) {
            File::makeDirectory($destination . '/storage/' . $dir, 0755, true, true);
        }
    }

    private function robocopyExcludeDirs(array $excludePaths): array
    {
        $dirs = [];
        foreach ($excludePaths as $path) {
            if (str_contains($path, '/')) {
                $firstSegment = explode('/', $path, 2)[0];
                $dirs[$firstSegment] = true;
            } else {
                $dirs[$path] = true;
            }
        }

        $dirList = array_values(array_filter(array_keys($dirs), fn($dir) => $dir !== '' && ! str_contains($dir, '.')));
        if ($dirList === []) {
            return [];
        }

        return array_merge(['/XD'], $dirList);
    }

    private function robocopyExcludeFiles(array $excludePaths): array
    {
        $files = [];
        foreach ($excludePaths as $path) {
            if (! str_contains($path, '/')) {
                $files[] = $path;
            }
        }

        $files[] = '.env*';

        $files = array_values(array_unique($files));
        if ($files === []) {
            return [];
        }

        return array_merge(['/XF'], $files);
    }

    private function prepareDist(string $distPath, string $laravelPath, string $publicHtmlPath): void
    {
        if (File::exists($distPath)) {
            if (File::exists($distPath . '/production.zip')) {
                File::delete($distPath . '/production.zip');
            }
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $emptyDir = rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'sweethr-empty-' . uniqid('', true);
            File::makeDirectory($emptyDir, 0755, true, true);

            foreach ([$laravelPath, $publicHtmlPath] as $target) {
                File::makeDirectory($target, 0755, true, true);
                Process::run([
                    'robocopy',
                    $emptyDir,
                    $target,
                    '/MIR',
                    '/MT:16',
                    '/R:1',
                    '/W:1',
                    '/NFL',
                    '/NDL',
                    '/NJH',
                    '/NJS',
                ]);
            }

            File::deleteDirectory($emptyDir);

            return;
        }

        if (File::exists($laravelPath)) {
            File::deleteDirectory($laravelPath);
        }
        if (File::exists($publicHtmlPath)) {
            File::deleteDirectory($publicHtmlPath);
        }
    }
}
