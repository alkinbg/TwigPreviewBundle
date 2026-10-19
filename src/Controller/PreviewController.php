<?php

declare(strict_types=1);

namespace Novora\TwigPreviewBundle\Controller;

use InvalidArgumentException;
use Novora\TwigPreviewBundle\Fixture\JsonFixtureLoader;
use Novora\TwigPreviewBundle\PreviewCatalog;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final readonly class PreviewController
{
    public function __construct(
        private Environment $twig,
        private JsonFixtureLoader $fixtures,
        private PreviewCatalog $catalog,
        private UrlGeneratorInterface $urlGenerator,
        #[Autowire('%kernel.environment%')]
        private string $environment,
    ) {
    }

    public function index(): Response
    {
        $this->ensureDevelopment();

        $items = '';
        foreach ($this->catalog->names() as $name) {
            $label = htmlspecialchars($name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $url = htmlspecialchars(
                $this->urlGenerator->generate('novora_twig_preview_show', ['name' => $name]),
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8',
            );
            $items .= sprintf('<li><a href="%s">%s</a></li>', $url, $label);
        }

        if ($items === '') {
            $items = '<li>No previews found. Add a file in <code>templates/previews/</code>.</li>';
        }

        $html = '<!doctype html><html lang="en"><head><meta charset="utf-8">'
            .'<meta name="viewport" content="width=device-width, initial-scale=1">'
            .'<title>Twig Previews</title></head><body>'
            .'<main><h1>Twig Previews</h1><ul>'.$items.'</ul></main>'
            .'</body></html>';

        return $this->response($html);
    }

    public function show(string $name): Response
    {
        $this->ensureDevelopment();

        $template = $this->catalog->templateFor($name);
        if ($template === null) {
            throw new NotFoundHttpException('Preview not found.');
        }

        try {
            $context = $this->fixtures->load($name);
        } catch (InvalidArgumentException $exception) {
            throw new BadRequestHttpException($exception->getMessage(), $exception);
        }

        // Let Twig exceptions surface in Symfony's dev error page, where they are useful.
        return $this->response($this->twig->render($template, $context));
    }

    private function ensureDevelopment(): void
    {
        if ($this->environment !== 'dev') {
            throw new NotFoundHttpException();
        }
    }

    private function response(string $html): Response
    {
        return new Response($html, Response::HTTP_OK, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
            'X-Robots-Tag' => 'noindex, nofollow',
        ]);
    }
}
