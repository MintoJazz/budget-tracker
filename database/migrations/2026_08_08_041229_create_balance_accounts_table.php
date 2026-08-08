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
        Schema::create('balance_accounts', function (Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('ledger_accounts')->cascadeOnDelete();

            $table->string('institution')->nullable()->comment('Bank or institution name');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('balance_accounts');
    }
};
