<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            $table->foreignId('source_id')->constrained('sources')->restrictOnDelete();
            $table->foreignId('status_id')->constrained('statuses')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('budget', 12, 2);
            $table->decimal('commission', 12, 2)->default(0.00);
            $table->decimal('net_income', 12, 2)->storedAs('budget - commission');

            $table->dateTime('deadline');
            $table->dateTime('payment_deadline')->nullable();
            $table->timestamps();
        });

        if ($driver === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_budget_non_negative CHECK (budget >= 0)');
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_commission_non_negative CHECK (commission >= 0)');
        DB::statement('ALTER TABLE orders ADD CONSTRAINT orders_commission_lte_budget CHECK (commission <= budget)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
