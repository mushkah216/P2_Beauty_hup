<?php

namespace App\Services\Expert;

use App\Models\Post;
use App\Traits\ApiResponseTrait;
use App\Traits\MediaTrait;
use Illuminate\Support\Facades\Auth;

class PostServices
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    
    use ApiResponseTrait, MediaTrait;

    // GET /expert/getMyPosts
    public function getMyPosts()
    {
        $expert = Auth::user();

        $posts = Post::where('provider_type', 'expert')
            ->where('provider_id', $expert->id)
            ->latest()
            ->paginate(10);

        return $this->sendResponse($posts, 'Posts retrieved successfully.');
    }

    // POST /expert/createPost
    public function createPost(array $input)
    {
        $expert = Auth::user();

        $post = Post::create([
            'provider_type' => 'expert',
            'provider_id'   => $expert->id,
            'caption'       => $input['caption'] ?? null,
            'media_json'    => $this->uploadMedia('post', $input['media'] ?? null),
        ]);

        return $this->sendResponse($post, 'Post created successfully.');
    }

    // GET /expert/getPostDetails/{post}
    public function getPostDetails(Post $post)
    {
        if (! $this->isOwner($post)) {
            return $this->sendError('You are not authorized to access this post.', 403);
        }

        return $this->sendResponse($post, 'Post details retrieved successfully.');
    }

    // PUT /expert/updatePost/{post}
    public function updatePost(array $input, Post $post)
    {
        if (! $this->isOwner($post)) {
            return $this->sendError('You are not authorized to update this post.', 403);
        }

        $post->update([
            'caption'    => $input['caption'] ?? $post->caption,
            'media_json' => $this->updateMedia('post', $post, $input['media'] ?? null, 'media_json'),
        ]);

        return $this->sendResponse($post, 'Post updated successfully.');
    }

    // DELETE /expert/deletePost/{post}
    public function deletePost(Post $post)
    {
        if (! $this->isOwner($post)) {
            return $this->sendError('You are not authorized to delete this post.', 403);
        }

        $this->deleteMedia($post->media_json);
        $post->delete();

        return $this->sendResponse([], 'Post deleted successfully.');
    }

    private function isOwner(Post $post): bool
    {
        $expert = Auth::user();

        return $post->provider_type === 'expert' && (int) $post->provider_id === (int) $expert->id;
    }
}
