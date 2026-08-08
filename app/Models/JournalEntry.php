<?php

namespace App\Models;

use App\JournalEntryStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JournalEntry extends Model
{
    protected $fillable = [
        'date',
        'description',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'status' => JournalEntryStatus::class,
        ];
    }

    /**
     * @return HasMany<EntryLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(EntryLine::class);
    }
}
