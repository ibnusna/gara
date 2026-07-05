<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mysql_auth';

    public function up(): void
    {
        Schema::connection('mysql_auth')->create('guru_oauth_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('id_user')->primary();
            $table->string('google_email', 255)->nullable();
            $table->text('refresh_token')->nullable();
            $table->text('access_token')->nullable();
            $table->dateTime('token_expiry')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::connection('mysql_auth')->dropIfExists('guru_oauth_tokens');
    }
};
