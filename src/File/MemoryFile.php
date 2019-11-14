<?php
namespace Wamania\BrewSearch\Dictionary\File;

class MemoryFile extends File
{
    /** The file content if loaded
     *
     * @var string
     */
    protected $content;

    /**
     * If modified in memory, we need to save it
     * @var bool
     */
    protected $isModified;

    /**
     * Equivalent of physical file pointer
     * @var int
     */
    protected $pointer;

    /**
     * Constructor
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        parent::__construct($filePath);

        $this->content = null;
        $this->pointer = 0;
        $this->isModified = false;
    }

    public function open()
    {
        $this->content = file_get_contents($this->filePath);
    }

    public function close()
    {
        $this->content = null;
    }

    public function flush()
    {
        if ($this->isModified) {
            file_put_contents($this->filePath, $this->content);
        }
    }

    public function reset()
    {
        $this->content = '';
        $this->pointer = 0;
    }

    /**
     * Get file size
     *
     * @return int
     */
    public function getFilesize()
    {
        return strlen($this->content);
    }

    /**
     * Seeks on a file pointer
     *
     * @param  int $position
     * @return bool
     */
    public function seek($position)
    {
        $this->pointer = $position;
    }

    /**
     * Seek to the end
     *
     * @return number
     */
    public function seekToEnd()
    {
        $this->pointer = $this->getFilesize();
    }

    /**
     * Return current position of pointer in file
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->pointer;
    }

    /**
     * Lit et retourne un nombre $size d'octet et converti en int (S,V) ou char (C)
     *
     * @param  integer $size Taille en octet
     * @return integer|string
     */
    public function readBytes($size)
    {
        $read = substr($this->content, $this->pointer, $size);
        $this->pointer += $size;

        return $read;
    }

    /**
     * Write string
     *
     * @param string $string
     */
    public function writeAtEnd($string)
    {
        $this->isModified = true;
        $this->pointer += strlen($string);

        $this->content .= $string;
    }

    /**
     * Write $size octets packed
     *
     * @param  [type] $value [description]
     * @param  [type] $size  [description]
     * @return [type]        [description]
     */
    public function writeBytes($value)
    {
        $this->isModified = true;
        $size = strlen($value);

        $this->content = substr_replace($this->content, $value, $this->pointer, $size);

        $this->pointer += $size;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        /*if ($this->isModified) {
            $this->close();
        }*/
    }
}