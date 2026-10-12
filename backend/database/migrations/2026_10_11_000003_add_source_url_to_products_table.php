<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chave natural da importação (BDR-009): link do detalhe no site.
        // Nullable para não tocar nos seeds; unique garante o upsert.
        // MySQL permite vários NULL — seeds nunca colidem.
        // 512 (não 2048): índice unique utf8mb4 estoura acima de 3072 bytes;
        // link de detalhe real tem < 100 chars.
        Schema::table('products', function (Blueprint $table): void {
            $table->string('source_url', 512)->nullable()->unique()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['source_url']);
            $table->dropColumn('source_url');
        });
    }
};
