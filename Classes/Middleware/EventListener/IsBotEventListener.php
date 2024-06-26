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
 * This file is part of TYPO3 CMS-based extension "wr_deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

/*
 * This file is part of TYPO3 CMS-based extension "wr_deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\Middleware\EventListener;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Werkraum\DeeplTranslate\Middleware\Event\IsTranslationAllowedEvent;

class IsBotEventListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __invoke(IsTranslationAllowedEvent $event): void
    {
        $crawler = new CrawlerDetect();
        $isBot = $crawler->isCrawler();

        if ($isBot) {
            $this->logger->info('detected bot', ['bot' => $crawler->getMatches()]);
            $event->setAllowed(false);
            $event->setStopPropagation(true);
        }
        $this->logger->info('no bot', ['user-agent' => $crawler->getUserAgent()]);
    }
}
