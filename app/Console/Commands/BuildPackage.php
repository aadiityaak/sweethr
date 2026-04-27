<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BuildPackage extends Command
{
    protected $signature = 'build:package';

    protected $description = 'Create WordPress-style package with installer system';

    public function handle(): int
    {
        $this->info('Building package with installer...');

        $packagePath = base_path('package');
        $installPath = $packagePath.'/install';

        // Create directories
        File::makeDirectory($packagePath, 0755, true, true);
        File::makeDirectory($installPath, 0755, true, true);

        $this->info('Created package directories');

        // Copy all files except excluded ones
        $this->copyApplicationFiles($packagePath);
        $this->info('Copied application files');

        // Create installer files
        $this->createInstaller($installPath);
        $this->info('Created installer system');

        // Create package.zip
        $this->createPackageZip($packagePath);
        $this->info('Created package.zip');

        $this->info('✅ Package build completed successfully!');
        $this->info('📁 Package created in: '.$packagePath);
        $this->info('📦 Package archive: '.$packagePath.'/package.zip');

        return self::SUCCESS;
    }

    private function copyApplicationFiles(string $destination): void
    {
        $excludePaths = [
            'package',
            'dist',
            'node_modules',
            '.git',
            '.env',
            '.env.example',
            'storage/logs',
            'storage/framework/cache',
            'storage/framework/sessions',
            'storage/framework/views',
            'tests',
            'phpunit.xml',
            '.gitignore',
            '.editorconfig',
            '.styleci.yml',
            'README.md',
        ];

        $basePath = base_path();

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = str_replace($basePath.DIRECTORY_SEPARATOR, '', $item->getPathname());
            $relativePath = str_replace('\\', '/', $relativePath);

            // Skip excluded paths
            $shouldSkip = false;
            foreach ($excludePaths as $excludePath) {
                if (str_starts_with($relativePath, $excludePath.'/') || $relativePath === $excludePath) {
                    $shouldSkip = true;
                    break;
                }
            }

            if ($shouldSkip) {
                continue;
            }

            $destinationPath = $destination.DIRECTORY_SEPARATOR.$relativePath;

            if ($item->isDir()) {
                if (! File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true);
                }
            } elseif ($item->isFile()) {
                $destinationDir = dirname($destinationPath);
                if (! File::exists($destinationDir)) {
                    File::makeDirectory($destinationDir, 0755, true);
                }
                File::copy($item->getPathname(), $destinationPath);
            }
        }

        // Copy .env.production as .env if it exists
        $envProduction = base_path('.env.production');
        if (File::exists($envProduction)) {
            File::copy($envProduction, $destination.'/.env');
        }

        // Create necessary storage directories
        $storageDirs = [
            'app/public',
            'framework/cache/data',
            'framework/sessions',
            'framework/views',
            'logs',
        ];

        foreach ($storageDirs as $dir) {
            File::makeDirectory($destination.'/storage/'.$dir, 0755, true, true);
        }
    }

    private function createInstaller(string $installPath): void
    {
        // Create main installer index
        $indexContent = <<<'PHP'
<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prevent direct access to other files
if (!defined('INSTALLER_ACCESS')) {
    define('INSTALLER_ACCESS', true);
}

require_once 'config.php';
require_once 'functions.php';

$step = $_GET['step'] ?? 'welcome';

switch ($step) {
    case 'welcome':
        include 'steps/welcome.php';
        break;
    case 'requirements':
        include 'steps/requirements.php';
        break;
    case 'database':
        include 'steps/database.php';
        break;
    case 'install':
        include 'steps/install.php';
        break;
    case 'complete':
        include 'steps/complete.php';
        break;
    default:
        include 'steps/welcome.php';
}
PHP;

        File::put($installPath.'/index.php', $indexContent);

        // Create installer config
        $configContent = <<<'PHP'
<?php
if (!defined('INSTALLER_ACCESS')) {
    exit('Direct access not permitted');
}

// Application configuration
define('APP_NAME', 'Sistem HR');
define('APP_VERSION', '1.0.1');
define('MIN_PHP_VERSION', '8.1.0');

// Required PHP extensions
$required_extensions = [
    'openssl',
    'pdo',
    'mbstring',
    'tokenizer',
    'xml',
    'ctype',
    'json',
    'bcmath',
    'curl',
    'fileinfo',
    'zip',
];

// Required directories (writable)
$required_directories = [
    '../storage/app',
    '../storage/framework',
    '../storage/logs',
    '../bootstrap/cache',
];

// Database configuration
$supported_databases = [
    'mysql' => 'MySQL 5.7+',
    'sqlite' => 'SQLite 3.8+',
    'pgsql' => 'PostgreSQL 9.4+',
];
PHP;

        File::put($installPath.'/config.php', $configContent);

        // Create installer functions
        $functionsContent = <<<'PHP'
<?php
if (!defined('INSTALLER_ACCESS')) {
    exit('Direct access not permitted');
}

function checkPhpVersion() {
    return version_compare(PHP_VERSION, MIN_PHP_VERSION, '>=');
}

function checkExtensions() {
    global $required_extensions;
    $missing = [];

    foreach ($required_extensions as $ext) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext;
        }
    }

    return $missing;
}

function checkDirectories() {
    global $required_directories;
    $issues = [];

    foreach ($required_directories as $dir) {
        if (!is_dir($dir)) {
            $issues[] = "$dir - Directory does not exist";
        } elseif (!is_writable($dir)) {
            $issues[] = "$dir - Directory is not writable";
        }
    }

    return $issues;
}

function testDatabaseConnection($config) {
    try {
        $dsn = "{$config['driver']}:host={$config['host']};port={$config['port']};dbname={$config['database']}";
        $pdo = new PDO($dsn, $config['username'], $config['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

function generateAppKey() {
    return 'base64:' . base64_encode(random_bytes(32));
}

function createEnvFile($config) {
    $envContent = "APP_NAME=Sistem_HR
APP_ENV=production
APP_KEY={$config['app_key']}
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL={$config['app_url']}

DB_CONNECTION={$config['db_driver']}
DB_HOST={$config['db_host']}
DB_PORT={$config['db_port']}
DB_DATABASE={$config['db_name']}
DB_USERNAME={$config['db_username']}
DB_PASSWORD={$config['db_password']}

BROADCAST_CONNECTION=log
CACHE_STORE=file
FILESYSTEM_DISK=local
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=\"hello@example.com\"
MAIL_FROM_NAME=\"\${APP_NAME}\"
";

    return file_put_contents('../.env', $envContent);
}

function runArtisanCommands() {
    $commands = [
        'php ../artisan key:generate --force',
        'php ../artisan migrate --force',
        'php ../artisan db:seed --force',
        'php ../artisan storage:link --force',
        'php ../artisan config:cache',
        'php ../artisan route:cache',
        'php ../artisan view:cache',
    ];

    $output = [];
    foreach ($commands as $command) {
        $result = shell_exec($command . ' 2>&1');
        $output[] = ['command' => $command, 'output' => $result];
    }

    return $output;
}

function cleanupInstaller() {
    // Remove installer directory after successful installation
    $installerPath = __DIR__;

    function deleteDirectory($dir) {
        if (!is_dir($dir)) return false;

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? deleteDirectory($path) : unlink($path);
        }

        return rmdir($dir);
    }

    return deleteDirectory($installerPath);
}
PHP;

        File::put($installPath.'/functions.php', $functionsContent);

        // Create installer steps directory
        $stepsPath = $installPath.'/steps';
        File::makeDirectory($stepsPath, 0755, true, true);

        // Create welcome step
        $welcomeContent = <<<'PHP'
<?php if (!defined('INSTALLER_ACCESS')) exit('Direct access not permitted'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> Installer</title>
    <link href="https://cdn.tailwindcss.com/3.3.0" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800"><?= APP_NAME ?></h1>
            <p class="text-gray-600 mt-2">Version <?= APP_VERSION ?></p>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-semibold mb-4">Welcome to the Installer</h2>
            <p class="text-gray-700 mb-4">
                This installer will guide you through the setup process for your HR Management application.
            </p>
            <p class="text-gray-700">
                Please ensure you have your database credentials ready before proceeding.
            </p>
        </div>

        <div class="text-center">
            <a href="?step=requirements" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition">
                Get Started
            </a>
        </div>
    </div>
</body>
</html>
PHP;

        File::put($stepsPath.'/welcome.php', $welcomeContent);

        // Create requirements step
        $requirementsContent = <<<'PHP'
<?php
if (!defined('INSTALLER_ACCESS')) exit('Direct access not permitted');

$phpOk = checkPhpVersion();
$missingExtensions = checkExtensions();
$directoryIssues = checkDirectories();
$allGood = $phpOk && empty($missingExtensions) && empty($directoryIssues);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> Installer - Requirements</title>
    <link href="https://cdn.tailwindcss.com/3.3.0" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto max-w-2xl bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-2xl font-bold mb-6">System Requirements</h1>

        <!-- PHP Version -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">PHP Version</h3>
            <div class="flex items-center">
                <span class="<?= $phpOk ? 'text-green-600' : 'text-red-600' ?>">
                    <?= $phpOk ? '✓' : '✗' ?> PHP <?= PHP_VERSION ?>
                </span>
                <span class="text-gray-500 ml-2">(Required: <?= MIN_PHP_VERSION ?>+)</span>
            </div>
        </div>

        <!-- PHP Extensions -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">PHP Extensions</h3>
            <?php if (empty($missingExtensions)): ?>
                <p class="text-green-600">✓ All required extensions are installed</p>
            <?php else: ?>
                <div class="text-red-600">
                    <p>✗ Missing extensions:</p>
                    <ul class="list-disc ml-6">
                        <?php foreach ($missingExtensions as $ext): ?>
                            <li><?= $ext ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <!-- Directory Permissions -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">Directory Permissions</h3>
            <?php if (empty($directoryIssues)): ?>
                <p class="text-green-600">✓ All directories are writable</p>
            <?php else: ?>
                <div class="text-red-600">
                    <p>✗ Directory issues:</p>
                    <ul class="list-disc ml-6">
                        <?php foreach ($directoryIssues as $issue): ?>
                            <li><?= $issue ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div class="flex justify-between">
            <a href="?step=welcome" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition">
                Back
            </a>
            <?php if ($allGood): ?>
                <a href="?step=database" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition">
                    Continue
                </a>
            <?php else: ?>
                <button disabled class="bg-gray-300 text-gray-500 px-6 py-2 rounded cursor-not-allowed">
                    Fix Issues First
                </button>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
PHP;

        File::put($stepsPath.'/requirements.php', $requirementsContent);

        // Create database step
        $databaseContent = <<<'PHP'
<?php
if (!defined('INSTALLER_ACCESS')) exit('Direct access not permitted');

$error = '';
$success = '';

if ($_POST) {
    $config = [
        'driver' => $_POST['db_driver'] ?? 'mysql',
        'host' => $_POST['db_host'] ?? 'localhost',
        'port' => $_POST['db_port'] ?? '3306',
        'database' => $_POST['db_name'] ?? '',
        'username' => $_POST['db_username'] ?? '',
        'password' => $_POST['db_password'] ?? '',
    ];

    $test = testDatabaseConnection($config);
    if ($test['success']) {
        $_SESSION['db_config'] = $config;
        $_SESSION['app_url'] = $_POST['app_url'] ?? 'http://localhost';
        $success = 'Database connection successful!';
    } else {
        $error = 'Database connection failed: ' . $test['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> Installer - Database</title>
    <link href="https://cdn.tailwindcss.com/3.3.0" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto max-w-2xl bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-2xl font-bold mb-6">Database Configuration</h1>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Application URL</label>
                <input type="url" name="app_url" value="<?= $_POST['app_url'] ?? 'http://localhost' ?>"
                       class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Database Type</label>
                <select name="db_driver" class="w-full border border-gray-300 rounded px-3 py-2">
                    <option value="mysql" <?= ($_POST['db_driver'] ?? 'mysql') === 'mysql' ? 'selected' : '' ?>>MySQL</option>
                    <option value="sqlite" <?= ($_POST['db_driver'] ?? '') === 'sqlite' ? 'selected' : '' ?>>SQLite</option>
                    <option value="pgsql" <?= ($_POST['db_driver'] ?? '') === 'pgsql' ? 'selected' : '' ?>>PostgreSQL</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Database Host</label>
                <input type="text" name="db_host" value="<?= $_POST['db_host'] ?? 'localhost' ?>"
                       class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Database Port</label>
                <input type="text" name="db_port" value="<?= $_POST['db_port'] ?? '3306' ?>"
                       class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
                <input type="text" name="db_name" value="<?= $_POST['db_name'] ?? '' ?>"
                       class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Database Username</label>
                <input type="text" name="db_username" value="<?= $_POST['db_username'] ?? '' ?>"
                       class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Database Password</label>
                <input type="password" name="db_password" value="<?= $_POST['db_password'] ?? '' ?>"
                       class="w-full border border-gray-300 rounded px-3 py-2">
            </div>

            <div class="flex justify-between pt-4">
                <a href="?step=requirements" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition">
                    Back
                </a>
                <div class="space-x-2">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition">
                        Test Connection
                    </button>
                    <?php if ($success): ?>
                        <a href="?step=install" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600 transition">
                            Install
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
PHP;

        File::put($stepsPath.'/database.php', $databaseContent);

        // Create install step
        $installContent = <<<'PHP'
<?php
if (!defined('INSTALLER_ACCESS')) exit('Direct access not permitted');

if (!isset($_SESSION['db_config'])) {
    header('Location: ?step=database');
    exit;
}

$installing = $_POST['install'] ?? false;
$output = [];
$success = false;

if ($installing) {
    // Generate app key and create .env file
    $appKey = generateAppKey();
    $envConfig = array_merge($_SESSION['db_config'], [
        'app_key' => $appKey,
        'app_url' => $_SESSION['app_url'],
        'db_driver' => $_SESSION['db_config']['driver'],
        'db_host' => $_SESSION['db_config']['host'],
        'db_port' => $_SESSION['db_config']['port'],
        'db_name' => $_SESSION['db_config']['database'],
        'db_username' => $_SESSION['db_config']['username'],
        'db_password' => $_SESSION['db_config']['password'],
    ]);

    if (createEnvFile($envConfig)) {
        $output[] = ['command' => 'Create .env file', 'output' => 'Success'];

        // Run artisan commands
        $artisanOutput = runArtisanCommands();
        $output = array_merge($output, $artisanOutput);

        $success = true;
        $_SESSION['installation_complete'] = true;
    } else {
        $output[] = ['command' => 'Create .env file', 'output' => 'Failed to create .env file'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> Installer - Installation</title>
    <link href="https://cdn.tailwindcss.com/3.3.0" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen py-8">
    <div class="container mx-auto max-w-4xl bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-2xl font-bold mb-6">Installation</h1>

        <?php if (!$installing): ?>
            <div class="mb-6">
                <p class="text-gray-700 mb-4">
                    Ready to install <?= APP_NAME ?>. This process will:
                </p>
                <ul class="list-disc ml-6 text-gray-700 space-y-1">
                    <li>Create configuration files</li>
                    <li>Set up the database schema</li>
                    <li>Create default admin user</li>
                    <li>Configure the application</li>
                </ul>
            </div>

            <form method="post">
                <div class="flex justify-between">
                    <a href="?step=database" class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600 transition">
                        Back
                    </a>
                    <button type="submit" name="install" value="1" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600 transition">
                        Install Now
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">Installation Progress</h2>

                <?php foreach ($output as $item): ?>
                    <div class="mb-4 p-4 bg-gray-50 rounded">
                        <div class="font-medium text-gray-800"><?= htmlspecialchars($item['command']) ?></div>
                        <pre class="text-sm text-gray-600 mt-2 whitespace-pre-wrap"><?= htmlspecialchars($item['output']) ?></pre>
                    </div>
                <?php endforeach; ?>

                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        ✓ Installation completed successfully!
                    </div>

                    <div class="text-center">
                        <a href="?step=complete" class="bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition">
                            Complete Setup
                        </a>
                    </div>
                <?php else: ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        ✗ Installation failed. Please check the errors above.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
PHP;

        File::put($stepsPath.'/install.php', $installContent);

        // Create complete step
        $completeContent = <<<'PHP'
<?php
if (!defined('INSTALLER_ACCESS')) exit('Direct access not permitted');

if (!isset($_SESSION['installation_complete'])) {
    header('Location: ?step=welcome');
    exit;
}

// Clean up installer on completion
$cleanup = $_GET['cleanup'] ?? false;
if ($cleanup) {
    cleanupInstaller();
    header('Location: ../');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> Installer - Complete</title>
    <link href="https://cdn.tailwindcss.com/3.3.0" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center">
        <div class="mb-6">
            <div class="mx-auto w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                <span class="text-green-500 text-2xl">✓</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Installation Complete!</h1>
            <p class="text-gray-600"><?= APP_NAME ?> has been successfully installed.</p>
        </div>

        <div class="mb-6 text-left bg-gray-50 p-4 rounded">
            <h3 class="font-semibold mb-2">Default Admin Login:</h3>
            <p><strong>Email:</strong> admin@admin.com</p>
            <p><strong>Password:</strong> password</p>
            <p class="text-sm text-orange-600 mt-2">⚠️ Please change these credentials after logging in!</p>
        </div>

        <div class="space-y-3">
            <a href="../" class="block bg-blue-500 text-white px-6 py-2 rounded hover:bg-blue-600 transition">
                Go to Application
            </a>
            <a href="?cleanup=1" class="block bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600 transition">
                Cleanup & Go to Application
            </a>
            <p class="text-xs text-gray-500">Cleanup will remove this installer for security.</p>
        </div>
    </div>
</body>
</html>
PHP;

        File::put($stepsPath.'/complete.php', $completeContent);
    }

    private function createPackageZip(string $packagePath): void
    {
        $zipFile = $packagePath.'/package.zip';

        if (File::exists($zipFile)) {
            File::delete($zipFile);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $this->addDirectoryToZip($zip, $packagePath, '', ['package.zip']);
            $zip->close();
        }
    }

    private function addDirectoryToZip(ZipArchive $zip, string $dir, string $zipDir = '', array $exclude = []): void
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
            $fileName = basename($filePath);

            // Skip excluded files
            if (in_array($fileName, $exclude)) {
                continue;
            }

            // Calculate relative path from base directory
            $relativePath = str_replace($realDir, '', $filePath);
            $relativePath = ltrim($relativePath, DIRECTORY_SEPARATOR);
            $relativePath = str_replace('\\', '/', $relativePath);

            // Create zip path
            $zipPath = $zipDir && $relativePath ? $zipDir.'/'.$relativePath : $relativePath;

            if ($file->isDir()) {
                if ($zipPath) { // Don't add empty root directory
                    $zip->addEmptyDir($zipPath);
                }
            } elseif ($file->isFile()) {
                $zip->addFile($filePath, $zipPath);
            }
        }
    }
}
