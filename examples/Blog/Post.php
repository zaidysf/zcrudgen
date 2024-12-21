<?php

namespace Examples\Blog;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category_id',
        'author_id',
        'status',
        'published_at',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
