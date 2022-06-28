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

use Symfony\Contracts\Cache\CacheInterface;

class AresCachingClient implements AresClientInterface
{
    private AresClient $client;
    private CacheInterface $cache;

    public function __construct(AresClient $client, CacheInterface $cache)
    {
        $this->cache = $cache;
        $this->client = $client;
    }

    public function fetchIdentity(int $registrationNumber): array
    {
        return $this->cache->get('ares-identity.'.$registrationNumber, function () use($registrationNumber) {
            return $this->client->fetchIdentity($registrationNumber);
        });
    }
}
