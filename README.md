# Article API - October CMS

RESTful API to display articles and categories from October CMS to mobile or web applications.

## 📋 Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [API Endpoints](#api-endpoints)
- [Usage Examples](#usage-examples)
- [Folder Structure](#folder-structure)
- [Database](#database)
- [Error Handling](#error-handling)
- [Contributing](#contributing)

## 📦 Requirements

- PHP 7.4 or higher
- October CMS 3.0 or higher
- MySQL 5.7 or higher
- Composer
- RainLab Blog Plugin (optional, for using built-in blog features)

## 🚀 Installation

### 1. Clone or Download Project

```bash
git clone https://github.com/ferryops/headless-cms-php.git
cd headless-cms-php
```

### 2. Install October CMS (If not already installed)

```bash
composer create-project october/october
cd october
php artisan october:install
```

### 3. Install Article API Plugin

Copy the plugin folder to the plugins directory:

```bash
cp -r headless-cms-php plugins/ferryops/articleapi
```

Or install via command:

```bash
php artisan plugin:install Ferryops.ArticleAPI
```

### 4. Install RainLab Blog Plugin (Optional)

```bash
php artisan plugin:install RainLab.Blog
```

### 5. Database Migration

```bash
php artisan migrate
```

## ⚙️ Configuration

### 1. Update Routes

Edit the file `plugins/ferryops/articleapi/routes.php`:

```php
Route::prefix('api/v1')->group(function () {
    Route::get('articles', 'Ferryops\ArticleAPI\Controllers\Articles@list');
    Route::get('articles/{id}', 'Ferryops\ArticleAPI\Controllers\Articles@show');
    Route::get('articles/search/{keyword}', 'Ferryops\ArticleAPI\Controllers\Articles@search');

    Route::get('categories', 'Ferryops\ArticleAPI\Controllers\Articles@categories');
    Route::get('categories/{id}/articles', 'Ferryops\ArticleAPI\Controllers\Articles@byCategory');
});
```

### 2. CORS Configuration (Optional)

To access the API from mobile applications across domains, add CORS middleware at `app/Http/Middleware/Cors.php`:

```php
<?php namespace App\Http\Middleware;

use Closure;

class Cors
{
    public function handle($request, Closure $next)
    {
        return $next($request)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
```

Then register it in `app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \App\Http\Middleware\Cors::class,
];
```

## 📡 API Endpoints

### 1. List Articles (Paginated)

**Endpoint:**
```
GET /api/v1/articles?page=1
```

**Parameters:**
- `page` (optional): Page number, default 1

**Response:**
```json
{
    "status": "success",
    "message": "Articles retrieved successfully",
    "data": [
        {
            "id": 1,
            "title": "Article Title",
            "content": "Article content...",
            "slug": "article-title",
            "published": true,
            "created_at": "2025-01-15 10:30:00",
            "updated_at": "2025-01-15 10:30:00",
            "categories": [
                {
                    "id": 1,
                    "name": "Technology",
                    "slug": "technology"
                }
            ]
        }
    ],
    "pagination": {
        "total": 50,
        "per_page": 10,
        "current_page": 1,
        "last_page": 5
    }
}
```

### 2. Get Article Detail

**Endpoint:**
```
GET /api/v1/articles/{id}
```

**Parameters:**
- `id` (required): Article ID

**Response:**
```json
{
    "status": "success",
    "message": "Article detail retrieved successfully",
    "data": {
        "id": 1,
        "title": "Article Title",
        "content": "Full article content...",
        "slug": "article-title",
        "published": true,
        "created_at": "2025-01-15 10:30:00",
        "categories": [
            {
                "id": 1,
                "name": "Technology",
                "slug": "technology"
            }
        ]
    }
}
```

### 3. Search Articles

**Endpoint:**
```
GET /api/v1/articles/search/{keyword}?page=1
```

**Parameters:**
- `keyword` (required): Search keyword
- `page` (optional): Page number

**Response:**
```json
{
    "status": "success",
    "message": "Search results retrieved successfully",
    "data": [
        {
            "id": 1,
            "title": "Article about Laravel",
            "content": "...",
            "categories": []
        }
    ],
    "pagination": {
        "total": 5,
        "per_page": 10
    }
}
```

### 4. List Categories

**Endpoint:**
```
GET /api/v1/categories
```

**Response:**
```json
{
    "status": "success",
    "message": "Categories retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Technology",
            "slug": "technology",
            "code": "",
            "description": "Articles about technology"
        },
        {
            "id": 2,
            "name": "Business",
            "slug": "business",
            "code": "",
            "description": "Articles about business"
        }
    ]
}
```

### 5. Get Articles by Category

**Endpoint:**
```
GET /api/v1/categories/{id}/articles?page=1
```

**Parameters:**
- `id` (required): Category ID
- `page` (optional): Page number

**Response:**
```json
{
    "status": "success",
    "message": "Articles in Technology category retrieved successfully",
    "category": {
        "id": 1,
        "name": "Technology",
        "slug": "technology"
    },
    "data": [
        {
            "id": 1,
            "title": "Technology Article",
            "content": "...",
            "categories": [
                {
                    "id": 1,
                    "name": "Technology",
                    "slug": "technology"
                }
            ]
        }
    ],
    "pagination": {
        "total": 15,
        "per_page": 10,
        "current_page": 1,
        "last_page": 2
    }
}
```

## 💻 Usage Examples

### Flutter

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class ArticleService {
  final String baseUrl = 'https://yoursite.com/api/v1';

  Future<List<Article>> getArticles({int page = 1}) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/articles?page=$page'),
        headers: {'Content-Type': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return (data['data'] as List)
            .map((article) => Article.fromJson(article))
            .toList();
      }
      throw Exception('Failed to load articles');
    } catch (e) {
      throw Exception('Error: $e');
    }
  }

  Future<Article> getArticleDetail(int id) async {
    final response = await http.get(
      Uri.parse('$baseUrl/articles/$id'),
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return Article.fromJson(data['data']);
    }
    throw Exception('Failed to load article detail');
  }

  Future<List<Article>> searchArticles(String keyword) async {
    final response = await http.get(
      Uri.parse('$baseUrl/articles/search/$keyword'),
    );

    if (response.statusCode == 200) {
      final data = json.decode(response.body);
      return (data['data'] as List)
          .map((article) => Article.fromJson(article))
          .toList();
    }
    throw Exception('Failed to search articles');
  }
}

class Article {
  final int id;
  final String title;
  final String content;
  final String slug;
  final bool published;
  final List<Category> categories;

  Article({
    required this.id,
    required this.title,
    required this.content,
    required this.slug,
    required this.published,
    required this.categories,
  });

  factory Article.fromJson(Map<String, dynamic> json) {
    return Article(
      id: json['id'],
      title: json['title'],
      content: json['content'],
      slug: json['slug'],
      published: json['published'],
      categories: (json['categories'] as List? ?? [])
          .map((cat) => Category.fromJson(cat))
          .toList(),
    );
  }
}

class Category {
  final int id;
  final String name;
  final String slug;

  Category({
    required this.id,
    required this.name,
    required this.slug,
  });

  factory Category.fromJson(Map<String, dynamic> json) {
    return Category(
      id: json['id'],
      name: json['name'],
      slug: json['slug'],
    );
  }
}
```

### React Native / JavaScript

```javascript
const API_URL = 'https://yoursite.com/api/v1';

export const articleService = {
  getArticles: async (page = 1) => {
    try {
      const response = await fetch(`${API_URL}/articles?page=${page}`);
      const data = await response.json();
      return data.data;
    } catch (error) {
      console.error('Error fetching articles:', error);
      throw error;
    }
  },

  getArticleDetail: async (id) => {
    try {
      const response = await fetch(`${API_URL}/articles/${id}`);
      const data = await response.json();
      return data.data;
    } catch (error) {
      console.error('Error fetching article detail:', error);
      throw error;
    }
  },

  searchArticles: async (keyword) => {
    try {
      const response = await fetch(`${API_URL}/articles/search/${keyword}`);
      const data = await response.json();
      return data.data;
    } catch (error) {
      console.error('Error searching articles:', error);
      throw error;
    }
  },

  getCategories: async () => {
    try {
      const response = await fetch(`${API_URL}/categories`);
      const data = await response.json();
      return data.data;
    } catch (error) {
      console.error('Error fetching categories:', error);
      throw error;
    }
  },

  getArticlesByCategory: async (categoryId, page = 1) => {
    try {
      const response = await fetch(`${API_URL}/categories/${categoryId}/articles?page=${page}`);
      const data = await response.json();
      return data.data;
    } catch (error) {
      console.error('Error fetching articles by category:', error);
      throw error;
    }
  }
};
```

## 📁 Folder Structure

```
plugins/
├── ferryops/
│   └── articleapi/
│       ├── controllers/
│       │   └── Articles.php          # Main API controller
│       ├── models/
│       │   ├── Article.php           # Article model
│       │   └── Category.php          # Category model
│       ├── Plugin.php                # Plugin class
│       ├── routes.php                # API routes
│       └── README.md                 # Plugin documentation
```

## 🗄️ Database

### Tables Used

1. **rainlab_blog_posts**
   - Main table for storing articles

2. **rainlab_blog_categories**
   - Table for storing article categories

3. **rainlab_blog_posts_categories**
   - Pivot table for many-to-many relationship between posts and categories

### Table Structure

```sql
-- rainlab_blog_posts_categories
CREATE TABLE rainlab_blog_posts_categories (
  post_id INT NOT NULL,
  category_id INT NOT NULL,
  PRIMARY KEY (post_id, category_id)
);
```

## ⚠️ Error Handling

### Error Response Format

```json
{
    "status": "error",
    "message": "Error message here"
}
```

### Common Errors

| HTTP Status | Error | Solution |
|-------------|-------|----------|
| 404 | Article not found | Check article ID |
| 500 | SQLSTATE Error | Check model relationships |
| 422 | Validation Error | Check parameters sent |

## 🔒 Security

### Best Practices

1. **Rate Limiting**
   ```php
   Route::middleware('throttle:60,1')->prefix('api/v1')->group(function () {
       // routes
   });
   ```

2. **API Authentication** (Coming Soon)
   ```php
   Route::middleware('auth:api')->prefix('api/v1')->group(function () {
       // protected routes
   });
   ```

3. **Input Validation**
   - Always validate input from mobile applications
   - Use sanitization to prevent SQL injection

4. **HTTPS**
   - Ensure API is accessed via HTTPS in production

## 📝 Version Changes

### v1.0.0 (Current)
- ✅ List articles with pagination
- ✅ Article detail
- ✅ Search articles
- ✅ List categories
- ✅ Filter articles by category

### v1.1.0 (Coming Soon)
- 🚀 API Authentication with tokens
- 🚀 Rate limiting
- 🚀 Filter articles by date
- 🚀 Article sorting

## 🤝 Contributing

Contributions are welcome! Please create a pull request or open an issue for discussion.

## 📄 License

MIT License - See LICENSE file for details

## 📞 Support

For help or questions, please:
- Create an issue on GitHub
- Email: support@example.com
- Visit: https://octobercms.com/

## 📚 References

- [October CMS Documentation](https://docs.octobercms.com/)
- [Laravel Documentation](https://laravel.com/docs/)
- [RainLab Blog Plugin](https://github.com/rainlab/blog-plugin)
- [RESTful API Best Practices](https://restfulapi.net/)

---

**Last Updated:** October 2025
