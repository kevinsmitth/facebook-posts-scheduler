<?php

namespace App\Actions\Facebook;

class RetryFacebookAction
{
    public function execute(
        callable $action,
        array $params,
        int $maxRetries = 3,
        int $delaySeconds = 2
    ): array {
        $attempt = 0;
        $lastError = '';

        while ($attempt < $maxRetries) {
            $attempt++;

            try {
                $result = call_user_func_array($action, $params);

                if ($result['success']) {
                    return array_merge($result, ['attempts' => $attempt]);
                }

                $lastError = $result['error'] ?? 'Erro desconhecido';

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }

            if ($attempt < $maxRetries) {
                sleep($delaySeconds);
            }
        }

        return [
            'success' => false,
            'error' => "Falha após {$maxRetries} tentativas. Último erro: {$lastError}",
            'attempts' => $attempt,
        ];
    }

    public function retryStore(string $title, string $content, $image = null, int $maxRetries = 3): array
    {
        $storeAction = app(StoreFacebookAction::class);

        return $this->execute(
            [$storeAction, 'execute'],
            [$title, $content, $image],
            $maxRetries
        );
    }

    public function retryUpdate(string $postId, string $title, string $content, $image = null, int $maxRetries = 3): array
    {
        $updateAction = app(UpdateFacebookAction::class);

        return $this->execute(
            [$updateAction, 'execute'],
            [$postId, $title, $content, $image],
            $maxRetries
        );
    }

    public function retryDestroy(string $postId, int $maxRetries = 3): array
    {
        $destroyAction = app(DestroyFacebookAction::class);

        return $this->execute(
            [$destroyAction, 'execute'],
            [$postId],
            $maxRetries
        );
    }
}
