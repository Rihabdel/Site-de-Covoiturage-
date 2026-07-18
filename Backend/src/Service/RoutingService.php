<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class RoutingService
{
    public function __construct(
        private HttpClientInterface $client
    ) {
    }


    public function calculateRoute(
        float $latitudeDepart,
        float $longitudeDepart,
        float $latitudeArrivee,
        float $longitudeArrivee
    ): array {

        $url = sprintf(
            'https://router.project-osrm.org/route/v1/driving/%s,%s;%s,%s?overview=false',
            $longitudeDepart,
            $latitudeDepart,
            $longitudeArrivee,
            $latitudeArrivee
        );


        $response = $this->client->request('GET', $url);

        $data = $response->toArray();


        if (!isset($data['routes'][0])) {
            throw new \Exception('Impossible de calculer le trajet');
        }


        $route = $data['routes'][0];


        return [
            'distance' => round($route['distance'] / 1000, 2),
            'duree' => round($route['duration'] / 60)
        ];
    }
}