<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    


    public function up(): void
    {
        Schema::connection('mysql_apps')->create('pengumuman', function (Blueprint $table) {
            $table->id();
            $table->integer('mapel_id');
            $table->integer('kelas_id');
            $table->string('judul', 255)->nullable();
            $table->text('isi')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });
    }

    


    public function down(): void
    {
        Schema::connection('mysql_apps')->dropIfExists('pengumuman');
    }
};
