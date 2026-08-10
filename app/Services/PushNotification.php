<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotification
{
    /**
     * Kirim push notification menggunakan OneSignal.
     *
     * @param string $title Judul notifikasi
     * @param string $message Isi pesan notifikasi
     * @param array $segments Segmen penerima (default: semua user yang aktif)
     * @param array|null $userIds Array spesifik ID user/subscription jika ingin mengirim ke user tertentu
     * @param array|null $additionalData Data tambahan (payload) yang ingin dikirimkan (opsional)
     * @return \Illuminate\Http\Client\Response|null
     */
    public static function send(string $title, string $message, array $segments = ['Subscribed Users', 'Total Subscriptions', 'Active Users', 'All'], array $userIds = null, array $additionalData = null)
    {
        $appId = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.rest_api_key');

        if (empty($appId) || empty($apiKey)) {
            Log::error('Kredensial OneSignal belum dikonfigurasi di config/services.php atau file .env');
            return null;
        }

        // Build HTTP request - bypass SSL verify di lokal (Windows tidak punya CA bundle)
        $http = Http::withHeaders([
            'Authorization' => 'Key ' . $apiKey,
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ]);

        if (app()->environment('local')) {
            $http = $http->withoutVerifying();
        }

        $payload = [
            'app_id' => $appId,
            'target_channel' => 'push',
            'contents' => [
                'en' => $message,
            ],
            'headings' => [
                'en' => $title,
            ]
        ];

        // Jika array userIds diisi, maka notifikasi hanya dikirim ke user tersebut
        if (!empty($userIds)) {
            $payload['include_aliases'] = ['external_id' => $userIds]; 
            // Catatan: sesuaikan key aliases jika menggunakan player_id (include_subscription_ids)
        } else {
            $payload['included_segments'] = $segments;
        }

        if (!empty($additionalData)) {
            $payload['data'] = $additionalData;
        }

        $response = $http->post('https://onesignal.com/api/v1/notifications', $payload);

        if ($response->failed()) {
            Log::error('OneSignal gagal kirim notifikasi', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);
        }

        return $response;
    }
}
