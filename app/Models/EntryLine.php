<?php

namespace App\Models;

use App\EntryLineDirection;
use App\ValueObjects\Money;
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

    protected function casts(): array
    {
        return [
            'amount' => Money::class,
            'direction' => EntryLineDirection::class,
        ];
    }

    /**
     * @return BelongsTo<JournalEntry, $this>
     */
    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    /**
     * @return BelongsTo<LedgerAccount, $this>
     */
    public function ledgerAccount(): BelongsTo
    {
        return $this->belongsTo(LedgerAccount::class);
    }
}
