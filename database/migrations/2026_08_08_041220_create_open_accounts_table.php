<?php

use App\OpenAccountStatus;
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
        Schema::create('open_accounts', function (Blueprint $table) {
            $table->foreignId('id')->primary()->constrained('ledger_accounts')->cascadeOnDelete();

            $table->date('due_date');
            $table->string('status')->default(OpenAccountStatus::OPEN->value);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('open_accounts');
    }
};
