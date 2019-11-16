<?php

namespace Wamania\BrewSearch\Dictionary\Stage;

use Wamania\BrewSearch\Dictionary\Constant as CC;
use Wamania\BrewSearch\Dictionary\SwitchBoard;
use Wamania\BrewSearch\Dictionary\Utils\Pack;

class StagePartReader
{
    /**
     * Dictionary in which we search
     * @var SwitchBoard
     */
    private $switchBoard;

    /**
     * Index of this stage
     * @var integer
     */
    private $index;

    /**
     * @var integer
     */
    private $annuaireSize;

    /**
     * @var array
     */
    private $annuaire;

    /**
     * Id of the word
     * @var integer
     */
    private $id;

    /**
     * Next part of this annuaire (0 if last)
     * @var integer
     */
    private $next;

    /**
     * Has been read ?
     * @var boolean
     */
    private $readed;

    public function __construct(SwitchBoard $switchBoard, int $index)
    {
        $this->switchBoard = $switchBoard;
        $this->index = $index;
        $this->annuaireSize = null;
        $this->annuaire = [];
        $this->id = null;
        $this->next = null;
        $this->readed = false;
    }

    public function getAnnuaire(): array
    {
        if ((null === $this->annuaire) && (false === $this->readed)) {
            $this->read();
        }

        return $this->annuaire;
    }

    private function read(): void
    {
        $this->readAnnuaireSize();
        $this->readAnnuaire();
        $this->readId();
        $this->readNext();

        $this->readed = true;
    }

    private function readAnnuaireSize(): void
    {
        $annuaireSize = $this->switchBoard->extract($this->index, CC::ANNUAIRE_SIZE_BYTES);
        $this->annuaireSize = Pack::unpack($annuaireSize, CC::ANNUAIRE_SIZE_BYTES);
    }

    private function readAnnuaire(): void
    {
        if (null === $this->annuaireSize) {
            $this->readAnnuaireSize();
        }

        $this->annuaire = array();
        for ($i = 0; $i < $this->annuaireSize; $i += (CC::LETTER_BYTES + CC::LETTER_POSITION_BYTES)) {
            $letter = $this->switchBoard->extract(($this->index + $i + CC::ANNUAIRE_SIZE_BYTES), CC::LETTER_BYTES);
            $letter = Pack::unpack($letter, CC::LETTER_BYTES);

            $position = $this->switchBoard->extract(($this->index + $i + CC::ANNUAIRE_SIZE_BYTES + CC::LETTER_BYTES), CC::LETTER_POSITION_BYTES);
            $position = Pack::unpack($position, CC::LETTER_POSITION_BYTES);

            $this->annuaire[$letter] = $position;
        }
    }

    private function readId(): void
    {
        $id = $this->switchBoard->extract(($this->index + CC::ANNUAIRE_SIZE_BYTES + $this->annuaireSize), CC::ID_BYTES);
        $this->id = Pack::unpack($id, CC::ID_BYTES);
    }

    private function readNext(): void
    {
        $next = $this->switchBoard->extract(($this->index + CC::ANNUAIRE_SIZE_BYTES + $this->annuaireSize + CC::ID_BYTES), CC::NEXT_PART_BYTES);
        $this->next = Pack::unpack($next, CC::NEXT_PART_BYTES);
    }

    public function getAnnuaireSize(): int
    {
        if (false === $this->readed) {
            $this->read();
        }

        return $this->annuaireSize;
    }

    public function getId(): ?int
    {
        if (false === $this->readed) {
            $this->read();
        }

        return $this->id;
    }

    public function getNext(): ?int
    {
        if (false === $this->readed) {
            $this->read();
        }

        return $this->next;
    }

    public function getIndex(): int
    {
        return $this->index;
    }
}
