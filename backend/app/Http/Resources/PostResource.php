<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     title="Post",
 *     description="Post resource",
 *
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="title", type="string", example="Title"),
 * @OA\Property(property="content", type="string", example="Content"),
 * @OA\Property(property="status", type="string", example="draft"),
 * @OA\Property(property="social_media_response", type="string", example="Social media response"),
 * @OA\Property(property="image_path", type="string", example="path/to/image.jpg"),
 * @OA\Property(property="scheduled_for", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 * @OA\Property(property="scheduled_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 * @OA\Property(property="sent_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 * @OA\Property(property="failed_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 * @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 * )
 */
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'social_media_response' => json_decode($this->social_media_response),
            'image_path' => config('app.url').'/storage/'.$this->image_path,
            'scheduled_for' => $this->scheduled_for?->format('Y-m-d H:i:s'),
            'scheduled_at' => $this->scheduled_at?->format('Y-m-d H:i:s'),
            'sent_at' => $this->sent_at?->format('Y-m-d H:i:s'),
            'failed_at' => $this->failed_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
