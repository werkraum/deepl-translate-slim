<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\Middleware\Event;

use Psr\Http\Message\ServerRequestInterface;
class CacheIdentifierEvent
{
    public function __construct(private ServerRequestInterface $request, private string $cacheIdentifier)
    {
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getCacheIdentifier(): string
    {
        return $this->cacheIdentifier;
    }

    public function setCacheIdentifier(string $cacheIdentifier): void
    {
        $this->cacheIdentifier = $cacheIdentifier;
    }
}
