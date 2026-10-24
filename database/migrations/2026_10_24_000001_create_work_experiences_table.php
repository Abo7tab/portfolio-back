<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('position');
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('start_date');          // e.g. "Jan 2023"
            $table->string('end_date')->nullable(); // null = still working
            $table->boolean('is_current')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_experiences');
    }
};
