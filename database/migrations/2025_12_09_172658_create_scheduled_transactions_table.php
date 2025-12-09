<?php

use App\Enums\ScheduledTransactionFrequency;
use App\Enums\ScheduledTransactionStatus;
use App\Enums\ScheduledTransactionType;
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
        Schema::create('scheduled_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
            $table->foreignId('to_account_id')->nullable()->constrained('accounts')->cascadeOnDelete();
            $table->enum('type' , [ScheduledTransactionType::convertEnumToArray()]);
            $table->decimal('amount', 15, 2);
            $table->enum('frequency' , [ScheduledTransactionFrequency::convertEnumToArray()])->default(ScheduledTransactionFrequency::MONTHLY->value);
            $table->timestamp('next_run_at');
            $table->timestamp('end_at')->nullable();
            $table->enum('status' , [ScheduledTransactionStatus::convertEnumToArray()])->default(ScheduledTransactionStatus::ACTIVE->value);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_transactions');
    }
};
