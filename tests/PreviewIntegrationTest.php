<?php

declare(strict_types=1);

namespace Novora\TwigPreviewBundle\Tests;

use FilesystemIterator;
use Novora\TwigPreviewBundle\Tests\Fixtures\PreviewKernel;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PreviewIntegrationTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir().'/novora-preview-integration-'.bin2hex(random_bytes(8));
        mkdir($this->projectDir.'/templates/previews', 0700, true);
        mkdir($this->projectDir.'/config/twig_previews', 0700, true);
    }

    protected function tearDown(): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->projectDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $file) {
            if ($file->isDir() && !$file->isLink()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }

        rmdir($this->projectDir);
    }

    public function testPreviewUsesRealTwigAndFixtureData(): void
    {
        file_put_contents($this->projectDir.'/templates/base.html.twig', '<html><body>{% block body %}{% endblock %}</body></html>');
        file_put_contents(
            $this->projectDir.'/templates/previews/card.html.twig',
            '{% extends "base.html.twig" %}{% block body %}<p>{{ title }}</p>{% endblock %}',
        );
        file_put_contents($this->projectDir.'/config/twig_previews/card.json', '{"title":"Hello & goodbye"}');

        $response = $this->request('/_dev/twig-preview/card');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('<html><body><p>Hello &amp; goodbye</p></body></html>', $response->getContent());
        self::assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        self::assertSame('noindex, nofollow', $response->headers->get('X-Robots-Tag'));
    }

    public function testIndexDiscoversTemplates(): void
    {
        file_put_contents($this->projectDir.'/templates/previews/example.html.twig', '<p>Example</p>');

        $response = $this->request('/_dev/twig-preview');

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertStringContainsString('/_dev/twig-preview/example', (string) $response->getContent());
    }

    public function testMissingTemplateReturnsNotFound(): void
    {
        self::assertSame(Response::HTTP_NOT_FOUND, $this->request('/_dev/twig-preview/missing')->getStatusCode());
    }

    public function testMalformedFixtureReturnsBadRequest(): void
    {
        file_put_contents($this->projectDir.'/templates/previews/bad.html.twig', '<p>Bad</p>');
        file_put_contents($this->projectDir.'/config/twig_previews/bad.json', '{bad');

        self::assertSame(Response::HTTP_BAD_REQUEST, $this->request('/_dev/twig-preview/bad')->getStatusCode());
    }

    public function testRoutesAreNotAvailableInProduction(): void
    {
        file_put_contents($this->projectDir.'/templates/previews/card.html.twig', '<p>Card</p>');

        self::assertSame(Response::HTTP_NOT_FOUND, $this->request('/_dev/twig-preview/card', 'prod')->getStatusCode());
    }

    private function request(string $path, string $environment = 'dev'): Response
    {
        $kernel = new PreviewKernel($this->projectDir, $environment);
        $kernel->boot();

        try {
            return $kernel->handle(Request::create('http://localhost'.$path));
        } finally {
            $kernel->shutdown();
        }
    }
}
