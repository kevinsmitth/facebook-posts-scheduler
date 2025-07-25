<?php

namespace App\Actions\Facebook;

use Facebook\Exception\SDKException;
use JoelButcher\Facebook\Facades\Facebook;

class DestroyFacebookAction
{
    public function execute(string $postId): array
    {
        try {
            $accessToken = config('facebook.page_access_token');

            if (empty($accessToken)) {
                return ['success' => false, 'error' => 'Access token não configurado'];
            }

            Facebook::setDefaultAccessToken($accessToken);
            Facebook::delete("/{$postId}");

            return ['success' => true, 'message' => 'Post deletado com sucesso'];

        } catch (SDKException $e) {
            return $this->handleDeleteError($postId, $accessToken, $e->getMessage());
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'Erro geral: '.$e->getMessage()];
        }
    }

    private function handleDeleteError(string $postId, string $accessToken, string $sdkError): array
    {
        $url = 'https://graph.facebook.com/'.config('facebook.graph_version')."/{$postId}";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => http_build_query(['access_token' => $accessToken]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'cURL Error: '.$curlError];
        }

        $result = json_decode($response, true);

        return ($httpCode === 200 && isset($result['success']) && $result['success'])
            ? ['success' => true, 'message' => 'Post deletado via fallback']
            : ['success' => false, 'error' => $result['error']['message'] ?? $sdkError];
    }
}
