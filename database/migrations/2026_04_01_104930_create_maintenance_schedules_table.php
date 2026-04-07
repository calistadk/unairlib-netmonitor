<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('maintenance_schedules')) {
            // Tabel belum ada → buat lengkap
            Schema::create('maintenance_schedules', function (Blueprint $table) {
                $table->id();
                $table->string('device_id');
                $table->string('device_name');
                $table->date('scheduled_date');
                $table->date('next_maintenance');
                $table->unsignedSmallInteger('interval_days')->default(3);
                $table->boolean('is_done')->default(false);
                $table->timestamp('done_at')->nullable();
                $table->foreignId('done_by')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        } else {
            // Tabel sudah ada → tambahkan kolom jika belum ada
            if (!Schema::hasColumn('maintenance_schedules', 'interval_days')) {
                Schema::table('maintenance_schedules', function (Blueprint $table) {
                    $table->unsignedSmallInteger('interval_days')->default(3)->after('next_maintenance');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('maintenance_schedules', 'interval_days')) {
            Schema::table('maintenance_schedules', function (Blueprint $table) {
                $table->dropColumn('interval_days');
            });
        }
    }
};