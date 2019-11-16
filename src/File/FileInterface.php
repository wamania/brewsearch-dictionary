<?php

namespace Wamania\BrewSearch\Dictionary\File;

interface FileInterface
{
    public function init(): void;

    public function open(): void;

    public function close(): void;

    public function flush(): void;

    public function reset(): void;

    public function seek(int $position): void;

    public function seekToEnd(): void;

    public function getCurrentPosition(): int;

    public function readBytes(int $size): string;

    public function writeAtEnd(string $bytes): void;

    public function writeBytes(string $bytes): void;

    public function getFilesize(): int;
}
