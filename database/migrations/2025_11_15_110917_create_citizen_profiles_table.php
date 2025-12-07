<?php

use App\Enums\ProfileCity;
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
        Schema::create('citizen_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users' , 'id')->onDelete('cascade');
            $table->string('phone' , 10)->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('national_number',11)->unique();
            $table->enum('city' , ProfileCity::convertEnumToArray())->nullable();
            $table->string('address',50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citizen_profiles');
    }
};
