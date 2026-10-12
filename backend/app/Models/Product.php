<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'source_url',
        'price',
        'category',
        'stock',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        // decimal:N serializa como string no JSON — o front aplica Number()
        // (BDR-003). stock sai int (unsignedInteger).
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    /**
     * @return HasMany<ProductLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(ProductLog::class);
    }
}
