<?php

namespace Wamania\BrewSearch\Dictionary;

use Wamania\BrewSearch\Dictionary\File\AbstractFile;
use Wamania\BrewSearch\Dictionary\File\FileInterface;
use Wamania\BrewSearch\Dictionary\Stage\Stage;
use Wamania\BrewSearch\Dictionary\Stage\StagePartFormater;
use Wamania\BrewSearch\Dictionary\Constant as CC;
use Wamania\BrewSearch\Dictionary\Utils\Pack;

class SwitchBoard
{
    /**
     * We work on loaded content or directly on the file ?
     * @var boolean
     */
    protected $isLoaded;

    /**
     * @var FileInterface
     */
    protected $file;

    /**
     * Constructor
     * @param string $filePath
     */
    public function __construct(string $filePath)
    {
        $this->file = AbstractFile::factory('physical', $filePath);
        $this->isLoaded = false;
    }

    public function init(): void
    {
        $this->file->init();
        $this->load();

        $filesize = $this->file->getFilesize();
        if ($filesize < (CC::ANNUAIRE_SIZE_BYTES + CC::ID_BYTES + CC::NEXT_PART_BYTES)) {
            $this->file->writeBytes(Pack::pack(0, CC::ANNUAIRE_SIZE_BYTES));
            $this->file->writeBytes(Pack::pack(0, CC::ID_BYTES));
            $this->file->writeBytes(Pack::pack(0, CC::NEXT_PART_BYTES));

            $this->file->flush();
        }
    }

    public function load(): void
    {
        if (!$this->isLoaded) {
            $this->file->open();
            $this->isLoaded = true;
        }
    }

    public function scan(array $chars): ?array
    {
        $lastFound = $this->scanIndex(0, $chars, 0);

        // if switchboard is empty
        if (null == $lastFound) {
            $lastFound = array(
                'charsIndex' => 0,
                'index' => 0
            );
        }

        return $lastFound;
    }

    /**
     * We recursively search all the letters of the word (that are in $chars)
     *
     * @param int $index Current position if the switchboard file
     * @param array $chars The letters
     * @param int $charsIndex Position in letters array $chars
     * @return array
     */
    public function scanIndex(int $index, array $chars, int $charsIndex): ?array
    {
        // we have all letters
        if ($charsIndex == count($chars)) {
            return [
                'charsIndex' => $charsIndex,
                'index' => $index
            ];
        }

        $stage = new Stage($this, $index);
        $annuaire = $stage->getAnnuaire();
        $found = null;

        foreach ($annuaire as $letter => $position) {

            // we found our letter
            if ($letter == $chars[$charsIndex]) {
                $found = $this->scanIndex($position, $chars, $charsIndex + 1);

                if (null === $found) {
                    $found = [
                        'charsIndex' => ($charsIndex + 1),
                        'index' => $position
                    ];
                    break;
                }
            }
        }

        return $found;
    }

    public function readIndex(int $index = 0): array
    {
        $stage = new Stage($this, $index);
        $parts = $stage->getParts();

        return $parts;
    }

    public function extract(int $start, int $length): string
    {
        $this->file->seek($start);

        return $this->file->readBytes($length);
    }

    public function lastIndex(): int
    {
        $this->file->seekToEnd();

        return $this->file->getCurrentPosition();
    }

    public function add(StagePartFormater $stagePartFormater): void
    {
        $this->file->writeAtEnd($stagePartFormater->toString());
    }

    public function replace(string $replace, int $start): void
    {
        $this->file->seek($start);
        $this->file->writeBytes($replace);
    }

    public function flush(): void
    {
        $this->file->flush();
    }
}
