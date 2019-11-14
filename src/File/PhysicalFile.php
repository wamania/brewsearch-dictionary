<?php
namespace Wamania\BrewSearch\Dictionary\File;

class PhysicalFile extends File
{
    /**
     * The file ressource
     */
    protected $fileHandler;

    /**
     * Constructor
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        parent::__construct($filePath);

        $this->fileHandler 	= null;
    }

    /**
     * Crée une ressource sur un fichier
     * open in r+ to allow seeking, and b for binary
     *
     * @return void
     */
    public function open($mode = 'r+b')
    {
        $this->fileHandler = fopen($this->filePath, $mode);
    }

    /**
     * Close file ressource
     *
     * @return void
     */
    public function close()
    {
        if (null !== $this->fileHandler) {
            fclose($this->fileHandler);
            $this->fileHandler = null;
        }
    }

    public function flush()
    {
        return true;
    }

    public function reset()
    {
        if (null == $this->fileHandler) {
            $this->open();
        }
        ftruncate($this->fileHandler, 0);
    }

    /**
     * Get file size
     *
     * @return int
     */
    public function getFilesize()
    {
        if (null == $this->filePath) {
            return 0;
        }

        return filesize($this->filePath);
    }

    /**
     * Seeks on a file pointer
     *
     * @param  int $position
     * @return bool
     */
    public function seek($position)
    {
        if (null == $this->fileHandler) {
            $this->open();
        }

        return fseek($this->fileHandler, $position);
    }

    /**
     * Seek to the end
     *
     * @return number
     */
    public function seekToEnd()
    {
        if (null == $this->fileHandler) {
            $this->open();
        }

        return fseek($this->fileHandler, 0, SEEK_END);
    }

    /**
     * Return current position of pointer in file
     *
     * @return int
     */
    public function getPosition()
    {
        if (null === $this->fileHandler) {
            return null;
        }
        return ftell($this->fileHandler);
    }

    /**
     * Lit et retourne un nombre $size d'octet et converti en int (S,V) ou char (C)
     *
     * @param  integer $size Taille en octet
     * @return integer|string
     */
    public function readBytes($size)
    {
        if (null == $this->fileHandler) {
            $this->open();
        }

        return fread($this->fileHandler, $size);
    }


    public function writeAtEnd($string)
    {
        if (null == $this->fileHandler) {
            $this->open();
        }

        $this->seekToEnd();
        return $this->writeBytes($string);
    }

    /**
     * Write $size octets packed
     *
     * @param  [type] $value [description]
     * @param  [type] $size  [description]
     * @return [type]        [description]
     */
    public function writeBytes($string)
    {
        if (null == $this->fileHandler) {
            $this->open();
        }

        return fwrite($this->fileHandler, $string);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if (null != $this->fileHandler) {
            $this->close();
        }
    }
}