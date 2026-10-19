<?php

declare(strict_types=1);

use Novora\TwigPreviewBundle\Controller\PreviewController;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('novora_twig_preview_index', '/_dev/twig-preview')
        ->controller([PreviewController::class, 'index'])
        ->methods(['GET']);

    $routes->add('novora_twig_preview_show', '/_dev/twig-preview/{name}')
        ->controller([PreviewController::class, 'show'])
        ->requirements(['name' => '[A-Za-z0-9_-]{1,100}'])
        ->methods(['GET']);
};
