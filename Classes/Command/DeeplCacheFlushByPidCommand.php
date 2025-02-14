<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

declare(strict_types=1);

namespace Werkraum\DeeplTranslate\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Werkraum\DeeplTranslate\Cache\DeeplCacheManager;

class DeeplCacheFlushByPidCommand extends Command
{
    private DeeplCacheManager $cacheManager;

    public function __construct()
    {
        parent::__construct(null);
        $this->cacheManager = GeneralUtility::makeInstance(DeeplCacheManager::class);
    }

    protected function configure(): void
    {
        $this->setDescription('Flush deepl cached pages by pid');
        $this->setHelp(
            <<<'EOH'
Flush deepl cached pages by pid.

<b>Example:</b>

  <code>%command.full_name% 123,456,789</code>
EOH
        );
        $this->setDefinition([
            new InputArgument(
                'pids',
                InputArgument::REQUIRED,
                'Array of pids (specified as comma separated values) to flush.'
            ),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $pids = GeneralUtility::intExplode(',', $input->getArgument('pids'), true);
        foreach ($pids as $pid) {
            $this->cacheManager->flushCachesByTag('deeplPageId_' . $pid);
        }
        return Command::SUCCESS;
    }

}
