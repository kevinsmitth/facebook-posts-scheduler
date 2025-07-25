<?php

namespace App\Actions\Facebook;

use Facebook\Exception\SDKException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use JoelButcher\Facebook\Facades\Facebook;

class StoreFacebookAction
{
    public function execute(string $title, string $content, UploadedFile|string|null $image = null): array
    {
        try {
            $message = $this->formatText($title, $content);
            $pageId = config('facebook.page_id');
            $accessToken = config('facebook.page_access_token');

            return $image
                ? $this->postWithImage($pageId, $message, $image, $accessToken)
                : $this->postText($pageId, $message, $accessToken);

        } catch (SDKException $e) {
            return ['success' => false, 'error' => 'SDK Exception: '.$e->getMessage()];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => 'General Exception: '.$e->getMessage()];
        }
    }

    private function postText(string $pageId, string $message, string $accessToken): array
    {
        Facebook::setDefaultAccessToken($accessToken);

        $response = Facebook::post("/{$pageId}/feed", ['message' => $message]);
        $data = $response->getGraphNode()->asArray();

        return [
            'success' => true,
            'post_id' => $data['id'],
            'data' => $data,
        ];
    }

    private function postWithImage(string $pageId, string $message, UploadedFile|string $image, string $accessToken): array
    {
        [$imagePath, $mimeType, $filename] = $this->resolveImageData($image);

        if (! file_exists($imagePath)) {
            return ['success' => false, 'error' => 'Arquivo não encontrado: '.$imagePath];
        }

        $result = $this->uploadViaCurl($pageId, $message, $imagePath, $mimeType, $filename, $accessToken);

        return $result['success']
            ? ['success' => true, 'post_id' => $result['data']['id'], 'data' => $result['data']]
            : ['success' => false, 'error' => $result['error']];
    }

    private function resolveImageData(UploadedFile|string $image): array
    {
        if ($image instanceof UploadedFile) {
            return [
                $image->getRealPath(),
                $image->getMimeType(),
                $image->getClientOriginalName(),
            ];
        }

        $imagePath = Storage::disk('public')->path($image);

        return [
            $imagePath,
            mime_content_type($imagePath),
            basename($imagePath),
        ];
    }

    private function uploadViaCurl(string $pageId, string $message, string $imagePath, string $mimeType, string $filename, string $accessToken): array
    {
        $url = 'https://graph.facebook.com/'.config('facebook.graph_version')."/{$pageId}/photos";

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'message' => $message,
                'access_token' => $accessToken,
                'source' => new \CURLFile($imagePath, $mimeType, $filename),
            ],
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

        return ($httpCode === 200 && isset($result['id']))
            ? ['success' => true, 'data' => $result]
            : ['success' => false, 'error' => $result['error']['message'] ?? 'Erro no upload'];
    }

    private function formatText(string $title, string $content): string
    {
        $text = trim($title);
        if (! empty($content)) {
            $text .= "\n\n".trim($content);
        }

        return $text;
    }
}
