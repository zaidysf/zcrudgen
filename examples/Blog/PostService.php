<?php

namespace Examples\Blog;

class PostService
{
    protected $repository;

    public function __construct(PostRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function create(array $data)
    {
        // Example of business logic
        $data['slug'] = Str::slug($data['title']);

        // Handle SEO metadata
        $data['meta_description'] = Str::limit(strip_tags($data['content']), 160);

        // Set default status
        $data['status'] = $data['status'] ?? 'draft';

        // Set author
        $data['author_id'] = auth()->id();

        $post = $this->repository->create($data);

        // Dispatch events
        event(new PostCreated($post));

        return $post;
    }
}
