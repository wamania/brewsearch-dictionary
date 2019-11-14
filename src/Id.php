<?php

namespace Wamania\BrewSearch\Dictionary;

use Wamania\BrewSearch\Dictionary\Constant as CC;
use Wamania\BrewSearch\Dictionary\File\File;
use Wamania\BrewSearch\Dictionary\File\PhysicalFile;
use Wamania\BrewSearch\Dictionary\Utils\Utils;

class Id
{
    /** @var int */
    protected $id;

    /** @var bool  */
    protected $isModified;

    /** @var PhysicalFile */
    protected $file;

    /**
     * Constructor
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->isModified = false;
        $this->id = null;
        $this->file = File::factory('physical', $filePath);
    }

    /**
     * Init function
     * @return void
     */
    public function init()
    {
        $this->file->init();

        $filesize = $this->file->getFilesize();
        if ($filesize < (CC::ID_BYTES)) {
            $this->file->writeBytes(Utils::pack(0, CC::ID_BYTES));
            $this->file->flush();
        }
    }

    /**
     * Get current id of the BrewString
     */
    public function getId()
    {
        if (null === $this->id) {
            $this->load();
        }

        return $this->id;
    }

    public function load()
    {
        $this->file->seek(0);
        $value = $this->file->readBytes(CC::ID_BYTES);
        $this->id = Utils::unpack($value, CC::ID_BYTES);
    }

    /**
     * Set current id of the BrewString
     */
    public function increment(): int
    {
        if (null === $this->id) {
            $this->load();
        }

        $this->id++;
        $this->isModified = true;

        return $this->id;
    }

    public function flush(): void
    {
        if ($this->isModified) {
            $this->file->seek(0);
            $value = Utils::pack($this->id, CC::ID_BYTES);
            $this->file->writeBytes($value);
        }
    }
}