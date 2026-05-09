<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lms_assignment_submissions')) {
            return;
        }

        $assignmentId = $this->idColumnDefinition('lms_assignments');
        $userId = $this->idColumnDefinition('users');

        Schema::create('lms_assignment_submissions', function (Blueprint $table) use ($assignmentId, $userId) {
            $table->engine = 'InnoDB';
            $table->id();
            $this->addFkColumn($table, 'lms_assignment_id', $assignmentId);
            $this->addFkColumn($table, 'user_id', $userId);
            $table->dateTime('submitted_at')->nullable();
            $table->longText('content')->nullable();
            $table->string('attachment_path')->nullable();
            $table->unsignedInteger('score')->nullable();
            $table->text('feedback')->nullable();
            $table->dateTime('graded_at')->nullable();
            $this->addFkColumn($table, 'graded_by', $userId, true);
            $table->timestamps();

            $table->index(['lms_assignment_id', 'user_id']);
            $table->index(['lms_assignment_id', 'submitted_at']);
        });

        $this->ensureInnoDb('users');
        $this->ensureInnoDb('lms_assignments');

        $this->addForeignKeysIfPossible();
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_assignment_submissions');
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
            Schema::table('lms_assignment_submissions', function (Blueprint $table) {
                $table->foreign('lms_assignment_id')->references('id')->on('lms_assignments')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('graded_by')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Throwable $e) {
        }
    }
};
