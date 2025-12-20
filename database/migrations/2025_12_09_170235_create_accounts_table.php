<?php

use App\Enums\AccountStatus;
use App\Enums\AccountType;
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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('description');
            $table->string('account_number')->unique();
            $table->enum('type' , [AccountType::convertEnumToArray()])->default(AccountType::CHECKING->value);
            $table->enum('status' , [AccountStatus::convertEnumToArray()])->default(AccountStatus::ACTIVE->value);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('user_id', 'accounts_user_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
