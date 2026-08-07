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

    protected $casts = [
        'date' => 'date',
        'status' => JournalEntryStatus::class,
    ];

    public function lines(): HasMany {
        return $this->hasMany(EntryLine::class);
    }
}
