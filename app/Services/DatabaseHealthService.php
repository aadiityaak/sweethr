<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class DatabaseHealthService
{
    /**
     * Check if database connection is healthy
     */
    public static function isHealthy(): bool
    {
        try {
            DB::select('SELECT 1');
            return true;
        } catch (QueryException $e) {
            Log::warning('Database health check failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return false;
        }
    }

    /**
     * Check if specific table exists and is accessible
     */
    public static function tableExists(string $table): bool
    {
        try {
            DB::select("SHOW TABLES LIKE '{$table}'");
            return true;
        } catch (QueryException $e) {
            Log::warning("Table health check failed for {$table}", [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            return false;
        }
    }

    /**
     * Attempt to reconnect to database
     */
    public static function reconnect(): bool
    {
        try {
            DB::reconnect();
            return self::isHealthy();
        } catch (\Exception $e) {
            Log::error('Database reconnection failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get database connection status info
     */
    public static function getConnectionInfo(): array
    {
        try {
            $result = DB::select('SELECT CONNECTION_ID() as connection_id, NOW() as server_time');
            return [
                'status' => 'healthy',
                'connection_id' => $result[0]->connection_id ?? null,
                'server_time' => $result[0]->server_time ?? null,
            ];
        } catch (QueryException $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ];
        }
    }
}