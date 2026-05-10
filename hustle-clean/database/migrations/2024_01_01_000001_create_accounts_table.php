<?php
// ─────────────────────────────────────────────────────────────────────────────
// FILE: database/migrations/2024_01_01_000001_create_accounts_table.php
// ─────────────────────────────────────────────────────────────────────────────
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('broker')->nullable();
            $table->enum('type', ['live', 'demo', 'funded', 'paper'])->default('live');
            $table->string('currency', 3)->default('USD');
            $table->decimal('starting_balance', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('max_daily_loss', 12, 2)->nullable();
            $table->decimal('max_daily_loss_pct', 5, 2)->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void { Schema::dropIfExists('accounts'); }
};
