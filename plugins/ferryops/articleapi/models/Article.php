<?php namespace Ferryops\ArticleAPI\Models;

use RainLab\Blog\Models\Post as BlogPost;
use RainLab\Blog\Models\Category;

class Article extends BlogPost
{
    protected $hidden = ['api_token'];
    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return url('blog/post/' . $this->slug);
    }

    // Relasi ke kategori
    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'rainlab_blog_posts_categories',
            'post_id',
            'category_id'
        );
    }

    protected $casts = [
        'published' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'published_at' => 'datetime:Y-m-d H:i:s'
    ];
}