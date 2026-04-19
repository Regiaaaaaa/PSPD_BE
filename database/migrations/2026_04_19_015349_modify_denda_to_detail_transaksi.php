<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('denda', function (Blueprint $table) {

            $table->dropForeign(['transaksi_id']);
            $table->dropColumn('transaksi_id');
            $table->foreignId('detail_transaksi_id')
                ->after('id')
                ->constrained('detail_transaksi')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('denda', function (Blueprint $table) {

            $table->dropForeign(['detail_transaksi_id']);
            $table->dropColumn('detail_transaksi_id');

            $table->foreignId('transaksi_id')
                ->constrained('transaksi')
                ->cascadeOnDelete();
        });
    }
};