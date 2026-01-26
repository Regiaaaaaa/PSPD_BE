<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();

            $table->text('kepentingan')->nullable();
            $table->text('pesan_ditolak')->nullable();

            $table->date('tgl_pinjam')->nullable();
            $table->date('tgl_deadline');
            $table->date('tgl_kembali')->nullable();

            $table->enum('status', ['menunggu', 'dipinjam', 'kembali', 'ditolak'])
                ->default('menunggu');

            $table->foreignId('disetujui_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('diterima_oleh')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};
