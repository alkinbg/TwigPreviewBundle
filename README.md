# Twig Preview Bundle

Preview a Twig template **inside your Symfony application**, using its real Twig extensions, components, themes and configuration. No temporary controller or route for each preview.

**Stable release:** 1.0.0 · **Requirements:** PHP 8.2+, Symfony 7.4 or 8.x, Twig 3.

## What you get

- A preview index at `/_dev/twig-preview`, listing `templates/previews/*.html.twig`.
- A preview page at `/_dev/twig-preview/{name}`, rendered with your application's Twig environment.
- Optional JSON fixture values from `config/twig_previews/{name}.json`.
- No database, JavaScript, generated application files, or code changes when refreshing a preview.

The package **only works in the `dev` environment**. Do not expose your development server publicly.

## Installation

### From GitHub

Until this package is registered on Packagist, add its public Git repository to Composer:

```bash
composer config repositories.twig-preview vcs https://github.com/alkinbg/TwigPreviewBundle.git
composer require --dev 'alkin/twig-preview-bundle:^1.0'
```

Once it is available on Packagist, only `composer require --dev 'alkin/twig-preview-bundle:^1.0'` is needed.

Symfony Flex may register the bundle automatically. Check `config/bundles.php`, and make sure it is enabled **only in development** (optionally also in `test`), not `all`:

```php
// config/bundles.php
Novora\TwigPreviewBundle\NovoraTwigPreviewBundle::class => ['dev' => true],
```

Import the preview routes only in development:

```php
<?php
// config/routes/dev/novora_twig_preview.php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('@NovoraTwigPreviewBundle/config/routes.php');
};
```

If the application already cached its routes, run:

```bash
php bin/console cache:clear --env=dev
```

### Create your first preview

Copy `examples/product.html.twig` into `templates/previews/product.html.twig` and `examples/product.json` into `config/twig_previews/product.json`.

Then open:

```text
http://localhost:8000/_dev/twig-preview
http://localhost:8000/_dev/twig-preview/product
```

Edit your template or JSON and refresh the page.

### Preview an existing component

The preview itself is an ordinary Twig template. It can extend a layout, include other templates, or render Twig Components already configured in your application:

```twig
{# templates/previews/product_card.html.twig #}
{% include 'components/_product_card.html.twig' with {product: product} only %}
```

The matching data file is `config/twig_previews/product_card.json`:

```json
{
  "product": {"name": "Demo product", "price": 19.99}
}
```

Only JSON objects are accepted at the root of fixture files. Fixtures are optional, limited to **128 KiB** and a maximum JSON nesting depth of 32. Preview names may use ASCII letters, digits, `-` and `_` (1–100 characters). Template names must be flat files matching `*.html.twig` inside `templates/previews`.

## Limitations and security

- JSON fixtures contain scalar values and arrays. They cannot instantiate Doctrine entities, Symfony Forms or arbitrary PHP objects. A component needing those values requires an application-specific adapter or example.
- A preview uses the application's *real* Twig services. Although this bundle never writes to a database, a template that invokes application services might have side effects.
- Route matching and the controller enforce `dev`-only access. Responses set `Cache-Control: private, no-store` and `X-Robots-Tag: noindex, nofollow`; these are **not authentication**. Keep the development server private.
- The package does not provide browser live reload: refresh the page after changes.

## Development

```bash
composer install
composer validate --strict
composer test
```

CI tests PHP 8.2, 8.3 and 8.4. The test suite includes real Symfony HTTP/kernel integration tests.

## License

MIT. Copyright © 2026 Novora Labs.
