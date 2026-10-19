<?php

declare(strict_types=1);

namespace Novora\TwigPreviewBundle\Fixture;

use InvalidArgumentException;
use JsonException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class JsonFixtureLoader
{
    private const MAX_BYTES = 131072;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws InvalidArgumentException If the name or JSON document is invalid.
     */
    public function load(string $name): array
    {
        if (preg_match('/\A[A-Za-z0-9_-]{1,100}\z/D', $name) !== 1) {
            throw new InvalidArgumentException('Invalid preview name.');
        }

        $path = $this->projectDir.'/config/twig_previews/'.$name.'.json';
        if (!file_exists($path)) {
            return [];
        }

        if (!is_file($path) || !is_readable($path)) {
            throw new InvalidArgumentException(sprintf('Preview fixture "%s" is not readable.', $name));
        }

        $size = filesize($path);
        if ($size === false || $size > self::MAX_BYTES) {
            throw new InvalidArgumentException('Preview fixtures must be at most 128 KiB.');
        }

        $json = file_get_contents($path);
        if ($json === false || strlen($json) > self::MAX_BYTES) {
            throw new InvalidArgumentException(sprintf('Could not read preview fixture "%s".', $name));
        }

        try {
            $value = json_decode($json, false, 32, JSON_THROW_ON_ERROR);
            if (!$value instanceof \stdClass) {
                throw new InvalidArgumentException('Preview fixture JSON must have an object at the root.');
            }

            /** @var array<string, mixed> $context */
            $context = json_decode($json, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidArgumentException(sprintf('Invalid JSON in preview fixture "%s".', $name), 0, $exception);
        }

        return $context;
    }
}
