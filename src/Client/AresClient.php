<?php

/*
 * This file is part of the ares-bundle package.
 *
 * (c) ARODAX a.s.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Arodax\AresBundle\Client;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DomCrawler\Crawler;

class AresClient implements AresClientInterface
{
    private HttpClientInterface $httpClient;
    private string $baseUrl;

    //https://wwwinfo.mfcr.cz/cgi-bin/ares/darv_std.cgi?ico=70940517
    //http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi?ico=70940517

    public function __construct(
        HttpClientInterface $httpClient,
        string $baseUrl = 'https://wwwinfo.mfcr.cz/cgi-bin/ares')
    {
        $this->httpClient = $httpClient;
        $this->baseUrl = $baseUrl;
    }

    public function fetchIdentity(int $registrationNumber): array
    {
        $response = $this->httpClient->request('GET', $this->baseUrl."/darv_bas.cgi", [
            'headers' => [
             ],
            'query' => [
                'ico' => $registrationNumber
            ],
            'verify_peer' => false,
            'verify_host' => false,
        ]);

        if (200 === $response->getStatusCode()) {
            $data = $this->parseResponse($response->getContent());

            if(empty($data)) {
                throw new \InvalidArgumentException('subject not found');
            }

            return $data;
        } else {
            throw new \RuntimeException('http client error');
        }
    }

    private function parseResponse(string $xml): ?array
    {
        $doc = new Crawler($xml);

        $records = (int)$doc->filterXPath('//are:Ares_odpovedi/are:Odpoved/D:PZA')->text();

        if(empty($records)) {
            return null;
        }

        $base = '//are:Ares_odpovedi/are:Odpoved';

        $info = [
            'registration_number' =>  $doc->filterXPath($base.'/D:VBAS/D:ICO')->text(),
            'name' =>  $doc->filterXPath($base.'/D:VBAS/D:OF')->text(),
            'city' =>  $doc->filterXPath($base.'/D:VBAS/D:AA/D:N')->text(),
            'street' =>  $doc->filterXPath($base.'/D:VBAS/D:AA/D:NU')->text(),
            'street_number' =>  $doc->filterXPath($base.'/D:VBAS/D:AA/D:CD')->text(),
            'postal_code' =>  $doc->filterXPath($base.'/D:VBAS/D:AA/D:PSC')->text(),
            'ruian_adm' =>  $doc->filterXPath($base.'/D:VBAS/D:AA/D:AU/U:KA')->text(),
            'county' =>  null,
            'street_code' => null,
            'vat_number' => null,
        ];

        try {
            $info['county'] = $doc->filterXPath($base.'/D:VBAS/D:AA/D:NOK')->text();
        } catch(\InvalidArgumentException $e) {}


        try {
            $info['street_code'] = $doc->filterXPath($base.'/D:VBAS/D:AA/D:CO')->text();
        } catch(\InvalidArgumentException $e) {}

        try {
            $info['vat_number'] = $doc->filterXPath($base.'/D:VBAS/D:DIC')->text();
        } catch(\InvalidArgumentException $e) {}

        return $info;
    }
}
