<?php

use App\EntryLineDirection;
use App\Models\JournalEntry;
use App\Models\LedgerAccount;
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
        Schema::create('entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(JournalEntry::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(LedgerAccount::class)->constrained()->cascadeOnDelete();

            $table->integer('amount');
            $table->string('direction')->default(EntryLineDirection::DEBIT->value);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_lines');
    }
};
