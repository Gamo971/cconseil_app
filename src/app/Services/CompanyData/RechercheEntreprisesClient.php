<?php

namespace App\Services\CompanyData;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RechercheEntreprisesClient
{
    private const BASE_URL = 'https://recherche-entreprises.api.gouv.fr';

    private function http(): PendingRequest
    {
        $ua = trim((string) config('app.name', 'cconseil_app')) ?: 'cconseil_app';

        return Http::baseUrl(self::BASE_URL)
            ->acceptJson()
            ->withUserAgent($ua)
            ->timeout(10)
            ->retry(2, 300, function ($exception) {
                $response = $exception->response;
                if (! $response) {
                    return false;
                }

                return in_array($response->status(), [429, 500, 502, 503, 504], true);
            });
    }

    public function search(string $q, int $page = 1, int $perPage = 10): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(25, $perPage));

        $resp = $this->http()->get('/search', [
            'q' => $q,
            'page' => $page,
            'per_page' => $perPage,
        ]);

        $resp->throw();

        return $resp->json();
    }
}
