<?php

namespace Wamania\BrewSearch\Dictionary\Stage;

use Wamania\BrewSearch\Dictionary\Exception\NoFirstPartException;
use Wamania\BrewSearch\Dictionary\SwitchBoard;

class Stage
{
    /**
     * Parts of annuaire
     * @var array
     */
    private $parts;

    /**
     * Dictionary in which we search
     * @var SwitchBoard
     */
    private $switchBoard;

    /**
     * Index of first part
     * @var integer
     */
    private $index;

    /**
     * Is whole stage read ?
     * @var boolean
     */
    private $readed;

    public function __construct(SwitchBoard $switchBoard, int $index = null)
    {
        $this->switchBoard = $switchBoard;
        $this->index = $index;
        $this->parts = [];
        $this->readed = false;
    }

    public function getAnnuaire(): array
    {
        if (false === $this->readed) {
            $this->read();
        }

        $annuaire = [];
        foreach ($this->parts as $part) {
            $annuaire += $part->getAnnuaire();
        }

        return $annuaire;
    }

    /**
     * Read the whole annuaire
     */
    private function read(): void
    {
        $next = $this->index;

        do {
            $part = new StagePartReader($this->switchBoard, $next);
            $next = $part->getNext();
            $this->parts[] = $part;

        } while ($next !== 0);

        $this->read = true;
    }

    public function getParts(): array
    {
        if (false === $this->readed) {
            $this->read();
        }

        return $this->parts;
    }

    /**
     * @throws NoFirstPartException
     */
    public function getFirstPart(): StagePartReader
    {
        if (false === $this->readed) {
            $this->read();
        }

        if (!isset($this->parts[0])) {
            throw new NoFirstPartException();
        }

        return $this->parts[0];
    }

    /**
     * Add StagePart at the end of the switchBoard
     */
    public function addPart(array $annuaire, int $id = 0): void
    {
        $insertIndex = $this->switchBoard->lastIndex();

        // on va mettre à jour le next du dernier part précédemment ajouté
        if (null !== $this->index) {
            if (false === $this->readed) {
                $this->read();
            }

            // replace last existing stagePart
            $lastPart = $this->parts[(count($this->parts) - 1)];
            $formater = new StagePartFormater();
            $formater->setStagePartReader($lastPart);
            $formater->setNext($insertIndex);

            $this->switchBoard->replace($formater->toString(), $lastPart->getIndex());
        }

        // add new stagePart for the id
        $formater = new StagePartFormater();
        $formater->setAnnuaire($annuaire);
        $formater->setId($id);

        $this->switchBoard->add($formater);
    }
}
