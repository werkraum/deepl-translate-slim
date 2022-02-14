<?php

use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Werkraum\DeeplTranslate\DocumentProcessor\DocumentProcessorChain;
use Werkraum\DeeplTranslate\DocumentProcessor\DocumentProcessorInterface;

return static function (ContainerConfigurator $configurator, ContainerBuilder $containerBuilder): void {
    $containerBuilder->registerForAutoconfiguration(DocumentProcessorInterface::class)->addTag('deepl.processor');
    $containerBuilder->addCompilerPass(new class() implements CompilerPassInterface {
        public function process(ContainerBuilder $container): void
        {
            $container->getDefinition(DocumentProcessorChain::class)
                ->addArgument(new TaggedIteratorArgument('deepl.processor', null, null, false, 'getPriority'));
        }
    });
};
