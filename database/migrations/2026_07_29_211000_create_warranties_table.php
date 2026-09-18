<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->string('serial_number')->nullable();
            $table->dateTime('purchase_date')->nullable();
            $table->integer('warranty_months')->nullable();
            $table->dateTime('warranty_expires_at')->nullable();
            $table->string('warranty_type')->nullable();
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('product_sku');
        });
    }

    public function down()
    {
        Schema::dropIfExists('warranties');
    }
};
