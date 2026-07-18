<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    public function __construct(
        private HttpClientInterface $client
    ) {
    }


    public function getCoordinates(string $adresse): array
    {
        $response = $this->client->request(
            'GET',
            'https://nominatim.openstreetmap.org/search',
            [
                'query' => [
                    'format' => 'jsonv2',
                    'q' => $adresse,
                    'limit' => 1
                ],
                'headers' => [
                    'User-Agent' => 'EcoRide/1.0'
                ]
            ]
        );


        $data = $response->toArray();


        if (empty($data)) {
            return [];
        }


        return [
            'latitude' => (float) $data[0]['lat'],
            'longitude' => (float) $data[0]['lon']
        ];
    }
}