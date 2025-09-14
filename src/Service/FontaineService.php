<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class FontaineService
{
    public function __construct(private HttpClientInterface $client) {}

    public function getRawFontaines(): array
    {
        $response = $this->client->request(
            'GET',
            'https://opendata.paris.fr/api/explore/v2.1/catalog/datasets/fontaines-a-boire/records?limit=100'
        );

        return $response->toArray()['results'] ?? [];
    }

    public function getCleanedFontaines(): array
    {
        $fontaines = $this->getRawFontaines();
        $cleaned = [];

        foreach ($fontaines as $fontaine) {
            $commune = $fontaine['commune'] ?? '';
            if (preg_match('/(\d+)EME/i', $commune, $matches)) {
                $fontaine['arrondissement'] = (int) $matches[1];
                $cleaned[] = $fontaine;
            }
        }

        return $cleaned;
    }


    public function filterFontaines(array $fontaines, ?int $arrondissement, ?bool $disponibleOnly): array
    {
        return array_values(array_filter($fontaines, function ($f) use ($arrondissement, $disponibleOnly) {
            if ($arrondissement && $f['arrondissement'] !== $arrondissement) {
                return false;
            }

            if ($disponibleOnly && strtolower(trim($f['dispo'] ?? '')) !== 'oui') {
                return false;
            }

            return true;
        }));
    }
}
