<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->decimal('quote_price', 10, 2)->nullable()->after('status');
            $table->text('quote_message')->nullable()->after('quote_price');
            $table->string('quote_status')->default('awaiting_admin')->after('quote_message');
        });
    }

    public function down()
    {
        Schema::table('repair_requests', function (Blueprint $table) {
            $table->dropColumn(['quote_price', 'quote_message', 'quote_status']);
        });
    }
};
