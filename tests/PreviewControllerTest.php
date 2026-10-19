<?php

declare(strict_types=1);

namespace Novora\TwigPreviewBundle\Tests;

use Novora\TwigPreviewBundle\Controller\PreviewController;
use Novora\TwigPreviewBundle\Fixture\JsonFixtureLoader;
use Novora\TwigPreviewBundle\PreviewCatalog;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class PreviewControllerTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir().'/novora-controller-'.bin2hex(random_bytes(6));
        mkdir($this->projectDir.'/templates/previews', 0700, true);
        mkdir($this->projectDir.'/config/twig_previews', 0700, true);
    }

    protected function tearDown(): void
    {
        foreach (['/templates/previews', '/config/twig_previews'] as $dir) {
            foreach (glob($this->projectDir.$dir.'/*') ?: [] as $path) {
                if (is_file($path)) {
                    unlink($path);
                }
            }
            rmdir($this->projectDir.$dir);
        }

        rmdir($this->projectDir.'/templates');
        rmdir($this->projectDir.'/config');
        rmdir($this->projectDir);
    }

    public function testPreviewRendersWithJsonDataAndNoCache(): void
    {
        file_put_contents($this->projectDir.'/templates/previews/card.html.twig', '<h1>{{ title }}</h1>');
        file_put_contents($this->projectDir.'/config/twig_previews/card.json', '{"title":"Hello <World>"}');

        $response = $this->controller()->show('card');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('<h1>Hello &lt;World&gt;</h1>', $response->getContent());
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        self::assertSame('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
    }

    public function testIndexLinksAvailablePreviews(): void
    {
        file_put_contents($this->projectDir.'/templates/previews/hello.html.twig', '<p>Hi</p>');

        $content = $this->controller()->index()->getContent();

        self::assertIsString($content);
        self::assertStringContainsString('/_dev/twig-preview/hello', $content);
    }

    public function testPreviewWithoutFixtureRenders(): void
    {
        file_put_contents($this->projectDir.'/templates/previews/empty.html.twig', 'Hello!');

        self::assertSame('Hello!', $this->controller()->show('empty')->getContent());
    }

    public function testMissingPreviewRespondsNotFound(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->controller()->show('unknown');
    }

    public function testMalformedFixtureRespondsBadRequest(): void
    {
        file_put_contents($this->projectDir.'/templates/previews/card.html.twig', '<p>Hi</p>');
        file_put_contents($this->projectDir.'/config/twig_previews/card.json', '{not json');

        $this->expectException(BadRequestHttpException::class);
        $this->controller()->show('card');
    }

    public function testProductionEnvironmentRefusesIndexAndPreview(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->controller('prod')->index();
    }

    public function testProductionEnvironmentRefusesPreview(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->controller('prod')->show('card');
    }

    private function controller(string $environment = 'dev'): PreviewController
    {
        $routes = new RouteCollection();
        $routes->add('novora_twig_preview_show', new Route('/_dev/twig-preview/{name}'));

        return new PreviewController(
            new Environment(new FilesystemLoader($this->projectDir.'/templates'), ['autoescape' => 'html']),
            new JsonFixtureLoader($this->projectDir),
            new PreviewCatalog($this->projectDir),
            new UrlGenerator($routes, new RequestContext()),
            $environment,
        );
    }
}
