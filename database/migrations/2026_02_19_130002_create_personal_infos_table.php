<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_infos', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('job_title');
            $table->text('bio')->nullable();
            $table->string('profile_image')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('cv_url')->nullable();
            $table->integer('years_experience')->default(0);
            $table->integer('completed_projects')->default(0);
            $table->integer('satisfied_customers')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_infos');
    }
};
