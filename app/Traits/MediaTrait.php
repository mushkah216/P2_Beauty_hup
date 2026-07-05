<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait MediaTrait
{
     public function uploadMedia($modelClass, $media)
    {
        $paths = [];

        if (!empty($media)) {
            foreach ($media as $file) {
                $paths[] = $file->store($modelClass.'_media', 'public');
            }
        }

        return $paths;
    }

    public function updateMedia($modelClass, $media_owner, $media, $key)
    {
        if (!empty($media)) {
            $this->deleteMedia($media_owner->$key);
            return $this->uploadMedia($modelClass, $media);
        }
        return $media_owner->$key;
    }

    public function deleteMedia($media)
    {
        $media = array_filter($media ?? []);
        if (!empty($media)) {
            foreach ($media as $path) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    protected function getMediaUrls($media_owner)
    {
        if (empty($media_owner->media_json)) {
            return [];
        }

        return array_map(fn($path) => $this->getMediaUrlFromPath($path), $media_owner->media_json);
    }

    public function getMediaUrlFromPath($path)
    {
        if (empty($path)) {
            return null;
        }
        if (str_starts_with($path, 'https://via.placeholder.com')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}