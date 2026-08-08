<?php

use App\Models\ResultAccount;
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
        Schema::create('result_accounts', function (Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('ledger_accounts')->cascadeOnDelete();
            $table->foreignIdFor(ResultAccount::class)->constrained()->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_accounts');
    }
};
