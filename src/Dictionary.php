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
            . 'dictionary' . DIRECTORY_SEPARATOR
            . 'switchboard.bin');

        $this->id = new Id(
            $folder . DIRECTORY_SEPARATOR
            . 'dictionary' . DIRECTORY_SEPARATOR
            . 'autoincrement.bin');
    }

    public function init()
    {
        if (!is_dir($this->folder . DIRECTORY_SEPARATOR . 'dictionary')) {
            mkdir($this->folder . DIRECTORY_SEPARATOR . 'dictionary', 0777, true);
        }

        $this->switchBoard->init();
        $this->id->init();
    }

    public function load()
    {
        $this->switchBoard->load();
        $this->id->load();
    }

    /*public function search($word)
    {
        $chars = unpack('C*', $word);
        // we reindex to begun at index 0
        $chars = array_values($chars);

        $lastFound = $this->switchBoard->scan($chars);

        // word found
        if ($lastFound['charsIndex'] == count($chars)) {
            $stage = new Stage($this->switchBoard, $lastFound['index']);
            $id = $stage->getFirstPart()->getId();

            return $id;
        }

        return null;
    }*/

    public function process(string $word): int
    {
        $chars = unpack('C*', $word);
        $chars = array_values($chars);

        /*if ( (isset($this->options['min_length'])) && (count($chars) < $this->options['min_length']) ) {
            return null;
        }*/

        // pour chaque chars, on va checker s'il est déjà dedans
        // lastFound va contenir la position du dernier stage trouvé pour le mot et la position dans $chars
        //$start = microtime(1);
        $lastFound = $this->switchBoard->scan($chars);

        if ($lastFound['charsIndex'] == count($chars)) {

            // on charge le Stage qu'on vient de trouver
            $stage = new Stage($this->switchBoard, $lastFound['index']);

            // enfin l'id du 1er StagePart
            $id = (int)$stage->getFirstPart()->getId();

            // sinon, reste des stages à insérer
        } else {

            // Un 1ier stagepart pour compléter l'annuaire de la dernière lettre trouvée
            // le stage existe déjà, on a donc déjà un 1er StagePart avec un id
            $stage = new Stage($this->switchBoard, $lastFound['index']);
            $stage->addPart(array(
                $chars[$lastFound['charsIndex']] => ($this->switchBoard->lastIndex() + CC::FULL_STAGE_PART_SIZE)
            ), 0);

            // tous les nouveaux Stage et pour chacun une StagePart, à la fin et avec un id
            $stage = new Stage($this->switchBoard);
            for ($i = ($lastFound['charsIndex'] + 1); $i < count($chars); $i++) {

                // le dernier Id +1
                $id = $this->id->increment();

                $stage->addPart(array(
                    $chars[$i] => ($this->switchBoard->lastIndex() + CC::FULL_STAGE_PART_SIZE)
                ), $id);
            }

            // le dernier Id +1
            $id = (int)$this->id->increment();
            //$this->id->flush();

            // le dernier stage, ne contient qu'un id
            $stage->addPart(null, $id);
        }

        return $id;
    }

    public function flush(): void
    {
        $this->switchBoard->flush();
        $this->id->flush();
    }
}
