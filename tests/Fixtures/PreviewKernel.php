<?php

declare(strict_types=1);

namespace Novora\TwigPreviewBundle\Tests\Fixtures;

use Novora\TwigPreviewBundle\NovoraTwigPreviewBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class PreviewKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct(
        private readonly string $projectDirectory,
        string $environment,
    ) {
        parent::__construct($environment, true);
    }

    public function getProjectDir(): string
    {
        return $this->projectDirectory;
    }

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TwigBundle();

        if ($this->getEnvironment() === 'dev') {
            yield new NovoraTwigPreviewBundle();
        }
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', ['secret' => 'preview-test-only-secret']);
        $container->extension('twig', [
            'default_path' => '%kernel.project_dir%/templates',
        ]);
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        if ($this->getEnvironment() === 'dev') {
            $routes->import(__DIR__.'/../../config/routes.php');
        }
    }
}
