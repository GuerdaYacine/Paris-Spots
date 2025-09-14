<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class EspaceVertService
{
    public function __construct(private HttpClientInterface $client) {}

    public function getRawEspaces(): array
    {
        $response = $this->client->request(
            'GET',
            'https://opendata.paris.fr/api/explore/v2.1/catalog/datasets/espaces_verts/records?limit=100'
        );

        return $response->toArray()['results'] ?? [];
    }

    public function getCleanedEspaces(): array
    {
        $rawData = $this->getRawEspaces();
        $cleaned = [];

        foreach ($rawData as $espace) {
            $nom = $espace['nom_ev'] ?? '';
            if (!preg_match('/[a-zA-Z]/', $nom)) {
                continue;
            }

            $codePostal = $espace['adresse_codepostal'] ?? '';
            if (strlen($codePostal) === 5 && str_starts_with($codePostal, '75')) {
                $espace['arrondissement'] = (int) substr($codePostal, 3, 2);
                $cleaned[] = $espace;
            }
        }

        return $cleaned;
    }


    public function filterEspaces(array $espaces, ?string $arrondissement): array
    {
        if (!$arrondissement) {
            return $espaces;
        }

        return array_values(array_filter($espaces, function ($e) use ($arrondissement) {
            return $e['arrondissement'] === (int)$arrondissement;
        }));
    }

    public function searchEspaces(array $espaces, string $search): array
    {
        $search = mb_strtolower(trim($search));

        return array_values(array_filter($espaces, function ($e) use ($search) {
            $nom = mb_strtolower($e['nom_ev'] ?? '');
            $adresse = mb_strtolower(
                ($e['adresse_numero'] ?? '') . ' ' .
                    ($e['adresse_typevoie'] ?? '') . ' ' .
                    ($e['adresse_libellevoie'] ?? '') . ' ' .
                    ($e['adresse_codepostal'] ?? '')
            );
            $arrondissement = (string)($e['arrondissement'] ?? '');

            return str_contains($nom, $search)
                || str_contains($adresse, $search)
                || str_contains($arrondissement, $search);
        }));
    }
}
