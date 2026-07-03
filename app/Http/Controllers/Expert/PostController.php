<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expert\CreatePostRequest;
use App\Http\Requests\Expert\UpdatePostRequest;
use App\Models\Post;
use App\Services\Expert\PostServices;
use App\Traits\ApiResponseTrait;
use Illuminate\Foundation\Providers\FoundationServiceProvider;
use Illuminate\Http\Request;

class PostController extends Controller
{
    //
    use ApiResponseTrait;
    public PostServices $post_services;

    public function __construct(PostServices $post_services)
    {
        $this->post_services=$post_services;
    }
    public function getMyPosts(){
        return $this->post_services->getMyPosts();
    }
    public function createPost(CreatePostRequest $request){
        return $this->post_services->createPost($request->validated());
    }
    public function getPostDetails(Post $post){
        return $this->post_services->getPostDetails($post);
    }
    public function updatePost(UpdatePostRequest $request,Post $post){
        return $this->post_services->updatePost($request->validated(),$post);
    }
    public function deletePost(Post $post){
        return $this->post_services->deletePost($post);
    }
}
