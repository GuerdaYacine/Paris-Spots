<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ActiviteService
{
    public function __construct(private HttpClientInterface $client) {}

    public function getRawActivites(): array
    {
        $response = $this->client->request(
            'GET',
            'https://opendata.paris.fr/api/explore/v2.1/catalog/datasets/que-faire-a-paris-/records?limit=100'
        );

        return $response->toArray()['results'] ?? [];
    }

    public function getCleanedActivites(): array
    {
        $activites = $this->getRawActivites();
        $cleaned = [];

        foreach ($activites as $a) {
            $zipcode   = $a['address_zipcode'] ?? '';
            $dateStart = $a['date_start'] ?? '';
            $dateEnd   = $a['date_end'] ?? '';

            if (strlen($zipcode) === 5 && str_starts_with($zipcode, '75') && $dateStart && $dateEnd) {
                $a['arrondissement'] = (int) substr($zipcode, 3, 2);
                $cleaned[] = $a;
            }
        }

        return $cleaned;
    }


    public function filterActivites(array $activites, ?string $prix, ?int $arrondissement): array
    {
        if ($prix) {
            $activites = array_filter($activites, function ($a) use ($prix) {
                return strtolower($a['price_type'] ?? '') === strtolower($prix);
            });
        }

        if ($arrondissement) {
            $activites = array_filter($activites, function ($a) use ($arrondissement) {
                return (int) ($a['arrondissement'] ?? 0) === $arrondissement;
            });
        }

        return array_values($activites);
    }

    public function searchActivites(array $activites, string $search): array
    {
        $search = mb_strtolower(trim($search));

        return array_values(
            array_filter($activites, function ($e) use ($search) {
                $titre = mb_strtolower($e['title'] ?? '');
                $description = mb_strtolower($e['lead_text'] ?? '');
                $adresse = mb_strtolower($e['address_street'] ?? '');
                $arrondissement = (int)($e['arrondissement'] ?? '');

                return str_contains($titre, $search) ||
                    str_contains($description, $search) ||
                    str_contains($adresse, $search) ||
                    str_contains((string)$arrondissement, $search);
            })
        );
    }
}
