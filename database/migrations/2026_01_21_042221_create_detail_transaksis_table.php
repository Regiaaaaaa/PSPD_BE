<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi')->cascadeOnDelete();
            $table->foreignId('buku_id')->constrained('buku')->cascadeOnDelete();

            $table->enum('status', [
                'menunggu',
                'dipinjam',
                'ditolak',
                'dibatalkan',
                'kembali_normal',
                'kembali_rusak_ringan',
                'kembali_rusak_sedang',
                'kembali_rusak_berat',
                'hilang',
            ])->default('menunggu');

            $table->decimal('denda_telat', 12, 2)->default(0);
            $table->decimal('denda_kerusakan', 12, 2)->default(0);
            $table->decimal('denda_hilang', 12, 2)->default(0);
            $table->decimal('total_denda_item', 12, 2)->default(0);
            $table->date('tgl_kembali')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};