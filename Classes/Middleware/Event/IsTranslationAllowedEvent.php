<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\Middleware\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Psr\Http\Message\ServerRequestInterface;
class IsTranslationAllowedEvent implements StoppableEventInterface
{
    private bool $allowed = true;

    private bool $stopPropagation = false;

    public function __construct(private readonly ServerRequestInterface $request)
    {
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function setAllowed(bool $allowed): void
    {
        $this->allowed = $allowed;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function setStopPropagation(bool $stopPropagation): void
    {
        $this->stopPropagation = $stopPropagation;
    }

    public function isPropagationStopped(): bool
    {
        return $this->stopPropagation;
    }
}
