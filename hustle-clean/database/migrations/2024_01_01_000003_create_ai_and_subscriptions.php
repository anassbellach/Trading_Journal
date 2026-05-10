<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ai_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['habit', 'performance', 'psychology', 'risk', 'summary']);
            $table->enum('category', ['revenge_trading', 'overtrading', 'best_edge', 'risk_alert', 'weekly_summary', 'pattern']);
            $table->string('title');
            $table->text('description');
            $table->json('data')->nullable();
            $table->json('action_items')->nullable();
            $table->enum('severity', ['info', 'warning', 'critical', 'positive'])->default('info');
            $table->boolean('is_read')->default(false);
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            $table->index(['user_id', 'is_read', 'generated_at']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_subscription_id')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_price_id')->nullable();
            $table->enum('plan', ['free', 'pro', 'premium'])->default('free');
            $table->enum('status', ['active', 'trialing', 'past_due', 'canceled', 'incomplete'])->default('active');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['pnl', 'win_rate', 'profit_factor', 'trades', 'rr', 'streak']);
            $table->decimal('target_value', 12, 4);
            $table->decimal('current_value', 12, 4)->default(0);
            $table->enum('period', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // Add subscription plan to users
        Schema::table('users', function (Blueprint $table) {
            $table->enum('subscription_plan', ['free', 'pro', 'premium'])->default('free')->after('email');
            $table->string('active_account_id')->nullable()->after('subscription_plan');
            $table->string('avatar')->nullable()->after('active_account_id');
            $table->string('google_id')->nullable()->after('avatar');
            $table->string('timezone', 50)->default('UTC')->after('google_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('goals');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('ai_insights');
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['subscription_plan', 'active_account_id', 'avatar', 'google_id', 'timezone']));
    }
};
