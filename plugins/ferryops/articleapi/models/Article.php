<?php namespace Ferryops\ArticleAPI\Models;

use RainLab\Blog\Models\Post as BlogPost;
use RainLab\Blog\Models\Category;
use System\Models\File;
use Backend\Models\User; // Import User model

class Article extends BlogPost
{
    protected $hidden = ['api_token'];
    protected $appends = ['url', 'featured_images_data', 'author_name'];

    public function getUrlAttribute()
    {
        return url('blog/post/' . $this->slug);
    }

    // Tambahkan accessor untuk author_name
    public function getAuthorNameAttribute()
    {
        if ($this->user) {
            return $this->user->first_name . ' ' . $this->user->last_name;
        }
        return 'Unknown';
    }

    // Relasi ke user (author)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories()
    {
        return $this->belongsToMany(
            Category::class,
            'rainlab_blog_posts_categories',
            'post_id',
            'category_id'
        );
    }

    public function getFeaturedImagesDataAttribute()
    {
        $images = File::where('attachment_type', 'RainLab\Blog\Models\Post')
            ->where('attachment_id', $this->id)
            ->where('field', 'featured_images')
            ->orderBy('sort_order', 'asc')
            ->get();

        if ($images->isEmpty()) {
            return null;
        }

        return $images->map(function($image) {
            return [
                'id' => $image->id,
                'file_name' => $image->file_name,
                'disk_name' => $image->disk_name,
                'file_size' => $image->file_size,
                'content_type' => $image->content_type,
                'title' => $image->title,
                'description' => $image->description,
                'path' => $image->getPath(),
                'url' => $image->getPath(),
                'thumb' => $image->getThumb(400, 300, ['mode' => 'crop']),
            ];
        })->toArray();
    }

    protected $casts = [
        'published' => 'boolean',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'published_at' => 'datetime:Y-m-d H:i:s'
    ];
}
