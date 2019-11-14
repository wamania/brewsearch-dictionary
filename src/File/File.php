<?php
namespace Wamania\BrewSearch\Dictionary\File;

abstract class File
{
    /**
     * The file path
     * @var string
     */
    protected $filePath;

    /**
     * Constructor
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath 	= $filePath;
    }

    /**
     * Return a File object
     *
     * @param string $type
     * @param string $filePath
     * @return File
     */
    public static function factory($type, $filePath)
    {
        $className = 'Wamania\BrewSearch\Dictionary\File\\'.ucwords($type).'File';
        if (! class_exists($className)) {
            $className = 'Wamania\BrewSearch\Dictionary\File\PhysicalFile';
        }
        return new $className($filePath);
    }

    /**
     * Init file (create it if not exists)
     */
    public function init()
    {
        if (! file_exists($this->filePath)) {
            touch($this->filePath);
        }
    }
}