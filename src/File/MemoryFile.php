<?php
namespace Wamania\BrewSearch\Dictionary\File;

class MemoryFile extends AbstractFile implements FileInterface
{
    /**
     * The file content if loaded
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

    public function __construct(string $filePath)
    {
        parent::__construct($filePath);

        $this->content = null;
        $this->pointer = 0;
        $this->isModified = false;
    }

    public function open(): void
    {
        $this->content = file_get_contents($this->filePath);
    }

    public function close(): void
    {
        $this->content = null;
    }

    public function flush(): void
    {
        if ($this->isModified) {
            file_put_contents($this->filePath, $this->content);
        }
    }

    public function reset(): void
    {
        $this->content = '';
        $this->pointer = 0;
    }

    public function getFilesize(): int
    {
        return strlen($this->content);
    }

    public function seek(int $position): void
    {
        $this->pointer = $position;
    }

    public function seekToEnd(): void
    {
        $this->pointer = $this->getFilesize();
    }

    public function getCurrentPosition(): int
    {
        return $this->pointer;
    }

    public function readBytes(int $size): string
    {
        $read = substr($this->content, $this->pointer, $size);
        $this->pointer += $size;

        return $read;
    }

    public function writeAtEnd(string $bytes): void
    {
        $this->isModified = true;
        $this->pointer += strlen($bytes);

        $this->content .= $bytes;
    }

    public function writeBytes(string $bytes): void
    {
        $this->isModified = true;
        $size = strlen($bytes);

        $this->content = substr_replace($this->content, $bytes, $this->pointer, $size);

        $this->pointer += $size;
    }
}
