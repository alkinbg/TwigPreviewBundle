<?php

declare(strict_types=1);

namespace Novora\TwigPreviewBundle;

use Novora\TwigPreviewBundle\Controller\PreviewController;
use Novora\TwigPreviewBundle\Fixture\JsonFixtureLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class NovoraTwigPreviewBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();
        $services->set(PreviewCatalog::class)->autowire();
        $services->set(JsonFixtureLoader::class)->autowire();
        $services->set(PreviewController::class)
            ->autowire()
            ->tag('controller.service_arguments');
    }
}
