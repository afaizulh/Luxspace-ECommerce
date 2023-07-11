<?php

namespace App\Models;

use App\Models\ProductsGallery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Products extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'price', 'description', 'qty', 'slug'
    ];

    /**
     * Get all of the comments for the Products
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function gallery(): HasMany
    {
        return $this->hasMany(ProductsGallery::class, 'product_id', 'id');
    }

    /**
     * Get the user associated with the Products
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function carts(): HasOne
    {
        return $this->hasOne(Carts::class, 'product_id', 'id');
    }

    /**
     * Get the transaction_item that owns the Products
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction_items(): BelongsTo
    {
        return $this->belongsTo(TransactionItems::class, 'id', 'product_id');
    }
}
