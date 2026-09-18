<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('repair_updates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('repair_request_id');
            $table->unsignedBigInteger('user_id')->nullable(); // technician/admin who posted
            $table->string('status')->nullable(); // confirmed, in_progress, closed
            $table->text('message')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->index('repair_request_id');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('repair_updates');
    }
};
