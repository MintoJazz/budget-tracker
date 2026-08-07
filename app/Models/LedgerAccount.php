<?php

namespace App\Models;

use App\LedgerAccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'is_synthetic',
        'parent_id',
    ];

    protected $casts = [
        'is_synthetic' => 'boolean',
        'type' => LedgerAccountType::class,
    ];

    public function parent(): BelongsTo {
        return $this->belongsTo(LedgerAccount::class, 'parent_id');
    }

    public function children(): HasMany {
        return $this->hasMany(LedgerAccount::class, 'parent_id');
    }

    public function isTopLevel(): bool {
        return $this->parent_id === null;
    }

    public function getFullNameAttribute(): string {
        if ($this->isTopLevel()) {
            return "{$this->code} - {$this->name}";
        }

        return "{$this->parent->code}.{$this->code} - {$this->name}";
    }
}
