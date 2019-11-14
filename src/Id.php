<?php
namespace Wamania\BrewSearch\Catalog\Dictionary;

use Wamania\BrewSearch\Catalog\CatalogConst as CC;
use Wamania\BrewSearch\Utils\Utils;
use Wamania\BrewSearch\File\PhysicalFile;
use Wamania\BrewSearch\File\MemoryFile;
use Wamania\BrewSearch\File\File;

class Id
{
    protected $id;

    protected $isModified;

    protected $file;

    protected $options;

    /**
     * Constructor
     * @param string $filePath
     */
    public function __construct($filePath, $options)
    {
        $this->isModified   = false;
        $this->id           = null;
        //$this->file         = new MemoryFile($filePath);

        $this->options = array_merge($options, array('file' => 'memory'));

        $this->file = File::factory($this->options['file'], $filePath);
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

    public function load()
    {
        $this->file->seek(0);
        $value = $this->file->readBytes(CC::ID_BYTES);
        $this->id = Utils::unpack($value, CC::ID_BYTES);
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

    /**
     * Set current id of the BrewString
     * @param int $id
     */
    public function increment()
    {
        if (null === $this->id) {
            $this->load();
        }

        $this->id++;
        $this->isModified = true;

        return $this->id;
    }

    public function flush()
    {
        if ($this->isModified) {
            $this->file->seek(0);
            $value = Utils::pack($this->id, CC::ID_BYTES);
            return $this->file->writeBytes($value);
        }
        return true;
    }
}