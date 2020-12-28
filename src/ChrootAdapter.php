<?php
/**
 * @author    Greg Roach <greg@subaqua.co.uk>
 * @copyright Copyright (c) 2020
 * @licence   MIT
 */

namespace Fisharebest\Flysystem\Adapter;

use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\StorageAttributes;
use ReflectionException;
use ReflectionProperty;

use function strlen;
use function substr;

/**
 * Create a subtree from an existing filesystem.
 */
class ChrootAdapter implements FilesystemAdapter
{
    /** @var FilesystemAdapter */
    public $adapter;

    /** @var string */
    private $prefix;

    /**
     * ChrootAdapter constructor.
     *
     * @param FilesystemOperator $filesystem
     * @param string             $prefix e.g. 'some/prefix'
     *
     * @throws ReflectionException
     */
    public function __construct(FilesystemOperator $filesystem, string $prefix = '')
    {
        // Since Flysystem 2.0, the adapter is private.  Use reflection to get it.
        $property = new ReflectionProperty($filesystem, 'adapter');
        $property->setAccessible(true);
        $this->adapter = $property->getValue($filesystem);

        $this->prefix = trim($prefix, '/') . '/';
    }

    /**
     * @param string $path
     *
     * @return bool
     * @throws FilesystemException
     */
    public function fileExists(string $path): bool
    {
        return $this->adapter->fileExists($this->prefix . $path);
    }

    /**
     * @param string $path
     * @param string $contents
     * @param Config $config
     *
     * @throws FilesystemException
     */
    public function write(string $path, string $contents, Config $config): void
    {
        $this->adapter->write($this->prefix . $path, $contents, $config);
    }

    /**
     * @param string   $path
     * @param resource $contents
     * @param Config   $config
     *
     * @throws FilesystemException
     */
    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->adapter->writeStream($this->prefix . $path, $contents, $config);
    }

    /**
     * @param string $path
     *
     * @return string
     * @throws FilesystemException
     */
    public function read(string $path): string
    {
        return $this->adapter->read($this->prefix . $path);
    }

    /**
     * @param string $path
     *
     * @return resource
     * @throws FilesystemException
     */
    public function readStream(string $path)
    {
        return $this->adapter->readStream($this->prefix . $path);
    }

    /**
     * @param string $path
     *
     * @throws FilesystemException
     */
    public function delete(string $path): void
    {
        $this->adapter->delete($this->prefix . $path);
    }

    /**
     * @param string $path
     *
     * @throws FilesystemException
     */
    public function deleteDirectory(string $path): void
    {
        $this->adapter->deleteDirectory($this->prefix . $path);
    }

    /**
     * @param string $path
     * @param Config $config
     *
     * @throws FilesystemException
     */
    public function createDirectory(string $path, Config $config): void
    {
        $this->adapter->createDirectory($this->prefix . $path, $config);
    }

    /**
     * @param string $path
     * @param bool   $deep
     *
     * @return iterable
     * @throws FilesystemException
     */
    public function listContents(string $path, bool $deep): iterable
    {
        $storage_attributes = $this->adapter->listContents($this->prefix . $path, $deep);

        foreach ($storage_attributes as $storage_attribute) {
            $attributes = $storage_attribute->jsonSerialize();

            $attributes[StorageAttributes::ATTRIBUTE_PATH] = substr($attributes[StorageAttributes::ATTRIBUTE_PATH], strlen($this->prefix));

            if ($storage_attribute instanceof DirectoryAttributes) {
                yield DirectoryAttributes::fromArray($attributes);
            }

            if ($storage_attribute instanceof FileAttributes) {
                yield FileAttributes::fromArray($attributes);
            }
        }
    }

    /**
     * @param string $source
     * @param string $destination
     * @param Config $config
     *
     * @throws FilesystemException
     */
    public function move(string $source, string $destination, Config $config): void
    {
        $this->adapter->move($this->prefix . $source, $this->prefix . $destination, $config);
    }

    /**
     * @param string $source
     * @param string $destination
     * @param Config $config
     *
     * @throws FilesystemException
     */
    public function copy(string $source, string $destination, Config $config): void
    {
        $this->adapter->copy($this->prefix . $source, $this->prefix . $destination, $config);
    }

    /**
     * @param string $path
     *
     * @return FileAttributes
     * @throws FilesystemException
     */
    public function lastModified(string $path): FileAttributes
    {
        return $this->adapter->lastModified($this->prefix . $path);
    }

    /**
     * @param string $path
     *
     * @return FileAttributes
     * @throws FilesystemException
     */
    public function fileSize(string $path): FileAttributes
    {
        return $this->adapter->fileSize($this->prefix . $path);
    }

    /**
     * @param string $path
     *
     * @return FileAttributes
     * @throws FilesystemException
     */
    public function mimeType(string $path): FileAttributes
    {
        return $this->adapter->mimeType($this->prefix . $path);
    }

    /**
     * @param string $path
     * @param string $visibility
     *
     * @throws FilesystemException
     */
    public function setVisibility(string $path, string $visibility): void
    {
        $this->adapter->setVisibility($this->prefix . $path, $visibility);
    }

    /**
     * @param string $path
     *
     * @return FileAttributes
     * @throws FilesystemException
     */
    public function visibility(string $path): FileAttributes
    {
        return $this->adapter->visibility($this->prefix . $path);
    }
}
