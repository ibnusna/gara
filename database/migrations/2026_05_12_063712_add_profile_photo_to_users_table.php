<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    


    public function up(): void
    {
        Schema::connection('mysql_auth')->table('users', function (Blueprint $table) {
            $table->string('profile_photo')->nullable()->after('status_aktif');
        });
    }

    


    public function down(): void
    {
        Schema::connection('mysql_auth')->table('users', function (Blueprint $table) {
            $table->dropColumn('profile_photo');
        });
    }
};
