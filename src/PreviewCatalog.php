<?php

declare(strict_types=1);

namespace Novora\TwigPreviewBundle;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class PreviewCatalog
{
    private const NAME_PATTERN = '/\A[A-Za-z0-9_-]{1,100}\z/D';

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    /** @return list<string> */
    public function names(): array
    {
        $paths = glob($this->projectDir.'/templates/previews/*.html.twig') ?: [];
        $names = [];

        foreach ($paths as $path) {
            if (!is_file($path)) {
                continue;
            }

            $name = substr(basename($path), 0, -strlen('.html.twig'));
            if (self::validName($name)) {
                $names[] = $name;
            }
        }

        sort($names, SORT_STRING);

        return $names;
    }

    public function templateFor(string $name): ?string
    {
        if (!self::validName($name)) {
            return null;
        }

        $path = $this->projectDir.'/templates/previews/'.$name.'.html.twig';

        return is_file($path) ? 'previews/'.$name.'.html.twig' : null;
    }

    private static function validName(string $name): bool
    {
        return preg_match(self::NAME_PATTERN, $name) === 1;
    }
}
