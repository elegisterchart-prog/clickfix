<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            DB::statement(
                'CREATE TABLE repair_requests_temp (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NULL,
                    name TEXT NULL,
                    email TEXT NULL,
                    phone TEXT NULL,
                    device_type TEXT NULL,
                    device_model TEXT NULL,
                    serial_number TEXT NULL,
                    problem_description TEXT NULL,
                    details TEXT NULL,
                    address TEXT NULL,
                    preferred_contact_time TEXT NULL,
                    priority TEXT NOT NULL DEFAULT "normal",
                    status TEXT NOT NULL DEFAULT "open",
                    assigned_to TEXT NULL,
                    attachments TEXT NULL,
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL,
                    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
                )'
            );

            DB::statement(
                'INSERT INTO repair_requests_temp (id, user_id, name, email, phone, device_type, device_model, serial_number, problem_description, details, address, preferred_contact_time, priority, status, assigned_to, attachments, created_at, updated_at)
                 SELECT id, user_id, name, email, phone, device_type, device_model, serial_number, problem_description, details, address, preferred_contact_time, priority, status, assigned_to, attachments, created_at, updated_at
                 FROM repair_requests'
            );

            DB::statement('DROP TABLE repair_requests;');
            DB::statement('ALTER TABLE repair_requests_temp RENAME TO repair_requests;');
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('ALTER TABLE repair_requests MODIFY assigned_to VARCHAR(255) NULL');
        }
    }

    public function down()
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
            DB::statement(
                'CREATE TABLE repair_requests_temp (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_id INTEGER NULL,
                    name TEXT NULL,
                    email TEXT NULL,
                    phone TEXT NULL,
                    device_type TEXT NULL,
                    device_model TEXT NULL,
                    serial_number TEXT NULL,
                    problem_description TEXT NULL,
                    details TEXT NULL,
                    address TEXT NULL,
                    preferred_contact_time TEXT NULL,
                    priority TEXT NOT NULL DEFAULT "normal",
                    status TEXT NOT NULL DEFAULT "open",
                    assigned_to INTEGER NULL,
                    attachments TEXT NULL,
                    created_at DATETIME NULL,
                    updated_at DATETIME NULL,
                    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
                )'
            );

            DB::statement(
                'INSERT INTO repair_requests_temp (id, user_id, name, email, phone, device_type, device_model, serial_number, problem_description, details, address, preferred_contact_time, priority, status, assigned_to, attachments, created_at, updated_at)
                 SELECT id, user_id, name, email, phone, device_type, device_model, serial_number, problem_description, details, address, preferred_contact_time, priority, status, assigned_to, attachments, created_at, updated_at
                 FROM repair_requests'
            );

            DB::statement('DROP TABLE repair_requests;');
            DB::statement('ALTER TABLE repair_requests_temp RENAME TO repair_requests;');
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('ALTER TABLE repair_requests MODIFY assigned_to BIGINT UNSIGNED NULL');
        }
    }
};
