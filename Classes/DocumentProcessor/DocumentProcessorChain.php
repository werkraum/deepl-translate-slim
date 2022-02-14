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

namespace Werkraum\DeeplTranslate\DocumentProcessor;

class DocumentProcessorChain
{

    /**
     * @var array<DocumentProcessorInterface>
     */
    private array $processors;

    public function __construct(iterable $processors)
    {
        $this->processors = $processors instanceof \Traversable ? iterator_to_array($processors) : $processors;
    }

    public function addProcessor(DocumentProcessorInterface $processor): void
    {
        $this->processors []= $processor;
    }

    public function getProcessors(): array
    {
        return $this->processors;
    }
}
