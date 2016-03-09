<?php namespace RabbitCMS\FileManager\Entities;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Media
 *
 * @property-read int    $id
 * @property int         $parent_id
 * @property string      $hash
 * @property string      $type
 * @property string      $caption
 * @property string      $ext
 * @property int         $size
 *
 * @property-read string $path
 * @property-read string $fullPath
 *
 */
class Media extends Model
{
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
            'ext',
            'parent_id',
            'size',
        ];

    protected $casts
        = [
            'parent_id' => 'int',
            'size'      => 'int',
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
        return "{$this->hash[0]}{$this->hash[1]}/{$this->hash[2]}{$this->hash[3]}/{$this->id}-{$this->hash}.{$this->ext}";
    }

    /**
     * Get full file path
     *
     * @return mixed
     */
    public function getFullPathAttribute()
    {
        return storage_path('media/'.$this->getPathAttribute());
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

        $dir = dirname($media->fullPath);
        if (@mkdir($dir, 0755, true) && !is_dir($dir)) {
            $media->forceDelete();
            throw new \InvalidArgumentException('Dont create directory.'); //todo
        }

        if (!$destination = fopen($media->fullPath, 'w+')) {
            $media->forceDelete();

            throw new \InvalidArgumentException('Dont create destination file.'); //todo
        }

        $size = stream_copy_to_stream($source, $destination);

        if (!fclose($destination)) {
            $media->forceDelete();

            throw new \InvalidArgumentException('Dont close destination file.'); //todo
        }

        $media->update(['size' => $size]);

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