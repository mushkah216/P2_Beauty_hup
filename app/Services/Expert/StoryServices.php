<?php

namespace App\Services\Expert;

use App\Models\Story;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StoryServices
{
    use ApiResponseTrait;

     
    public function getMyStories()
    {
        $expert = Auth::user();

        $stories = Story::where('provider_type', 'expert')
            ->where('provider_id', $expert->id)
            ->where('is_active', true)
            ->where('expires_at', '>', now())
            ->latest()
            ->get();

        return $this->sendResponse($stories, 'Stories retrieved successfully.');
    }

     
    public function createStory(array $input)
    {
        $expert = Auth::user();

        if (!empty($input['media'])) {
            $file = $input['media'];
            $mediaType = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
            $path = $file->store('story_media', 'public');
        } else {
            $mediaType = 'text';
            $path = null;
        }

        $story = Story::create([
            'provider_type' => 'expert',
            'provider_id'   => $expert->id,
            'media_type'    => $mediaType,
            'media_url'     => $path,
            'caption'       => $input['caption'] ?? null,
            'expires_at'    => now()->addDay(),
        ]);

        return $this->sendResponse($story, 'Story created successfully.');
    }

     
    public function deleteStory(Story $story)
    {
        if (! $this->isOwner($story)) {
            return $this->sendError('You are not authorized to delete this story.', 403);
        }

        if ($story->media_url) {
            Storage::disk('public')->delete($story->media_url);
        }

        $story->delete();

        return $this->sendResponse([], 'Story deleted successfully.');
    }

    private function isOwner(Story $story): bool
    {
        $expert = Auth::user();

        return $story->provider_type === 'expert' && (int) $story->provider_id === (int) $expert->id;
    }
}
