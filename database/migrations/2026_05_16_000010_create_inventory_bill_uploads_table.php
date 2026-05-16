<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_bill_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_name')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('gst_number')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('file_type');         // image or pdf
            $table->string('file_hash')->nullable();
            $table->text('extracted_json')->nullable();
            $table->string('ocr_provider')->nullable();
            $table->string('status')->default('uploaded');
            $table->text('notes')->nullable();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'uploaded_by']);
            $table->index('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_bill_uploads');
    }
};
