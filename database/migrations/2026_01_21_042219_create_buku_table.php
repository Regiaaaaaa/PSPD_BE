<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('buku', function (Blueprint $table) {
            $table->id();
            $table->string('isbn', 13)->unique();
            $table->string('judul');
            $table->string('penulis')->nullable();
            $table->string('penerbit')->nullable();
            $table->year('tahun_terbit')->nullable();
            
           
            $table->integer('stok_total')->default(0);  
            $table->integer('stok_tersedia')->default(0); 
            $table->integer('dalam_perbaikan')->default(0);
            
            
            $table->decimal('harga_buku', 12, 2)->default(0);
            $table->decimal('persen_rusak_ringan', 6, 2)->default(10.00);
            $table->decimal('persen_rusak_sedang', 6, 2)->default(25.00);
            $table->decimal('persen_rusak_berat', 6, 2)->default(50.00);
            
            $table->string('cover')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buku');
    }
};
