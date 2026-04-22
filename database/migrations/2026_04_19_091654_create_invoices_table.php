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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('invoice_type_id')->constrained()->onDelete('restrict');
            $table->string('generation_code')->unique();
            $table->string('correlative');
            $table->string('mh_stamp')->nullable();
            $table->date('issue_date');
            $table->enum('payment_method', ['cash', 'credit_card', 'bank_transfer', 'credit']);
            $table->enum('payment_status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->date('due_date')->nullable();
            $table->decimal('monto_exento', 10, 2)->default(0);
            $table->decimal('monto_gravado', 10, 2)->default(0);
            $table->decimal('monto_iva', 10, 2)->default(0);
            $table->decimal('iva_retenido', 10, 2)->default(0);
            $table->decimal('isr_retenido', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', ['draft', 'issued', 'cancelled'])->default('draft');
            $table->enum('status_mh', ['draft', 'received', 'rejected', 'cancelled'])->default('draft');
            $table->text('note')->nullable();
            $table->json('mh_request')->nullable();
            $table->json('mh_response')->nullable();
            $table->json('mh_cancellation_request')->nullable();
            $table->json('mh_cancellation_response')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->date('cancellation_date')->nullable();
            $table->string('cancellation_mh_stamp')->nullable();
            $table->string('cancellation_generation_code')->nullable();
            $table->timestamps();

            $table->index('customer_id');
            $table->index('issue_date');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
