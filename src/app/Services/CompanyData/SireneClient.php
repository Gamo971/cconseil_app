<?php

namespace App\Services\CompanyData;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class SireneClient
{
    private const BASE_URL = 'https://api.insee.fr/api-sirene/3.11';

    private function http(): PendingRequest
    {
        return Http::baseUrl(self::BASE_URL)
            ->acceptJson()
            ->withToken((string) config('services.insee.token'))
            ->timeout(10)
            ->retry(2, 500, function ($exception) {
                $response = $exception->response;
                if (! $response) {
                    return false;
                }

                return in_array($response->status(), [429, 500, 502, 503, 504], true);
            });
    }

    public function getEtablissementBySiret(string $siret): array
    {
        $siret = substr(preg_replace('/\D+/', '', $siret), 0, 14);
        if (strlen($siret) !== 14) {
            abort(422, 'SIRET invalide');
        }

        $resp = $this->http()->get("/siret/{$siret}");
        $resp->throw();

        return $resp->json();
    }
}
