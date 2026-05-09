<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lms_performance_appraisals')) {
            return;
        }

        $userId = $this->idColumnDefinition('users');

        Schema::create('lms_performance_appraisals', function (Blueprint $table) use ($userId) {
            $table->engine = 'InnoDB';
            $table->id();
            $this->addFkColumn($table, 'user_id', $userId);
            $this->addFkColumn($table, 'evaluator_id', $userId, true);
            $table->date('evaluated_at');

            $table->unsignedTinyInteger('quality_work');
            $table->unsignedTinyInteger('quantity_work');
            $table->unsignedTinyInteger('task_knowledge');

            $table->unsignedTinyInteger('discipline');
            $table->unsignedTinyInteger('teamwork');
            $table->unsignedTinyInteger('communication');
            $table->unsignedTinyInteger('initiative');

            $table->unsignedTinyInteger('target_realization');
            $table->unsignedTinyInteger('time_management');

            $table->unsignedTinyInteger('attitude');
            $table->unsignedTinyInteger('adaptability');

            $table->unsignedTinyInteger('leadership_delegation')->nullable();
            $table->unsignedTinyInteger('leadership_development')->nullable();

            $table->text('feedback')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'evaluated_at']);
            $table->index(['evaluator_id', 'evaluated_at']);
        });

        $this->ensureInnoDb('users');

        $this->addForeignKeysIfPossible();
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_performance_appraisals');
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
            Schema::table('lms_performance_appraisals', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('evaluator_id')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Throwable $e) {
        }
    }
};
