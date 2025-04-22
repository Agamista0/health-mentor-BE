<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private $relationships = [
        'body_status_details' => [
            'parent' => 'body_statuses',
            'key' => 'body_status_id'
        ],
        // Add other nested relationships here
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update nested relationships to cascade delete
        foreach ($this->relationships as $table => $info) {
            if (Schema::hasTable($table)) {
                $constraint = DB::select("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = ?
                    AND COLUMN_NAME = ?
                    LIMIT 1
                ", [$table, $info['key']]);

                if (!empty($constraint)) {
                    $constraintName = $constraint[0]->CONSTRAINT_NAME;
                    
                    // Drop the existing foreign key
                    DB::statement("
                        ALTER TABLE `{$table}` 
                        DROP FOREIGN KEY `{$constraintName}`
                    ");

                    // Add new foreign key with cascade delete
                    DB::statement("
                        ALTER TABLE `{$table}` 
                        ADD CONSTRAINT `{$constraintName}` 
                        FOREIGN KEY (`{$info['key']}`) 
                        REFERENCES `{$info['parent']}` (`id`) 
                        ON DELETE CASCADE
                    ");
                }
            }
        }

        // Now handle the main user relationships
        $tables = [
            'answer_users',
            'avatars',
            'body_statuses',
            'subscription_users',
            'laboratory_files',
            'user_examinations',
            'user_topics'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                // Get the constraint name
                $constraint = DB::select("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = ?
                    AND REFERENCED_TABLE_NAME = 'users'
                    AND REFERENCED_COLUMN_NAME = 'id'
                    LIMIT 1
                ", [$table]);

                if (!empty($constraint)) {
                    $constraintName = $constraint[0]->CONSTRAINT_NAME;

                    // Drop the existing foreign key
                    DB::statement("
                        ALTER TABLE `{$table}` 
                        DROP FOREIGN KEY `{$constraintName}`
                    ");

                    // Add the new foreign key with cascade delete
                    DB::statement("
                        ALTER TABLE `{$table}` 
                        ADD CONSTRAINT `{$constraintName}` 
                        FOREIGN KEY (`user_id`) 
                        REFERENCES `users` (`id`) 
                        ON DELETE CASCADE
                    ");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, revert nested relationships
        foreach ($this->relationships as $table => $info) {
            if (Schema::hasTable($table)) {
                $constraint = DB::select("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = ?
                    AND COLUMN_NAME = ?
                    LIMIT 1
                ", [$table, $info['key']]);

                if (!empty($constraint)) {
                    $constraintName = $constraint[0]->CONSTRAINT_NAME;
                    
                    // Drop the existing foreign key
                    DB::statement("
                        ALTER TABLE `{$table}` 
                        DROP FOREIGN KEY `{$constraintName}`
                    ");

                    // Add new foreign key without cascade delete
                    DB::statement("
                        ALTER TABLE `{$table}` 
                        ADD CONSTRAINT `{$constraintName}` 
                        FOREIGN KEY (`{$info['key']}`) 
                        REFERENCES `{$info['parent']}` (`id`)
                    ");
                }
            }
        }

        // Now revert the main user relationships
        $tables = [
            'answer_users',
            'avatars',
            'body_statuses',
            'subscription_users',
            'laboratory_files',
            'user_examinations',
            'user_topics'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $constraint = DB::select("
                    SELECT CONSTRAINT_NAME
                    FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = ?
                    AND REFERENCED_TABLE_NAME = 'users'
                    AND REFERENCED_COLUMN_NAME = 'id'
                    LIMIT 1
                ", [$table]);

                if (!empty($constraint)) {
                    $constraintName = $constraint[0]->CONSTRAINT_NAME;

                    // Drop the existing foreign key
                    DB::statement("
                        ALTER TABLE `{$table}` 
                        DROP FOREIGN KEY `{$constraintName}`
                    ");

                    // Add the new foreign key without cascade delete
                    DB::statement("
                        ALTER TABLE `{$table}` 
                        ADD CONSTRAINT `{$constraintName}` 
                        FOREIGN KEY (`user_id`) 
                        REFERENCES `users` (`id`)
                    ");
                }
            }
        }
    }
}; 