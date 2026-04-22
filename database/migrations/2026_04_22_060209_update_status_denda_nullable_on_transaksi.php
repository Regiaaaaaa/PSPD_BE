<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->enum('status_denda', ['belum_bayar', 'lunas'])
                  ->nullable()
                  ->default(null)
                  ->change();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            $table->enum('status_denda', ['belum_bayar', 'lunas'])
                  ->default('belum_bayar')
                  ->nullable(false)
                  ->change();
        });
    }
};