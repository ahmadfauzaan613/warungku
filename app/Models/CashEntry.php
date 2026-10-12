<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashEntry extends Model
{
    use HasFactory;

    public const IN = 'in';
    public const OUT = 'out';

    protected $fillable = ['type', 'amount', 'note', 'sale_id', 'is_restock', 'occurred_at'];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_restock' => 'boolean',
        'occurred_at' => 'datetime',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function scopeBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('occurred_at', [$from, $to]);
    }

    /** Pengeluaran yang belum terhitung sebagai modal barang terjual. */
    public function scopeOperational(Builder $query): Builder
    {
        return $query->where('type', self::OUT)->where('is_restock', false);
    }

    public function isLocked(): bool
    {
        return $this->sale_id !== null;
    }

    public function isIncome(): bool
    {
        return $this->type === self::IN;
    }

    public function label(): string
    {
        return $this->isIncome() ? 'Pemasukan' : 'Pengeluaran';
    }
}
