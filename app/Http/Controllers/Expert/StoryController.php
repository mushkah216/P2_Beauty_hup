<?php

namespace App\Http\Controllers\Expert;

use App\Http\Controllers\Controller;
use App\Http\Requests\Expert\CreateStoryRequest;
use App\Models\Story;
use App\Services\Expert\StoryServices;
use Illuminate\Http\Request;

class StoryController extends Controller
{
    //
    public StoryServices $story_services;

    public function __construct(StoryServices $story_services)
    {
        $this->story_services=$story_services;
    }
    public function getMyStories(){
        return $this->story_services->getMyStories();
    }

    public function createStory(CreateStoryRequest $request){
        return $this->story_services->createStory($request->validated());
    }
    public function deleteStory(Story $story){
        return $this->story_services->deleteStory($story);
    }
}

