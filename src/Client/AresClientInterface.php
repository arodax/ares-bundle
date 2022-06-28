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

interface AresClientInterface
{
    public function fetchIdentity(int $registrationNumber): array;
}
