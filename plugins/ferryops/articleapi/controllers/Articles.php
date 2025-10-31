<?php namespace Ferryops\ArticleAPI\Controllers;

use Illuminate\Routing\Controller;
use Ferryops\ArticleAPI\Models\Article;
use RainLab\Blog\Models\Category;
use Response;

class Articles extends Controller
{
    // Tampilkan daftar semua artikel dengan pagination
    public function list()
    {
        try {
            $page = request('page', 1);
            $limit = request('limit', 10);
            $categoryId = request('category_id', null);
            $search = request('search', null);

            // Validasi limit (max 100)
            $limit = $limit > 100 ? 100 : $limit;
            $limit = $limit < 1 ? 10 : $limit;

            $query = Article::where('published', true);

            // Filter berdasarkan kategori jika diberikan
            if ($categoryId) {
                $query->whereHas('categories', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }

            if ($search && strlen($search) >= 2) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', '%' . $search . '%')
                      ->orWhere('excerpt', 'like', '%' . $search . '%');
                });
            }

            $articles = $query->orderBy('created_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            // Load kategori dan user untuk setiap artikel
            $articles->load(['categories', 'user']);

            // Format data dengan kategori (tanpa content)
            $data = $articles->items();
            $formattedData = array_map(function($article) {
                return $this->formatArticleWithCategories($article, false);
            }, $data);

            return Response::json([
                'status' => 'success',
                'message' => 'Artikel berhasil diambil',
                'data' => $formattedData,
                'pagination' => [
                    'total' => $articles->total(),
                    'per_page' => $articles->perPage(),
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage(),
                    'from' => $articles->firstItem(),
                    'to' => $articles->lastItem()
                ]
            ], 200);

        } catch (\Exception $e) {
            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Tampilkan detail satu artikel
    public function show($slug)
    {
        try {
            // Cari artikel berdasarkan slug
            $article = Article::where('slug', $slug)
                ->where('published', true)
                ->firstOrFail();

            $article->load(['categories', 'user']);

            return Response::json([
                'status' => 'success',
                'message' => 'Detail artikel berhasil diambil',
                'data' => $this->formatArticleWithCategories($article, true)
            ], 200);

        } catch (\Exception $e) {
            return Response::json([
                'status' => 'error',
                'message' => 'Artikel tidak ditemukan'
            ], 404);
        }
    }

    // Cari artikel berdasarkan keyword
    public function search()
    {
        try {
            $keyword = request('q', '');
            $page = request('page', 1);
            $limit = request('limit', 10);
            $categoryId = request('category_id', null);

            // Validasi keyword
            if (strlen($keyword) < 2) {
                return Response::json([
                    'status' => 'error',
                    'message' => 'Keyword minimal 2 karakter'
                ], 400);
            }

            // Validasi limit
            $limit = $limit > 100 ? 100 : $limit;
            $limit = $limit < 1 ? 10 : $limit;

            $query = Article::where('published', true)
                ->where(function($q) use ($keyword) {
                    $q->where('title', 'like', '%' . $keyword . '%')
                        ->orWhere('content', 'like', '%' . $keyword . '%')
                        ->orWhere('excerpt', 'like', '%' . $keyword . '%');
                });

            // Filter kategori jika diberikan
            if ($categoryId) {
                $query->whereHas('categories', function($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }

            $articles = $query->orderBy('created_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            $articles->load(['categories', 'user']);

            $data = $articles->items();
            $formattedData = array_map(function($article) {
                return $this->formatArticleWithCategories($article, false);
            }, $data);

            return Response::json([
                'status' => 'success',
                'message' => 'Hasil pencarian berhasil diambil',
                'data' => $formattedData,
                'pagination' => [
                    'total' => $articles->total(),
                    'per_page' => $articles->perPage(),
                    'current_page' => $articles->currentPage(),
                    'last_page' => $articles->lastPage()
                ]
            ], 200);

        } catch (\Exception $e) {
            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Get semua kategori
    public function categories()
    {
        try {
            $categories = Category::where('parent_id', null)
                ->with('children')
                ->orderBy('name', 'asc')
                ->get();

            $formattedCategories = $categories->map(function($category) {
                return $this->formatCategory($category);
            });

            return Response::json([
                'status' => 'success',
                'message' => 'Kategori berhasil diambil',
                'data' => $formattedCategories
            ], 200);

        } catch (\Exception $e) {
            return Response::json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Format artikel dengan kategori
    private function formatArticleWithCategories($article, $includeContent = false)
    {
        $categories = $article->categories->map(function($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug
            ];
        })->toArray();

        $formatted = [
            'id' => $article->id,
            'title' => $article->title,
            'slug' => $article->slug,
            'excerpt' => $article->excerpt,
            'author_name' => $article->author_name,
            'published' => (bool) $article->published,
            'published_at' => $article->published_at,
            'created_at' => $article->created_at,
            'updated_at' => $article->updated_at,
            'featured_images' => $article->featured_images_data,
            'categories' => $categories,
        ];

        // Tambahkan content hanya untuk detail view
        if ($includeContent) {
            $formatted['content'] = $article->content;
        }

        return $formatted;
    }

    // Format kategori dengan sub-kategori
    private function formatCategory($category)
    {
        $children = $category->children ? $category->children->map(function($child) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'slug' => $child->slug
            ];
        })->toArray() : [];

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'children' => $children
        ];
    }
}
