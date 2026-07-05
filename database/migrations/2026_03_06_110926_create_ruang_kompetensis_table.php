<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    


    public function up(): void
    {
        Schema::connection('mysql_apps')->create('ruang_kompetensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guru_id');
            $table->string('judul');
            $table->string('link_evaluasi');
            $table->integer('waktu_menit');
            $table->enum('status', ['aktif', 'arsip'])->default('aktif');
            $table->timestamps();
        });
    }

    


    public function down(): void
    {
        Schema::connection('mysql_apps')->dropIfExists('ruang_kompetensi');
    }
};
