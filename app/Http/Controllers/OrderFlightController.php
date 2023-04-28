<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

class OrderFlightController extends Controller
{
    public function __invoke (Request $request, Client $client)
    {
        $url = 'https://test.api.amadeus.com/v1/booking/flight-orders';
        if (session('access_token')) {
            $access_token = session('access_token');
        } else {
            $access_token = json_decode(app('App\Http\Controllers\AccessTokenController')->__invoke($client))->access_token;
            session(['access_token' => $access_token]);
        }
        //dd($request['data']);
        $data = [
            'data' => [
                'type' => 'flight-order',
                'flightOffers' => [
                    json_decode($request['data'])
                ],
                //'validatingAirlineCodes' => ['TP'], // fixed syntax error
                'travelers' => [
                    [
                        'id' => '1',
                        'dateOfBirth' => '2001-09-02',
                        'name' => [
                            'firstName' => 'Idris Aweda',
                            'lastName' => 'Zubair'
                        ],
                        'gender' => 'MALE',
                        'contact' => [
                            'emailAddress' => 'zubairidrisaweda@gmail.com',
                            'phones' => [
                                [
                                    'deviceType' => 'MOBILE',
                                    'countryCallingCode' => '234',
                                    'number' => '7052053780'
                                ]
                            ]
                        ],
                        'documents' => [
                            [
                                'documentType' => 'PASSPORT',
                                'birthPlace' => 'Madrid',
                                'issuanceLocation' => 'Madrid',
                                'issuanceDate' => '2015-04-14',
                                'number' => '00000000',
                                'expiryDate' => '2025-04-14',
                                'issuanceCountry' => 'ES',
                                'validityCountry' => 'ES',
                                'nationality' => 'ES',
                                'holder' => true
                            ]
                        ]
                    ]
                ]
            ]
        ];


        //dd(json_encode($data));
        try {
            //dd("Error 400");
            $response = $client->post($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $access_token
                ],
                'json' => $data,
                'verify' => false,
            ]);
            //dd($response);
            if ($response->getStatusCode() === 400) {
                    dd("Error 400");
                    $errorResponse = json_decode($response->getBody(), true);
                    var_dump($errorResponse);
                }

            $response = $response->getBody();
            $response = json_decode($response);
            //dd($response);
            return view('confirm')->with('flight', $response->data);
        } catch (GuzzleException $exception) {
            return $exception->getMessage();
        }

    }
}