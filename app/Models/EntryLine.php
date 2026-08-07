<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'ledger_account_id',
        'amount',
        'direction',
    ];

    public function journalEntry(): BelongsTo {
        return $this->belongsTo(JournalEntry::class);
    }

    public function ledgerAccount(): BelongsTo {
        return $this->belongsTo(LedgerAccount::class);
    }

    public function getAmountAttribute(string $value): float {
        return (float) $value;
    }
}
