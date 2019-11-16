<?php

namespace Wamania\BrewSearch\Dictionary;

use Wamania\BrewSearch\Dictionary\Constant as CC;
use Wamania\BrewSearch\Dictionary\File\AbstractFile;
use Wamania\BrewSearch\Dictionary\File\PhysicalFile;
use Wamania\BrewSearch\Dictionary\Utils\Pack;

class Id
{
    /** @var int */
    protected $id;

    /** @var bool  */
    protected $isModified;

    /** @var PhysicalFile */
    protected $file;

    public function __construct(string $filePath)
    {
        $this->isModified = false;
        $this->id = null;
        $this->file = AbstractFile::factory('physical', $filePath);
    }

    public function init(): void
    {
        $this->file->init();

        $filesize = $this->file->getFilesize();
        if ($filesize < (CC::ID_BYTES)) {
            $this->file->writeBytes(Pack::pack(0, CC::ID_BYTES));
            $this->file->flush();
        }
    }

    public function load(): void
    {
        $this->file->seek(0);
        $value = $this->file->readBytes(CC::ID_BYTES);
        $this->id = Pack::unpack($value, CC::ID_BYTES);
    }

    public function increment(): int
    {
        if (null === $this->id) {
            $this->load();
        }

        $this->id++;
        $this->isModified = true;
        $this->flush();

        return $this->id;
    }

    public function flush(): void
    {
        if ($this->isModified) {
            $this->file->seek(0);
            $value = Pack::pack($this->id, CC::ID_BYTES);
            $this->file->writeBytes($value);
        }
    }
}
