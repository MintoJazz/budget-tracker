<?php

use App\EntryLineDirection;
use App\LedgerAccountType;
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
        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->nullable();
            $table->string('currency')->default('BRL');
            $table->string('type')->default(LedgerAccountType::BALANCE->value);
            $table->string('normal_balance')->default(EntryLineDirection::DEBIT->value);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledger_accounts');
    }
};
