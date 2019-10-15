<?php

namespace Spatie\Export\Tests\Integration;

use Spatie\Export\Exporter;
use Illuminate\Support\Facades\Route;
use Spatie\Export\ExportServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class IntegrationTest extends BaseTestCase
{


    protected function setUp() : void
    {
        parent::setUp();

        exec('rm -r ' . __DIR__ . '/dist');

        Route::get('/', function () {
            return 'Home <a href="about">About</a>';
        });

        Route::get('about', function () {
            return 'About';
        });
    }

    /** @test */
    public function it_crawls_and_exports_routes()
    {
        app(Exporter::class)->export();

        $this->assertFileExists(__DIR__.'/dist/index.html');
        $this->assertEquals(
            'Home <a href="about">About</a>',
            file_get_contents(__DIR__.'/dist/index.html')
        );

        $this->assertFileExists(__DIR__.'/dist/about/index.html');
        $this->assertEquals(
            'About',
            file_get_contents(__DIR__.'/dist/about/index.html')
        );
    }

    /** @test */
    public function it_exports_paths()
    {
        app(Exporter::class)
            ->crawl(false)
            ->paths(['/', '/about'])
            ->export();

        $this->assertFileExists(__DIR__.'/dist/index.html');
        $this->assertEquals(
            'Home <a href="about">About</a>',
            file_get_contents(__DIR__.'/dist/index.html')
        );

        $this->assertFileExists(__DIR__.'/dist/about/index.html');
        $this->assertEquals(
            'About',
            file_get_contents(__DIR__.'/dist/about/index.html')
        );
    }

    /** @test */
    public function it_exports_included_files()
    {
        app(Exporter::class)
            ->includeFiles([__DIR__.'/public' => ''])
            ->export();

        $this->assertFileExists(__DIR__.'/dist/favicon.ico');
        $this->assertFileExists(__DIR__.'/dist/media/image.png');

        $this->assertFileNotExists(__DIR__.'/dist/index.php');
    }

    protected function getPackageProviders($app)
    {
        return [ExportServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('filesystems.disks.export', [
            'driver' => 'local',
            'root' => __DIR__.'/dist',
        ]);

        $app['config']->set('export.disk', 'export');
        $app['config']->set('export.include_files', []);
    }
}