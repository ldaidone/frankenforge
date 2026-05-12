<?php

declare(strict_types=1);


/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */
namespace FrankenForge\Tests\Unit\Core\View;

use FrankenForge\Core\View\View;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ViewTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/frankenforge_view_test_' . uniqid();
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->tmpDir . '/*.php'));
        rmdir($this->tmpDir);
    }

    private function writeView(string $name, string $content): string
    {
        $path = "{$this->tmpDir}/{$name}";
        file_put_contents($path, $content);
        return $path;
    }

    #[Test]
    public function it_renders_a_simple_view(): void
    {
        $view = new View();
        $path = $this->writeView('hello.html.php', 'Hello, <?= $name ?>!');

        $output = $view->render($path, ['name' => 'World']);

        self::assertSame('Hello, World!', $output);
    }

    #[Test]
    public function it_extracts_data_variables(): void
    {
        $view = new View();
        $path = $this->writeView('extract.html.php', '<?= $a ?>-<?= $b ?>');

        $output = $view->render($path, ['a' => 'foo', 'b' => 'bar']);

        self::assertSame('foo-bar', $output);
    }

    #[Test]
    public function it_returns_html_comment_when_view_is_empty(): void
    {
        $view = new View();
        $path = $this->writeView('empty.html.php', '');

        $output = $view->render($path, []);

        self::assertStringContainsString('View rendered empty', $output);
        self::assertStringContainsString('empty.html.php', $output);
    }

    #[Test]
    public function it_wraps_view_in_layout(): void
    {
        $view = new View();
        $layoutPath = $this->writeView('layout.html.php', '<html><?= $content ?></html>');
        $viewPath = $this->writeView('page.html.php', '<h1><?= $title ?></h1>');

        $output = $view->layout($layoutPath, $viewPath, ['title' => 'Dashboard']);

        self::assertSame('<html><h1>Dashboard</h1></html>', $output);
    }

    #[Test]
    public function layout_passes_content_and_data_to_layout(): void
    {
        $view = new View();
        $layoutPath = $this->writeView('shell.html.php', '<main><?= $content ?></main><footer><?= $year ?></footer>');
        $viewPath = $this->writeView('inner.html.php', '<p>body</p>');

        $output = $view->layout($layoutPath, $viewPath, ['year' => '2026']);

        self::assertSame('<main><p>body</p></main><footer>2026</footer>', $output);
    }

    #[Test]
    public function it_renders_view_without_data(): void
    {
        $view = new View();
        $path = $this->writeView('static.html.php', 'static content');

        $output = $view->render($path);

        self::assertSame('static content', $output);
    }
}
