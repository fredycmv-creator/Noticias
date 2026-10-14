<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'categoria' => ['nullable', 'integer', 'exists:categories,id']]);
        $query = Post::published()->with(['category', 'author']);
        if ($q = trim($filters['q'] ?? '')) {
            $query->where(function ($search) use ($q) {
                $search->where('title', 'like', '%'.$q.'%')->orWhere('body', 'like', '%'.$q.'%');
            });
        }
        if ($category = $filters['categoria'] ?? null) {
            $query->where('category_id', $category);
        }

        return view('news.index', ['posts' => $query->orderByDesc('published_at')->orderByDesc('id')->paginate(6)->withQueryString(), 'categories' => Category::orderBy('name')->get()]);
    }

    public function show(string $slug)
    {
        return view('news.show', ['post' => Post::published()->with(['category', 'author'])->where('slug', $slug)->firstOrFail()]);
    }
}
