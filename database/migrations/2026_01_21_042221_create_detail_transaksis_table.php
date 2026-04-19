<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaksi_id')
                ->constrained('transaksi')
                ->cascadeOnDelete();

            $table->foreignId('buku_id')
                ->constrained('buku')
                ->cascadeOnDelete();

            $table->enum('status', [
                'menunggu',
                'dipinjam',
                'kembali',
                'ditolak',
                'dibatalkan'
            ])->default('menunggu');

            $table->date('tgl_kembali')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};