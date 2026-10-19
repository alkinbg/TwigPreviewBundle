# Contributing

Thanks for considering a contribution to Twig Preview Bundle. Small, focused improvements are especially welcome.

## Before opening an issue

- Search existing [issues](https://github.com/alkinbg/TwigPreviewBundle/issues).
- For a bug, include the installed package version, PHP/Symfony/Twig versions, steps to reproduce, and the expected and actual results.
- For an enhancement, show the developer task it would simplify and a short example of the proposed usage.
- **Do not disclose security vulnerabilities publicly.** Use [SECURITY.md](SECURITY.md).

Use the [bug report](https://github.com/alkinbg/TwigPreviewBundle/issues/new?template=bug_report.yml) or [feature request](https://github.com/alkinbg/TwigPreviewBundle/issues/new?template=feature_request.yml) form.

## Local development

Requirements: PHP 8.2 or newer and Composer 2.

```bash
git clone https://github.com/alkinbg/TwigPreviewBundle.git
cd TwigPreviewBundle
composer install
composer validate --strict
composer test
```

Tests cover fixture validation, preview discovery, HTTP responses, and a real Symfony kernel. CI runs the suite on PHP 8.2, 8.3, and 8.4.

For a manual preview, install your working checkout into a separate Symfony application as a Composer [path repository](https://getcomposer.org/doc/05-repositories.md#path), using the installation instructions in the [README](README.md).

## Pull requests

1. Create a branch from `main`. Keep each PR focused on one improvement.
2. Add or update tests for behavior changes; update the README when usage changes.
3. Run `composer validate --strict` and `composer test` before submitting.
4. Avoid unrelated refactoring, additional dependencies, generated files, and changes to the release tag.
5. Explain what changed, why it matters, and how you verified it in the [pull request template](.github/PULL_REQUEST_TEMPLATE.md).

Please do not commit `vendor/`, `composer.lock`, local fixture secrets, or temporary test artifacts.

Contributions are provided under the project's [MIT license](LICENSE). See the [Code of Conduct](CODE_OF_CONDUCT.md) for participation guidelines.
