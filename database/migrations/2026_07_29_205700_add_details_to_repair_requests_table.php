<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('repair_requests') && ! Schema::hasColumn('repair_requests', 'details')) {
            Schema::table('repair_requests', function (Blueprint $table) {
                $table->text('details')->nullable()->after('problem_description');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('repair_requests') && Schema::hasColumn('repair_requests', 'details')) {
            Schema::table('repair_requests', function (Blueprint $table) {
                $table->dropColumn('details');
            });
        }
    }
};
