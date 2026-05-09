<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lms_material_reads')) {
            return;
        }

        $materialId = $this->idColumnDefinition('lms_materials');
        $userId = $this->idColumnDefinition('users');

        Schema::create('lms_material_reads', function (Blueprint $table) use ($materialId, $userId) {
            $table->engine = 'InnoDB';
            $table->id();
            $this->addFkColumn($table, 'lms_material_id', $materialId);
            $this->addFkColumn($table, 'user_id', $userId);
            $table->dateTime('read_at')->nullable();
            $table->timestamps();

            $table->unique(['lms_material_id', 'user_id']);
            $table->index(['user_id', 'read_at']);
        });

        $this->ensureInnoDb('users');
        $this->ensureInnoDb('lms_materials');

        $this->addForeignKeysIfPossible();
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_material_reads');
    }

    private function idColumnDefinition(string $tableName): array
    {
        $row = DB::selectOne(
            "select DATA_TYPE, COLUMN_TYPE
             from information_schema.COLUMNS
             where TABLE_SCHEMA = database()
               and TABLE_NAME = ?
               and COLUMN_NAME = 'id'
             limit 1",
            [$tableName]
        );

        $dataType = strtolower((string) ($row->DATA_TYPE ?? 'bigint'));
        $columnType = strtolower((string) ($row->COLUMN_TYPE ?? 'bigint unsigned'));

        return [
            'big' => str_contains($dataType, 'bigint') || str_contains($columnType, 'bigint'),
            'unsigned' => str_contains($columnType, 'unsigned'),
        ];
    }

    private function addFkColumn(Blueprint $table, string $columnName, array $idDefinition, bool $nullable = false): void
    {
        $column = match (true) {
            $idDefinition['big'] && $idDefinition['unsigned'] => $table->unsignedBigInteger($columnName),
            $idDefinition['big'] => $table->bigInteger($columnName),
            $idDefinition['unsigned'] => $table->unsignedInteger($columnName),
            default => $table->integer($columnName),
        };

        if ($nullable) {
            $column->nullable();
        }
    }

    private function tableEngine(string $tableName): ?string
    {
        $row = DB::selectOne(
            "select ENGINE
             from information_schema.TABLES
             where TABLE_SCHEMA = database()
               and TABLE_NAME = ?
             limit 1",
            [$tableName]
        );

        return $row?->ENGINE;
    }

    private function ensureInnoDb(string $tableName): void
    {
        try {
            $engine = $this->tableEngine($tableName);
            if ($engine === null) {
                return;
            }

            if (strtolower((string) $engine) === 'innodb') {
                return;
            }

            DB::statement("alter table `$tableName` engine=InnoDB");
        } catch (\Throwable $e) {
        }
    }

    private function addForeignKeysIfPossible(): void
    {
        try {
            Schema::table('lms_material_reads', function (Blueprint $table) {
                $table->foreign('lms_material_id')->references('id')->on('lms_materials')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Throwable $e) {
        }
    }
};
