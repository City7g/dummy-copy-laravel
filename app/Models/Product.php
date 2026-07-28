<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, Searchable;

    protected $fillable = ["title", "description", "price", "stock", "rating"];

    protected function makeAllSearchableUsing(Builder $query): Builder
    {
        return $query;
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, "commentable");
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    #[SearchUsingPrefix(["id"])]
    #[SearchUsingFullText(["title", "description"])]
    public function toSearchableArray(): array
    {
        return [
            "id" => $this->id,
            "title" => $this->title,
            "description" => $this->description,
        ];
    }
}
