<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate\Report;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Reports\Status;
use TYPO3\CMS\Reports\StatusProviderInterface;
use Werkraum\DeeplTranslate\DeepL\DeepL;

class UsageStatus implements StatusProviderInterface
{
    /**
     * Returns the status of an extension or (sub)system
     *
     * @return array An array of \TYPO3\CMS\Reports\Status objects
     */
    public function getStatus(): array
    {
        $reports = [];

        $config = GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('wr_deepl_translate');

        $authenticationKey = $config['authenticationKey'] ?? '';

        if (empty($authenticationKey)) {
            $reports []= GeneralUtility::makeInstance(
                Status::class,
                'Authentication key',
                'missing',
                'provide a authenticationKey in extension settings',
                \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::ERROR
            );
        } else {
            $deepL = new DeepL($authenticationKey);
            $usage = $deepL->usage();

            $reports['character_count'] = GeneralUtility::makeInstance(
                Status::class,
                'character count',
                $usage['character_count'],
                'Characters translated so far in the current billing period.',
                \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO
            );
            $reports['character_limit'] = GeneralUtility::makeInstance(
                Status::class,
                'character limit',
                $usage['character_limit'],
                'Current maximum number of characters that can be translated per billing period.',
                \TYPO3\CMS\Core\Type\ContextualFeedbackSeverity::INFO
            );
        }
        return $reports;
    }

    public function getLabel(): string
    {
        return 'Deepl Usage Status';
    }
}
