<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'total', 'sold_at'];

    protected $casts = [
        'total' => 'decimal:2',
        'sold_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        return $query->when(filled($term), fn (Builder $q) => $q->where(fn (Builder $w) => $w
            ->whereLike('code', '%'.$term.'%')
            ->orWhereHas('items.product', fn (Builder $p) => $p->whereLike('name', '%'.$term.'%'))));
    }

    public function scopeBetween(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('sold_at', [$from, $to]);
    }
}
