<?php
namespace Examples\Blog;

class PostController extends Controller
{
    public function index()
    {
        // Example usage of the generated CRUD
        $posts = Post::query()
            ->when(request('title'), fn($q) => $q->where('title', 'like', '%'.request('title').'%'))
            ->when(request('category_id'), fn($q) => $q->where('category_id', request('category_id')))
            ->when(request('created_at'), fn($q, $date) => $q->whereDate('created_at', $date))
            ->paginate();

        return PostResource::collection($posts);
    }
}

