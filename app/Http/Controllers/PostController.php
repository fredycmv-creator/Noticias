<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Models\Category;
use App\Models\Post;

class PostController extends Controller
{
    public function index()
    {
        return view('admin.index', ['posts' => Post::with('category')->latest('id')->paginate(10)]);
    }

    public function create()
    {
        return view('admin.form', ['post' => new Post(['status' => 'draft']), 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(PostRequest $request)
    {
        $post = new Post($request->validated());
        $post->user_id = $request->user()->id;
        $post->save();

        return redirect()->route('admin.posts.index')->with('success', 'Noticia creada correctamente.');
    }

    public function edit(Post $post)
    {
        return view('admin.form', ['post' => $post, 'categories' => Category::orderBy('name')->get()]);
    }

    public function update(PostRequest $request, Post $post)
    {
        $post->update($request->validated());

        return redirect()->route('admin.posts.index')->with('success', 'Noticia actualizada correctamente.');
    }

    public function confirm(Post $post)
    {
        return view('admin.delete', compact('post'));
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')->with('success', 'Noticia eliminada correctamente.');
    }
}
