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

namespace Werkraum\DeeplTranslate\ExpressionLanguage;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class DeeplConditionFunctionsProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions(): array
    {
        return [
            $this->getDeeplSourceLanguageFunction()
        ];
    }

    protected function getDeeplSourceLanguageFunction(): ExpressionFunction
    {
        return new ExpressionFunction('deeplSourceLanguage', function (): void {
//            empty by design
        }, function ($arguments, $str) {
            /** @var ServerRequestInterface $request */
            $request = $GLOBALS['TYPO3_REQUEST'] ?? ServerRequestFactory::fromGlobals();
            if ($request->getAttribute('originalLanguage') instanceof SiteLanguage) {
                $siteLanguage =  $request->getAttribute('originalLanguage');
                $methodName = 'get' . ucfirst(trim($str));
                if (method_exists($siteLanguage, $methodName)) {
                    return $siteLanguage->$methodName();
                }
            }
            return -1;
        });
    }
}
