<?php

namespace App\Models;

use Awcodes\Curator\Models\Media;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_has_categories');
    }

    public function productPictures(): BelongsToMany
    {
        return $this
            ->belongsToMany(Media::class, 'media_product', 'product_id', 'media_id')
            ->withPivot('order')
            ->orderBy('order');;
    }
}
