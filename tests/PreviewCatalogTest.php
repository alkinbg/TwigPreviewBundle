<?php

declare(strict_types=1);

namespace Novora\TwigPreviewBundle\Tests;

use Novora\TwigPreviewBundle\PreviewCatalog;
use PHPUnit\Framework\TestCase;

final class PreviewCatalogTest extends TestCase
{
    private string $projectDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir().'/novora-catalog-'.bin2hex(random_bytes(6));
        mkdir($this->projectDir.'/templates/previews', 0700, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->projectDir.'/templates/previews/*') ?: [] as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        rmdir($this->projectDir.'/templates/previews');
        rmdir($this->projectDir.'/templates');
        rmdir($this->projectDir);
    }

    public function testFindsNamedTemplatesInSortedOrder(): void
    {
        file_put_contents($this->projectDir.'/templates/previews/zeta.html.twig', 'Hello');
        file_put_contents($this->projectDir.'/templates/previews/alpha.html.twig', 'Hello');
        file_put_contents($this->projectDir.'/templates/previews/not-a-preview.twig', 'Hello');
        file_put_contents($this->projectDir.'/templates/previews/has.dot.html.twig', 'Hello');

        $catalog = new PreviewCatalog($this->projectDir);

        self::assertSame(['alpha', 'zeta'], $catalog->names());
        self::assertSame('previews/alpha.html.twig', $catalog->templateFor('alpha'));
        self::assertNull($catalog->templateFor('missing'));
    }

    public function testRejectsTraversalAndInvalidNames(): void
    {
        $catalog = new PreviewCatalog($this->projectDir);

        self::assertNull($catalog->templateFor('../.env'));
        self::assertNull($catalog->templateFor('foo/bar'));
        self::assertNull($catalog->templateFor(''));
    }
}
