<?php

declare(strict_types=1);

namespace Novora\TwigPreviewBundle\Tests;

use InvalidArgumentException;
use Novora\TwigPreviewBundle\Fixture\JsonFixtureLoader;
use PHPUnit\Framework\TestCase;

final class JsonFixtureLoaderTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir().'/novora-fixtures-'.bin2hex(random_bytes(6));
        mkdir($this->projectDir.'/config/twig_previews', 0700, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->projectDir.'/config/twig_previews/*') ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        rmdir($this->projectDir.'/config/twig_previews');
        rmdir($this->projectDir.'/config');
        rmdir($this->projectDir);
    }

    public function testMissingFixtureReturnsEmptyContext(): void
    {
        self::assertSame([], new JsonFixtureLoader($this->projectDir)->load('missing'));
    }

    public function testValidJsonObjectLoadsAsTwigContext(): void
    {
        file_put_contents($this->projectDir.'/config/twig_previews/card.json', '{"title":"Hello","product":{"price":15}}');

        self::assertSame(
            ['title' => 'Hello', 'product' => ['price' => 15]],
            new JsonFixtureLoader($this->projectDir)->load('card'),
        );
    }

    public function testRejectsTraversal(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new JsonFixtureLoader($this->projectDir)->load('../.env');
    }

    public function testRejectsInvalidJson(): void
    {
        file_put_contents($this->projectDir.'/config/twig_previews/bad.json', '{bad');
        $this->expectException(InvalidArgumentException::class);
        new JsonFixtureLoader($this->projectDir)->load('bad');
    }

    public function testRejectsListInsteadOfObject(): void
    {
        file_put_contents($this->projectDir.'/config/twig_previews/list.json', '[1,2,3]');
        $this->expectException(InvalidArgumentException::class);
        new JsonFixtureLoader($this->projectDir)->load('list');
    }

    public function testRejectsOversizedFixture(): void
    {
        file_put_contents($this->projectDir.'/config/twig_previews/huge.json', '{"data":"'.str_repeat('x', 131072).'"}');
        $this->expectException(InvalidArgumentException::class);
        new JsonFixtureLoader($this->projectDir)->load('huge');
    }
}
