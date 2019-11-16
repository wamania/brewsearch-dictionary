<?php

namespace Wamania\BrewSearch\Dictionary\File;

class PhysicalFile extends AbstractFile implements FileInterface
{
    /**
     * @var resource
     */
    protected $fileHandler;

    public function __construct(string $filePath)
    {
        parent::__construct($filePath);

        $this->fileHandler 	= null;
    }

    public function open(): void
    {
        $this->fileHandler = fopen($this->filePath, 'r+b');
        flock($this->fileHandler,  LOCK_SH);
    }

    public function close(): void
    {
        if (null !== $this->fileHandler) {
            flock($this->fileHandler, LOCK_UN);
            fclose($this->fileHandler);
            $this->fileHandler = null;
        }
    }

    public function flush(): void
    {
    }

    public function reset(): void
    {
        if (null == $this->fileHandler) {
            $this->open();
        }
        ftruncate($this->fileHandler, 0);
    }

    public function getFilesize(): int
    {
        if (null == $this->filePath) {
            return 0;
        }

        return filesize($this->filePath);
    }

    public function seek(int $position): void
    {
        if (null == $this->fileHandler) {
            $this->open();
        }

        fseek($this->fileHandler, $position);
    }

    public function seekToEnd(): void
    {
        if (null == $this->fileHandler) {
            $this->open();
        }

        fseek($this->fileHandler, 0, SEEK_END);
    }

    public function getCurrentPosition(): int
    {
        if (null === $this->fileHandler) {
            return null;
        }
        return ftell($this->fileHandler);
    }

    public function readBytes(int $size): string
    {
        if (null == $this->fileHandler) {
            $this->open();
        }

        return fread($this->fileHandler, $size);
    }

    public function writeAtEnd(string $bytes): void
    {
        if (null == $this->fileHandler) {
            $this->open();
        }

        $this->seekToEnd();

        $this->writeBytes($bytes);
    }

    public function writeBytes(string $bytes): void
    {
        if (null == $this->fileHandler) {
            $this->open();
        }

        if (flock($this->fileHandler,  LOCK_EX | LOCK_NB)) {
            fwrite($this->fileHandler, $bytes);
            flock($this->fileHandler, LOCK_UN);
        }
    }

    public function __destruct()
    {
        if (null != $this->fileHandler) {
            $this->close();
        }
    }
}
