<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('broken_devices', function (Blueprint $table) {
            $table->id();
            $table->string('hostid')->index();        // dari Zabbix
            $table->string('host_name');              // nama device
            $table->string('ip')->nullable();
            $table->string('groups')->nullable();
            $table->text('reason');                   // alasan rusak
            $table->date('broken_date');              // tanggal rusak
            $table->foreignId('reported_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broken_devices');
    }
};
