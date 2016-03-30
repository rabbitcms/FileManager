<?php namespace RabbitCMS\FileManager\Entities;

use Baum\Node;
use Carbon\Carbon;
use DKulyk\Eloquent\Logging;
use DKulyk\Eloquent\PrintableJson;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Media
 *
 * @property-read int    $id
 * @property int         $parent_id
 * @property string      $hash
 * @property string      $type
 * @property string      $caption
 *
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon $deleted_at
 *
 * @property-read string $path
 * @property-read string $realPath
 * @property-read string $fullPath
 * @property-read int    $size
 * @proeprty-read string $mime
 */
class Media extends Node
{
    use Logging, PrintableJson, SoftDeletes;

    const TYPE_FILE = 'file';
    const TYPE_DIR = 'dir';

    /**
     * {@inheritdoc}
     */
    protected $table = 'media';

    /**
     * {@inheritdoc}
     */
    protected $fillable
        = [
            'hash',
            'type',
            'caption',
        ];

    protected $casts
        = [
            'parent_id' => 'int',
            'size'      => 'int',
        ];

    protected $hidden
        = [
            'lft',
            'rght',
            'depth',
            'parent',
            'children',
        ];

    protected $appends
        = [
            'fullPath',
            'size',
            'mime',
        ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Media::class, 'parent_id');
    }

    /**
     * Get file path
     *
     * @return string
     */
    public function getPathAttribute()
    {
        $pathInfo = pathinfo($this->caption);

        return "{$this->hash[0]}{$this->hash[1]}/{$this->hash[2]}{$this->hash[3]}/{$this->id}-{$this->hash}.{$pathInfo['extension']}";
    }

    /**
     * Get real file path
     *
     * @return mixed
     */
    public function getRealPathAttribute()
    {
        return storage_path('media/'.$this->getPathAttribute());
    }

    /**
     * Get full file path
     *
     * @return string
     */
    public function getFullPathAttribute()
    {
        return $this->getAncestorsAndSelf()->implode('caption', '/');
    }

    /**
     * @return string
     */
    public function getMimeAttribute()
    {
        if ($this->type === self::TYPE_DIR) {
            return 'directory';
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($this->realPath);
    }

    /**
     * @return bool|int
     */
    public function getSizeAttribute()
    {
        return $this->type === self::TYPE_DIR ? false : filesize($this->realPath);
    }

    /**
     * Create media record
     *
     * @param string $filename
     * @param array  $attributes
     *
     * @return static
     */
    public static function createFile($filename, array $attributes = [])
    {
        $pathInfo = pathinfo($filename);

        $media = static::create(
            [
                'hash'      => array_key_exists('hash', $attributes) ? $attributes['hash'] : md5(uniqid('media', true)),
                'ext'       => array_key_exists('extension', $pathInfo) ? $pathInfo['extension'] : null,
                'type'      => Media::TYPE_FILE,
                'caption'   => array_key_exists('caption ', $attributes) ? $attributes['caption'] : $pathInfo['filename'],
                'parent_id' => array_key_exists('parent_id', $attributes) ? $attributes['parent_id'] : null,
            ]
        );

        $dir = dirname($media->realPath);
        if (@mkdir($dir, 0755, true) && !is_dir($dir)) {
            $media->forceDelete();
            throw new \InvalidArgumentException('Dont create directory.'); //todo
        }

        return $media;
    }

    /**
     * Create media from resource
     *
     * @param resource $source
     * @param string   $filename
     * @param array    $attributes [optional]
     *
     * @throws \InvalidArgumentException
     * @return static
     */
    public static function createFromStream($source, $filename, array $attributes = [])
    {
        $media = static::createFile($filename, $attributes);

        if (!$destination = fopen($media->realPath, 'w+')) {
            $media->forceDelete();

            throw new \InvalidArgumentException('Dont create destination file.'); //todo
        }

        stream_copy_to_stream($source, $destination);

        if (!fclose($destination)) {
            $media->forceDelete();

            throw new \InvalidArgumentException('Dont close destination file.'); //todo
        }

        return $media;
    }

    /**
     * Create directory
     *
     * @param string $caption
     * @param array  $attributes [optional]
     *
     * @return static
     */
    public static function createDirectory($caption, array $attributes = [])
    {
        $media = static::create(
            [
                'hash'      => array_key_exists('hash', $attributes) ? $attributes['hash'] : md5(uniqid('media', true)),
                'ext'       => null,
                'type'      => Media::TYPE_DIR,
                'caption'   => $caption,
                'parent_id' => array_key_exists('parent_id', $attributes) ? $attributes['parent_id'] : null,
            ]
        );

        return $media;
    }

    /**
     * @param string $filename
     * @param array  $attributes [optional]
     *
     * @throws \InvalidArgumentException
     *
     * @return static
     */
    static public function createFromFile($filename, array $attributes = [])
    {
        $fp = @fopen($filename, 'r');

        if ($fp === false) {
            throw new \InvalidArgumentException('Dont open file'); //todo
        }

        return static::createFromStream($fp, array_key_exists('filename', $attributes) ? $attributes['filename'] : $filename, $attributes);
    }
}