<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SendLog",
 *     type="object",
 *     title="Send Log",
 *     description="Send Log resource",
 *
 * @OA\Property(property="id", type="integer", example=1),
 * @OA\Property(property="post", type="object", ref="#/components/schemas/Post"),
 * @OA\Property(property="user", type="object", ref="#/components/schemas/User"),
 * @OA\Property(property="status", type="string", example="sent"),
 * @OA\Property(property="sent_at", type="string", format="date-time", example="2024-01-15T10:30:00Z"),
 * )
 */
class SendLogResource extends JsonResource
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
            'post' => $this->when($this->post, PostResource::make($this->post)),
            'user' => $this->when($this->user, UserResource::make($this->user)),
            'status' => $this->status,
            'sent_at' => $this->sent_at?->format('Y-m-d H:i:s'),
            'response' => $this->response,
        ];
    }
}
