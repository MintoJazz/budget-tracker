<?php

namespace App\Models;

use App\EntryLineDirection;
use App\LedgerAccountType;
use App\ValueObjects\AccountCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LedgerAccount extends Model
{
    protected $fillable = [
        'code',
        'name',
        'type',
        'normal_balance',
    ];

    protected function casts(): array
    {
        return [
            'code' => AccountCode::class,
            'type' => LedgerAccountType::class,
            'normal_balance' => EntryLineDirection::class,
        ];
    }

    /**
     * @return HasMany<EntryLine, $this>
     */
    public function lines(): HasMany
    {
        return $this->hasMany(EntryLine::class);
    }

    public function getFullNameAttribute(): string
    {
        if ($this->code !== null) {
            return "{$this->code} - {$this->name}";
        }

        return $this->name;
    }
}
