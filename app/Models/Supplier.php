<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'contactNO',
        'address',
        'is_active',
        'date_disabled',
        'disabled_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'date_disabled' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function disabledBy()
    {
        return $this->belongsTo(User::class, 'disabled_by_user_id')->withDefault([
            'full_name' => 'System'
        ]);
    }

    // Relationship with products (many-to-many through product_suppliers)
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_suppliers')
                    ->withPivot('default_unit_cost')
                    ->withTimestamps();
    }

    // Relationship with stock ins
    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    // Check if supplier has associated products
    public function hasProducts()
    {
        return $this->products()->exists();
    }

    // Check if supplier has associated stock ins
    public function hasStockIns()
    {
        return $this->stockIns()->exists();
    }

    // Check if supplier can be archived
    public function canBeArchived()
    {
        return true;
    }

    // Get recent stock ins count
    public function getRecentStockInsCountAttribute()
    {
        return $this->stockIns()
                    ->where('stock_in_date', '>=', now()->subDays(30))
                    ->count();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_active', false);
    }

    public function isDefaultSupplier()
    {
        return Product::where('default_supplier_id', $this->id)->exists();
    }
}