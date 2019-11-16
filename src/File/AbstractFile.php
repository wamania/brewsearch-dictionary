<?php

namespace Wamania\BrewSearch\Dictionary\File;

abstract class AbstractFile
{
    /**
     * @var string
     */
    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath 	= $filePath;
    }

    public static function factory(string $type, string $filePath): FileInterface
    {
        $className = 'Wamania\BrewSearch\Dictionary\File\\'.ucwords($type).'File';
        if (! class_exists($className)) {
            $className = 'Wamania\BrewSearch\Dictionary\File\PhysicalFile';
        }
        return new $className($filePath);
    }

    public function init(): void
    {
        if (! file_exists($this->filePath)) {
            touch($this->filePath);
        }
    }
}