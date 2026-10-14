<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = ['title', 'excerpt', 'body', 'category_id', 'status'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            $post->title = Str::squish($post->title);
            $post->title_key = Str::lower($post->title);
            if (! $post->exists) {
                $post->slug = (Str::slug($post->title) ?: 'noticia').'-'.Str::lower((string) Str::ulid());
            }
            $post->published_at = $post->status === 'published' ? ($post->published_at ?? now()) : null;
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished(Builder $query): void
    {
        $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
