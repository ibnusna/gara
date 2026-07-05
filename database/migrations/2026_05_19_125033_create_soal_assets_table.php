<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    


    protected $connection = 'mysql_asesmen';

    public function up(): void
    {
        Schema::create('soal_assets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('bank_soal_id');
            $table->enum('asset_type', ['local_image', 'external_image', 'youtube_link', 'audio_mp3', 'google_drive']);
            $table->text('asset_source');
            $table->string('original_name')->nullable();
            $table->timestamps();

            
            
            
            $table->index('bank_soal_id');
        });
    }

    


    public function down(): void
    {
        Schema::dropIfExists('soal_assets');
    }
};
