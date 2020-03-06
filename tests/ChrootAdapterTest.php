<?php
/**
 * @author    Greg Roach <greg@subaqua.co.uk>
 * @copyright Copyright (c) 2020
 * @licence   MIT
 */

use Fisharebest\Flysystem\Adapter\ChrootAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\Memory\MemoryAdapter;
use PHPUnit\Framework\TestCase;

/**
 * Test the ChrootAdapter class.
 */
class ChrootAdapterTest extends TestCase
{
    /** @var \League\Flysystem\Filesystem */
    protected $master;

    /** @var \League\Flysystem\Filesystem */
    protected $chroot;

    /**
     * Create master and chroot filesystems.  Almost every test will need these.
     *
     * @return void
     */
    public function setUp()
    {
        $this->master = new Filesystem(new MemoryAdapter());
        $this->master->write('foo/hello.txt', 'hello world');
        $this->master->write('foo/bar/goodbye.txt', 'goodbye world');

        $this->chroot = new Filesystem(new ChrootAdapter($this->master, 'foo'));
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::__construct
     * @return void
     */
    public function testConstructor()
    {
        $adapter = new ChrootAdapter($this->master);

        $this->assertInstanceOf(AdapterInterface::class, $adapter);
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::has
     * @return void
     */
    public function testHas()
    {
        $this->assertTrue($this->chroot->has('hello.txt'));
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::read
     * @return void
     */
    public function testRead()
    {
        $this->assertSame('hello world', $this->chroot->read('hello.txt'));
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::readStream
     * @return void
     */
    public function testReadStream()
    {
        $resource = $this->chroot->readStream('hello.txt');
        $content  = stream_get_contents($resource);
        $this->assertSame('hello world', $content);
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::listContents
     * @return void
     */
    public function testListContents()
    {
        $contents = $this->chroot->listContents('', false);
        $this->assertCount(2, $contents);

        $contents = $this->chroot->listContents('', true);
        $this->assertCount(3, $contents);

        $contents = $this->chroot->listContents('bar', false);
        $this->assertCount(1, $contents);
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::getMetadata
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::removePrefixFromMetadata
     * @return void
     */
    public function testGetMetadata()
    {
        $metadata = $this->chroot->getMetadata('hello.txt');
        $this->assertSame('file', $metadata['type']);

        $metadata = $this->chroot->getMetadata('bar');
        $this->assertSame('dir', $metadata['type']);

        $metadata = $this->chroot->getMetadata('bar/');
        $this->assertSame('dir', $metadata['type']);

        $metadata = $this->chroot->getMetadata('bar/goodbye.txt');
        $this->assertSame('file', $metadata['type']);
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::getSize
     * @return void
     */
    public function testGetSize()
    {
        $expected = $this->master->getSize('foo/hello.txt');
        $actual   = $this->chroot->getSize('hello.txt');
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::getMimetype
     * @return void
     */
    public function testGetMimetype()
    {
        $expected = $this->master->getMimetype('foo/hello.txt');
        $actual   = $this->chroot->getMimetype('hello.txt');
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::getTimestamp
     * @return void
     */
    public function testGetTimestamp()
    {
        $expected = $this->master->getTimestamp('foo/hello.txt');
        $actual   = $this->chroot->getTimestamp('hello.txt');
        $this->assertSame($expected, $actual);
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::getVisibility
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::setVisibility
     * @return void
     */
    public function testGetSetVisibility()
    {
        $this->assertSame(AdapterInterface::VISIBILITY_PUBLIC, $this->chroot->getVisibility('hello.txt'));
        $this->chroot->setVisibility('hello.txt', AdapterInterface::VISIBILITY_PRIVATE);
        $this->assertSame(AdapterInterface::VISIBILITY_PRIVATE, $this->chroot->getVisibility('hello.txt'));
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::write
     * @return void
     */
    public function testWrite()
    {
        $this->chroot->write('folder/fish.txt', 'fish');
        $this->assertSame('fish', $this->chroot->read('folder/fish.txt'));
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::writeStream
     * @return void
     */
    public function testWriteStream()
    {
        $resource = fopen('php://memory', 'w+');
        fwrite($resource, 'fish');
        rewind($resource);

        $this->chroot->writeStream('folder/fish.txt', $resource);
        $this->assertSame('fish', $this->chroot->read('folder/fish.txt'));
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::update
     * @return void
     */
    public function testUpdate()
    {
        $this->chroot->update('hello.txt', 'HELLO WORLD');
        $this->assertSame('HELLO WORLD', $this->chroot->read('hello.txt'));
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::updateStream
     * @return void
     */
    public function testUpdateStream()
    {
        $resource = fopen('php://memory', 'w+');
        fwrite($resource, 'HELLO WORLD');
        rewind($resource);

        $this->chroot->updateStream('hello.txt', $resource);
        $this->assertSame('HELLO WORLD', $this->chroot->read('hello.txt'));
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::rename
     * @return void
     */
    public function testRename()
    {
        $this->chroot->rename('hello.txt', 'NEWPATH/HELLO.txt');
        $this->assertFalse($this->chroot->has('hello.txt'));
        $this->assertTrue($this->chroot->has('NEWPATH/HELLO.txt'));
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::copy
     * @return void
     */
    public function testCopy()
    {
        $this->chroot->copy('hello.txt', 'NEWPATH/HELLO.txt');
        $this->assertTrue($this->chroot->has('hello.txt'));
        $this->assertTrue($this->chroot->has('NEWPATH/HELLO.txt'));
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::delete
     * @return void
     */
    public function testDelete()
    {
        $this->chroot->delete('hello.txt');
        $this->assertFalse($this->chroot->has('hello.txt'));
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::deleteDir
     * @return void
     */
    public function testDeleteDir()
    {
        $this->chroot->deleteDir('bar');
        $this->assertFalse($this->chroot->has('bar'));
    }

    /**
     * @covers \Fisharebest\Flysystem\Adapter\ChrootAdapter::createDir
     * @return void
     */
    public function testCreateDir()
    {
        $this->assertFalse($this->chroot->has('newdir'));
        $this->chroot->createDir('newdir');
        $this->assertTrue($this->chroot->has('newdir'));
    }

    /**
     * @coversNothing
     * @return void
     */
    public function testFilesAddedToMasterAfterChrootWasCreated()
    {
        $this->assertFalse($this->chroot->has('qux/hello.txt'));
        $this->master->write('foo/qux/hello.txt', '');
        $this->assertTrue($this->chroot->has('qux/hello.txt'));
    }
}
