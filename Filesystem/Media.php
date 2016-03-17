<?php
namespace RabbitCMS\FileManager\FileSystem;

use finfo as Finfo;
use InvalidArgumentException;
use LogicException;
use League\Flysystem\Directory;
use League\Flysystem\File;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\Handler;
use League\Flysystem\Plugin\PluggableTrait;
use League\Flysystem\RootViolationException;
use League\Flysystem\Util;
use \RabbitCMS\FileManager\Entities\Media as MediaEntity;

class Media implements FilesystemInterface
{
    use PluggableTrait;

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function query()
    {
        return MediaEntity::query()//->where('owner_id', '=', $this->ownerId)
            ;
    }

    /**
     * @param string $path
     * @param bool   $require
     *
     * @throws FileExistsException
     * @throws FileNotFoundException
     *
     * @return null|MediaEntity
     */
    protected function find($path, $require = null)
    {
        static $cache = [];
        $path = Util::normalizePath($path);

        if (array_key_exists($path, $cache)) {
            $media = $cache[$path];
        } else {
            $parts = explode('/', $path);
            $media = null;
            $parent = null;
            do {
                /* @var MediaEntity $media ; */
                $media = $this->query()
                    ->where([
                        'parent_id' => $parent,
                        'caption'   => array_shift($parts),
                    ])
                    ->first();
                if ($media !== null) {
                    $parent = $media->getKey();
                }
            } while (count($parts) && $media);
            // $cache[$path] = $media;
        }
        if ($require === true && ($media === null)) {
            throw new FileNotFoundException($path);
        }

        if ($require === false && ($media !== null)) {
            throw new FileExistsException($path);
        }

        return $media;
    }

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        return $this->find($path) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        return file_get_contents($this->find($path, true)->realPath);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream($path)
    {
        return fopen($this->find($path, true)->realPath, 'r');
    }

    /**
     * {@inheritdoc}
     */
    public function listContents($directory = '', $recursive = false)
    {
        if (in_array($directory, ['/', '.', ''], true)) {
            $list = $recursive ? MediaEntity::all() : MediaEntity::roots()->get();
        } else {
            $media = $this->find($directory);
            if ($media === null || $media->type === MediaEntity::TYPE_FILE) {
                return [];
            }

            if ($recursive) {
                $list = $media->getDescendants();
            } else {
                $list = $media->getImmediateDescendants();
            }
        }

        return $list->map([$this, 'mapFileInfo'])->all();

    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($path)
    {
        return $this->mapFileInfo($this->find($path, true));
    }

    /**
     * Get a file's size.
     *
     * @param string $path The path to the file.
     *
     * @return int|false The file size or false on failure.
     */
    public function getSize($path)
    {
        $media = $this->find($path);

        if ($media === null || $media->type === MediaEntity::TYPE_DIR) {
            return false;
        }

        return filesize($media->realPath);
    }

    /**
     * Get a file's mime-type.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     *
     * @return string|false The file mime-type or false on failure.
     */
    public function getMimetype($path)
    {
        $media = $this->find($path, true);

        $finfo = new Finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($media->realPath);
    }

    /**
     * Get a file's timestamp.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     *
     * @return string|false The timestamp or false on failure.
     */
    public function getTimestamp($path)
    {
        $media = $this->find($path, true);

        return $media->updated_at->timestamp;
    }

    /**
     * Get a file's visibility.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     *
     * @return string|false The visibility (public|private) or false on failure.
     */
    public function getVisibility($path)
    {
        $this->find($path, true);

        return 'public';
    }

    /**
     * Write a new file.
     *
     * @param string $path     The path of the new file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws FileExistsException
     * @throws FileNotFoundException
     *
     * @return bool True on success, false on failure.
     */

    public function write($path, $contents, array $config = [])
    {
        $this->find($path, false);
        $media = $this->createFile($path, $config);

        if (file_put_contents($media->realPath, $contents) === false) {
            $media->forceDelete();

            return false;
        }

        return true;
    }

    /**
     * Write a new file using a stream.
     *
     * @param string   $path     The path of the new file.
     * @param resource $resource The file handle.
     * @param array    $config   An optional configuration array.
     *
     * @throws \InvalidArgumentException If $resource is not a file handle.
     * @throws FileExistsException
     * @throws FileNotFoundException
     *
     * @return bool True on success, false on failure.
     */
    public function writeStream($path, $resource, array $config = [])
    {
        $this->find($path, false);

        $media = $this->createFile($path, $config);

        if (!$destination = fopen($media->realPath, 'w+')) {
            $media->forceDelete();

            return false;
        }

        stream_copy_to_stream($resource, $destination);

        if (!fclose($destination)) {
            $media->forceDelete();

            return false;
        }

        Util::rewindStream($resource);

        return $media !== null;
    }

    /**
     * Update an existing file.
     *
     * @param string $path     The path of the existing file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws FileNotFoundException
     *
     * @return bool True on success, false on failure.
     */
    public function update($path, $contents, array $config = [])
    {
        $media = $this->find($path, true);

        if (file_put_contents($media->realPath, $contents) === false) {
            return false;
        }

        return true;

    }

    /**
     * Update an existing file using a stream.
     *
     * @param string   $path     The path of the existing file.
     * @param resource $resource The file handle.
     * @param array    $config   An optional configuration array.
     *
     * @throws InvalidArgumentException If $resource is not a file handle.
     * @throws FileNotFoundException
     *
     * @return bool True on success, false on failure.
     */
    public function updateStream($path, $resource, array $config = [])
    {
        $media = $this->find($path, true);

        $realPath = $media->realPath;
        if (!$destination = fopen($realPath.'.new', 'w+')) {


            return false;
        }

        stream_copy_to_stream($resource, $destination);

        if (!fclose($destination)) {

            return false;
        }

        $oldPath = $realPath.'old'.time();
        if (!rename($realPath, $oldPath) || !rename($realPath.'.new', $realPath)) {
            return false;
        }
        unlink($oldPath);

        Util::rewindStream($resource);

        return $media !== null;
    }

    /**
     * Rename a file.
     *
     * @param string $path    Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @throws \Exception|\Throwable
     * @throws FileExistsException   Thrown if $newpath exists.
     * @throws FileNotFoundException Thrown if $path does not exist.
     *
     * @return bool True on success, false on failure.
     */
    public function rename($path, $newpath)
    {
        $source = $this->find($path, true);

        $this->find($newpath, false);

        return $source->getConnection()->transaction(function () use ($source, $newpath) {
            $src = $source->realPath;
            $source->caption = basename($newpath);
            $source->save();
            $this->moveTo($source, dirname($newpath));
            $dst = $source->realPath;
            if ($dst !== $src && rename($src, $dst) === false) {
                throw new InvalidArgumentException;
            }

            return true;
        });

    }

    /**
     * Copy a file.
     *
     * @param string $path    Path to the existing file.
     * @param string $newpath The new path of the file.
     *
     * @throws \Exception|\Throwable
     * @throws \LogicException
     * @throws \InvalidArgumentException
     * @throws FileExistsException   Thrown if $newpath exists.
     * @throws FileNotFoundException Thrown if $path does not exist.
     *
     * @return bool True on success, false on failure.
     */
    public function copy($path, $newpath)
    {
        $source = $this->find($path, true);
        $this->find($newpath, false);

        return $source->getConnection()->transaction(function () use ($newpath, $source) {
            $destination = $this->createFile($newpath, []);
            $this->moveTo($destination, dirname($newpath));

            if (copy($source->realPath, $destination->realPath) === false) {
                throw new \InvalidArgumentException();
            }

            return true;
        });
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @throws FileNotFoundException
     *
     * @return bool True on success, false on failure.
     */
    public function delete($path)
    {
        return $this->find($path, true)->delete();
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @throws RootViolationException Thrown if $dirname is empty.
     *
     * @return bool True on success, false on failure.
     */
    public function deleteDir($dirname)
    {
        $dir = $this->find($dirname, true);
        $dir->delete();
//        if ($dir->immediateDescendants()->count() > 0) {
//
//        } else {
//            throw new RootViolationException();
//        }
        return true;
    }

    /**
     * Create a directory.
     *
     * @param string $dirname The name of the new directory.
     * @param array  $config  An optional configuration array.
     *
     * @return bool True on success, false on failure.
     */
    public function createDir($dirname, array $config = [])
    {
        return $this->ensureDirectory($dirname, $config) !== null;
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path       The path to the file.
     * @param string $visibility One of 'public' or 'private'.
     *
     * @return bool True on success, false on failure.
     */
    public function setVisibility($path, $visibility)
    {
        return false;
    }

    /**
     * Create a file or update if exists.
     *
     * @param string $path     The path to the file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @return bool True on success, false on failure.
     */
    public function put($path, $contents, array $config = [])
    {
        return $this->has($path) ? $this->update($path, $contents, $config) : $this->write($path, $contents, $config);
    }

    /**
     * Create a file or update if exists.
     *
     * @param string   $path     The path to the file.
     * @param resource $resource The file handle.
     * @param array    $config   An optional configuration array.
     *
     * @throws InvalidArgumentException Thrown if $resource is not a resource.
     *
     * @return bool True on success, false on failure.
     */
    public function putStream($path, $resource, array $config = [])
    {
        return $this->has($path) ? $this->updateStream($path, $resource, $config) : $this->writeStream($path, $resource, $config);
    }

    /**
     * Read and delete a file.
     *
     * @param string $path The path to the file.
     *
     * @throws FileNotFoundException
     *
     * @return string|false The file contents, or false on failure.
     */
    public function readAndDelete($path)
    {
        $this->find($path, true);

        $contents = $this->read($path);
        if ($contents === false) {
            return false;
        }

        $this->delete($path);

        return $contents;
    }

    /**
     * Get a file/directory handler.
     *
     * @param string  $path    The path to the file.
     * @param Handler $handler An optional existing handler to populate.
     *
     * @return Handler Either a file or directory handler.
     */
    public function get($path, Handler $handler = null)
    {
        $path = Util::normalizePath($path);

        if (!$handler) {
            $metadata = $this->getMetadata($path);
            $handler = $metadata['type'] === 'file' ? new File($this, $path) : new Directory($this, $path);
        }

        $handler->setPath($path);
        $handler->setFilesystem($this);

        return $handler;
    }


    /**
     * @param MediaEntity $node
     * @param string      $destination
     */
    protected function moveTo(MediaEntity $node, $destination)
    {
        if ($parent = $this->ensureDirectory($destination)) {
            $node->makeChildOf($parent);
        } else {
            $node->makeRoot();
        }
    }

    /**
     * @param MediaEntity $file
     *
     * @return array
     */
    public function mapFileInfo(MediaEntity $file)
    {
        $normalized = [
            'type'     => $file->type,
            'path'     => $file->fullPath,
            'basename' => $file->fullPath,
        ];

        $normalized['timestamp'] = $file->updated_at->timestamp;

        if ($normalized['type'] === MediaEntity::TYPE_FILE) {
            $normalized['size'] = filesize($file->realPath);
        }

        return $normalized;
    }

    /**
     * Ensure the root directory exists.
     *
     * @param string $root root directory path
     * @param array  $config
     *
     * @return MediaEntity
     */
    protected function ensureDirectory($root, array $config = [])
    {
        $media = $this->find($root);
        if ($media === null && !in_array($root, ['/', '.', ''], true)) {
            $media = MediaEntity::createDirectory(basename($root));
            $this->moveTo($media, dirname($root));
        }

        return $media;
    }

    /**
     * @param string $path
     * @param array  $config
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     *
     * @return MediaEntity
     */
    protected function createFile($path, array $config)
    {
        $path = Util::normalizePath($path);
        $pathInfo = pathinfo($path);

        $media = MediaEntity::create(
            [
                'hash'      => array_key_exists('hash', $config) ? $config['hash'] : md5(uniqid('media', true)),
                'ext'       => null,
                'type'      => MediaEntity::TYPE_FILE,
                'caption'   => array_key_exists('caption ', $config) ? $config['caption'] : $pathInfo['basename'],
                'parent_id' => array_key_exists('parent_id', $config) ? $config['parent_id'] : null,
            ]
        );

        $dir = dirname($media->realPath);
        if (@mkdir($dir, 0755, true) && !is_dir($dir)) {
            $media->forceDelete();

            throw new \InvalidArgumentException();
        }

        $this->moveTo($media, dirname($path));

        return $media;
    }
}