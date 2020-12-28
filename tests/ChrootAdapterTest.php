<?php
/**
 * @author    Greg Roach <greg@subaqua.co.uk>
 * @copyright Copyright (c) 2020
 * @licence   MIT
 */

use Fisharebest\Flysystem\Adapter\ChrootAdapter;
use League\Flysystem\AdapterTestUtilities\FilesystemAdapterTestCase;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
use League\Flysystem\PathTraversalDetected;

/**
 * Test the ChrootAdapter class.
 */
class ChrootAdapterTest extends FilesystemAdapterTestCase
{
    protected static function createFilesystemAdapter(): FilesystemAdapter
    {
        return new ChrootAdapter(new Filesystem(new InMemoryFilesystemAdapter()), 'prefix');
    }

    public function testChrootFileEquivalences(): void
    {
        $master = new Filesystem(new InMemoryFilesystemAdapter());
        $chroot = new Filesystem(new ChrootAdapter($master, 'foo'));

        $master->write('foo/bar.txt', 'FOO');
        self::assertTrue($chroot->fileExists('bar.txt'));
        self::assertSame($chroot->read('bar.txt'), 'FOO');

        $chroot->write('qux.txt', 'QUX');
        self::assertTrue($master->fileExists('foo/qux.txt'));
        self::assertSame($master->read('foo/qux.txt'), 'QUX');
    }

    public function testCannotEscapeChroot(): void
    {
        $master = new Filesystem(new InMemoryFilesystemAdapter());
        $chroot = new Filesystem(new ChrootAdapter($master, 'foo'));

        $master->write('foo.txt', 'FOO');

        $this->expectException(PathTraversalDetected::class);
        $chroot->fileExists('../foo.txt');
    }
}
