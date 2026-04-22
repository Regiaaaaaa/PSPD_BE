<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('kepentingan')->nullable();
            $table->text('pesan_ditolak')->nullable();
            $table->text('pesan_diterima')->nullable();
            $table->date('tgl_pinjam')->nullable();
            $table->date('tgl_deadline');
            $table->decimal('total_denda', 12, 2)->default(0);
            $table->enum('status', [
                'menunggu',
                'dipinjam',
                'kembali',
                'ditolak',
                'dibatalkan'
            ])->default('menunggu');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('diterima_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ditolak_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status_denda', ['belum_bayar', 'lunas'])->default('belum_bayar');
            $table->foreignId('penerima_denda_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('tgl_lunas')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};