<?php
// ─── strategies ───────────────────────────────────────────────────────────────
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('strategies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->default('#00C896');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color', 7)->default('#7B9FFF');
            $table->timestamps();
            $table->unique(['user_id', 'name']);
        });

        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('strategy_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ticker', 20);
            $table->enum('direction', ['long', 'short']);
            $table->enum('status', ['open', 'closed', 'cancelled'])->default('closed');
            $table->decimal('entry_price', 14, 5);
            $table->decimal('exit_price', 14, 5)->nullable();
            $table->decimal('stop_loss', 14, 5)->nullable();
            $table->decimal('take_profit', 14, 5)->nullable();
            $table->decimal('position_size', 10, 4)->default(1);
            $table->decimal('commission', 10, 2)->default(0);
            $table->decimal('risk_amount', 12, 2)->nullable();
            $table->decimal('risk_pct', 5, 2)->nullable();
            $table->decimal('pnl', 12, 2)->nullable();
            $table->decimal('pnl_pct', 8, 4)->nullable();
            $table->decimal('rr_ratio', 8, 4)->nullable();
            $table->boolean('is_win')->nullable();
            $table->enum('session', ['asian', 'london', 'new_york', 'overnight', 'pre_market'])->default('new_york');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->tinyInteger('psychology_rating')->nullable()->unsigned();
            $table->text('psychology_notes')->nullable();
            $table->json('mistakes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status', 'opened_at']);
            $table->index(['account_id', 'opened_at']);
            $table->index(['user_id', 'ticker']);
            $table->index(['user_id', 'strategy_id']);
        });

        Schema::create('tag_trade', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();
            $table->primary(['tag_id', 'trade_id']);
        });

        Schema::create('screenshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->enum('type', ['entry', 'exit', 'analysis'])->default('entry');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->tinyInteger('mood')->default(5);
            $table->enum('market_bias', ['bullish', 'bearish', 'neutral'])->default('neutral');
            $table->text('pre_session_notes')->nullable();
            $table->text('post_session_notes')->nullable();
            $table->text('lessons_learned')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('screenshots');
        Schema::dropIfExists('tag_trade');
        Schema::dropIfExists('trades');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('strategies');
    }
};
