<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table(table: 'pessoa', callback: function (Blueprint $table): void {
            $table->string(column: 'stack', length: 32)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(table: 'pessoa', callback: function (Blueprint $table): void {
            $table->string(column: 'stack', length: 32)->change();
        });
    }
};
