<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete()
                  ->unique();

            $table->string('nomor_induk_siswa')->unique();
            $table->enum('tingkat', ['X', 'XI', 'XII']);
            $table->enum('jurusan', ['RPL', 'ANIMASI', 'TJKT', 'TE', 'PSPT']);
            $table->integer('kelas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
