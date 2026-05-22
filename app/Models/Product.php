<?php

namespace App\Models;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ["title", "description", "price", "stock", "rating"];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}
