<?php

namespace App\Console\Commands;

use App\Actions\Facebook\StoreFacebookAction;
use App\Enums\PostStatusEnum;
use App\Models\Post;
use App\Models\SendLog;
use Illuminate\Console\Command;

class ProcessScheduledPosts extends Command
{
    protected $signature = 'posts:process-scheduled';

    protected $description = 'Processa posts agendados';

    public function handle(StoreFacebookAction $action)
    {
        $posts = Post::where('status', PostStatusEnum::SCHEDULED)
            ->whereDate('scheduled_for', '<=', now())
            ->get();

        foreach ($posts as $post) {
            $post->update(['status' => PostStatusEnum::SENT]);

            $result = $action->execute($post->title, $post->content, $post->image_path);

            $data = [];

            if ($result['success']) {
                $data['social_media_response'] = json_encode($result['data']);
                $data['status'] = PostStatusEnum::SENT;
                $data['sent_at'] = now();
            } else {
                $data['social_media_response'] = json_encode($result['error']);
                $data['status'] = PostStatusEnum::FAILED;
                $data['failed_at'] = now();
            }

            $post->update([
                'status' => $data['status'],
                'social_media_response' => $data['social_media_response'],
                'sent_at' => $data['sent_at'] ?? null,
                'failed_at' => $data['failed_at'] ?? null,
            ]);

            SendLog::create([
                'post_id' => $post->id,
                'user_id' => $post->user_id,
                'status' => $data['status'],
                'response' => $data['social_media_response'],
            ]);

            $this->info($result['success']
                ? "Post {$post->id} publicado com sucesso"
                : "Post {$post->id} falhou: {$result['error']}"
            );
        }
    }
}
