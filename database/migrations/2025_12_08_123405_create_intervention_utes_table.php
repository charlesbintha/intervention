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
        Schema::create('intervention_utes', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('location');
            $table->string('contact_name');
            $table->string('contact_function');
            $table->string('contact_phone');
            $table->string('contact_email');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->text('purpose');
            $table->enum('diagnostic', ['cablage', 'wifi', 'FAI', 'electricite', 'autre']);
            $table->enum('type', ['changement_piece', 'entretien', 'depannage', 'autre']);
            $table->text('observations')->nullable();
            $table->enum('status', ['draft', 'pending', 'validated'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intervention_utes');
    }
};
