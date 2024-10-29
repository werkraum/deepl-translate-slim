<?php
/*
 * This file is part of TYPO3 CMS-based extension "deepl_translate" by werkraum.
 *
 *  It is free software; you can redistribute it and/or modify it under
 *  the terms of the GNU General Public License, either version 2
 *  of the License, or any later version.
 *
 */

namespace Werkraum\DeeplTranslate;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Configuration\SiteTcaConfiguration;
use TYPO3\CMS\Backend\Routing\Route;

/**
 * Only to manage the unavailable TCA column type 'user' on save
 */
class ExtendedSiteTcaConfiguration extends SiteTcaConfiguration
{

    public function getTca(): array
    {
        $tca = parent::getTca();

        /** @var ServerRequestInterface $request */
        $request = $GLOBALS['TYPO3_REQUEST'];

        /** @var Route $routing */
        if ($route = $request->getAttribute('route')) {
            if ( $route->getPath() === '/module/site/configuration/save' ) {
                $tca['site']['columns']['deepl_split_content_by_selectors']['config']['type'] = 'text';
                $tca['site']['columns']['deepl_recaptcha']['config']['type'] = 'text';
                $tca['site']['columns']['deepl_recaptcha_redirect_to_page']['config']['type'] = 'text';
            }
        }

        return $tca;
    }
}
