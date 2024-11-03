<?php

namespace App\Models;

use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_has_categories');
    }
}
