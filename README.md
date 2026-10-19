# Twig Preview Bundle

Preview Twig templates in a **real Symfony application** without creating a temporary controller and route for each template.

> **Status:** Experimental. Intended for local development only; not published to Packagist yet.

## What it does

- Lists all `templates/previews/*.html.twig` files at `/_dev/twig-preview`.
- Renders a preview by name at `/_dev/twig-preview/{name}` using the application's real Twig environment.
- Optionally supplies variables from `config/twig_previews/{name}.json`.
- Does not write to the database, expose a fixture editor, or require JavaScript.

## Install locally

Use a Composer [path repository](https://getcomposer.org/doc/05-repositories.md#path) or a Git VCS repository until the package is published to Packagist.

Install this package as a development dependency. Symfony Flex may register it automatically for development and tests. If it does not, register it **only for development**:

```php
// config/bundles.php
Novora\TwigPreviewBundle\NovoraTwigPreviewBundle::class => ['dev' => true],
```

Then import the two preview routes **only in dev**:

```yaml
# config/routes/dev/novora_twig_preview.yaml
novora_twig_preview:
  resource: '@NovoraTwigPreviewBundle/config/routes.yaml'
```

If you added the routes after Symfony had already warmed its cache, run `php bin/console cache:clear --env=dev`.

Copy `examples/product.html.twig` to `templates/previews/product.html.twig` and `examples/product.json` to `config/twig_previews/product.json` (adjust the base layout as needed).

Visit:

```text
http://localhost:8000/_dev/twig-preview
http://localhost:8000/_dev/twig-preview/product
```

The preview template can include a real component from your application:

```twig
{# templates/previews/product_card.html.twig #}
{% include 'components/_product_card.html.twig' with {product: product} only %}
```

In `config/twig_previews/product_card.json`:

```json
{
  "product": {"name": "Example", "price": 19.99}
}
```

The JSON fixture is **optional**; without one, Twig receives an empty preview context. Fixture names must match preview template names and contain only ASCII letters, digits, `-`, or `_` (up to 100 characters). Fixtures must be JSON objects no larger than 128 KiB.

## Limitations

Fixtures are JSON, so they cannot model real PHP objects, methods, Doctrine entities, or Symfony Forms. Previews requiring those should use a purpose-built example page. The bundle doesn't watch files; refresh the browser to see changes.

## Safety

**Never enable the bundle or its routes outside dev.** The controller also refuses to run unless `kernel.environment` is exactly `dev`. Keep dev servers private. Even with fixed template names, rendering a real application's Twig templates may access application services or data. Do not put credentials or real user data in fixtures. Responses are marked `no-store` and `noindex`.

## Development

```bash
composer install
composer validate --strict
composer test
```

## License

MIT. Copyright 2026 Novora Labs.
