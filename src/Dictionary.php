<?php

namespace Wamania\BrewSearch\Dictionary;

use Wamania\BrewSearch\Dictionary\Stage\Stage;
use Wamania\BrewSearch\Dictionary\Constant as CC;

class Dictionary
{
    /** @var Id */
    private $id;

    /** @var SwitchBoard */
    private $switchBoard;

    /** @var string */
    private $folder;

    public function __construct(string $folder)
    {
        $this->folder = $folder;

        $this->switchBoard = new SwitchBoard(
            $folder . DIRECTORY_SEPARATOR
            . 'switchboard.bin');

        $this->id = new Id(
            $folder . DIRECTORY_SEPARATOR
            . 'autoincrement.bin');
    }

    public function init(): void
    {
        if (!is_dir($this->folder)) {
            mkdir($this->folder, 0777, true);
        }

        $this->switchBoard->init();
        $this->id->init();
    }

    public function load(): void
    {
        $this->switchBoard->load();
        $this->id->load();
    }

    public function process(string $word): int
    {
        $chars = unpack('C*', $word);
        $chars = array_values($chars);

        // we scan le switchboard file to try to find each letter
        // lastFound contains the index of the last stage found
        // and the corresponding index in the $chars array
        $lastFound = $this->switchBoard->scan($chars);

        if ($lastFound['charsIndex'] == count($chars)) {

            // on load the stage we have found
            $stage = new Stage($this->switchBoard, $lastFound['index']);

            // get the id
            $id = $stage->getFirstPart()->getId();

        } else {

            // Un 1ier stagepart pour compléter l'annuaire de la dernière lettre trouvée
            // le stage existe déjà, on a donc déjà un 1er StagePart avec un id
            $stage = new Stage($this->switchBoard, $lastFound['index']);
            $stage->addPart([
                $chars[$lastFound['charsIndex']] => ($this->switchBoard->lastIndex() + CC::FULL_STAGE_PART_SIZE)
            ], 0);

            // tous les nouveaux Stage et pour chacun une StagePart, à la fin et avec un id
            $stage = new Stage($this->switchBoard);
            for ($i = ($lastFound['charsIndex'] + 1); $i < count($chars); $i++) {

                // last id +1
                $id = $this->id->increment();

                $stage->addPart([
                    $chars[$i] => ($this->switchBoard->lastIndex() + CC::FULL_STAGE_PART_SIZE)
                ], $id);
            }

            // last id +1
            $id = $this->id->increment();

            // le dernier stage, ne contient qu'un id
            $stage->addPart([], $id);
        }

        return $id;
    }

    public function flush(): void
    {
        $this->switchBoard->flush();
        $this->id->flush();
    }
}
