<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('maintenance_schedules', function (Blueprint $table) {
        $table->id();
        $table->string('device_id');        // hostid dari Zabbix
        $table->string('device_name');      // nama host dari Zabbix
        $table->date('scheduled_date');     // tanggal jadwal maintenance
        $table->date('next_maintenance');   // jadwal berikutnya (+ 3 hari)
        $table->boolean('is_done')->default(false);
        $table->timestamp('done_at')->nullable();
        $table->foreignId('done_by')->nullable()->constrained('users')->nullOnDelete();
        $table->text('notes')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};
