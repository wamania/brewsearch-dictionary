<?php

namespace Wamania\BrewSearch\Dictionary\Stage;

use Wamania\BrewSearch\Dictionary\Constant as CC;
use Wamania\BrewSearch\Dictionary\SwitchBoard;
use Wamania\BrewSearch\Dictionary\Utils\Utils;

class StagePartReader
{
    /**
     * Dictionary in which we search
     * @var SwitchBoard
     */
    private $switchBoard;

    /**
     * Index du stage dans le fichier
     * @var integer
     */
    private $index;

    /**
     * Taille de l'annuaire
     * @var integer
     */
    private $annuaireSize;

    /**
     * Annuaire
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
    private $read;

    /**
     * Constructor
     * @param integer $index Index du stage dans le fichier
     */
    public function __construct(SwitchBoard $switchboard, int $index)
    {
        $this->switchBoard = $switchboard;
        $this->index = $index;
        $this->annuaireSize = null;
        $this->annuaire = array();
        $this->id = null;
        $this->next = null;
        $this->read = false;
    }

    public function getAnnuaire()
    {
        if ((null === $this->annuaire) && (false === $this->read)) {
            $this->read();
        }

        return $this->annuaire;
    }

    public function read()
    {
        $this->readAnnuaireSize();
        $this->readAnnuaire();
        $this->readId();
        $this->readNext();

        $this->read = true;
    }

    private function readAnnuaireSize()
    {
        $annuaireSize = $this->switchBoard->extract($this->index, CC::ANNUAIRE_SIZE_BYTES);
        //$annuaireSize = substr($this->catalog, $this->index, CC::ANNUAIRE_SIZE_BYTES);
        $this->annuaireSize = Utils::unpack($annuaireSize, CC::ANNUAIRE_SIZE_BYTES);
    }

    private function readAnnuaire()
    {
        if (null === $this->annuaireSize) {
            $this->readAnnuaireSize();
        }

        $this->annuaire = array();
        for ($i = 0; $i < $this->annuaireSize; $i += (CC::LETTER_BYTES + CC::LETTER_POSITION_BYTES)) {
            //$letter = substr($this->catalog, ($catalogIndex + $i  + CC::ANNUAIRE_SIZE_BYTES), CC::LETTER_BYTES);
            $letter = $this->switchBoard->extract(($this->index + $i + CC::ANNUAIRE_SIZE_BYTES), CC::LETTER_BYTES);
            $letter = Utils::unpack($letter, CC::LETTER_BYTES);

            //$position = substr($this->catalog, ($catalogIndex + $i + CC::ANNUAIRE_SIZE_BYTES + CC::LETTER_BYTES), CC::LETTER_POSITION_BYTES);
            $position = $this->switchBoard->extract(($this->index + $i + CC::ANNUAIRE_SIZE_BYTES + CC::LETTER_BYTES), CC::LETTER_POSITION_BYTES);
            $position = Utils::unpack($position, CC::LETTER_POSITION_BYTES);

            $this->annuaire[$letter] = $position;
        }
    }

    private function readId()
    {
        //$id = substr($this->catalog, ($this->index + CC::ANNUAIRE_SIZE_BYTES + $this->annuaireSize), CC::ID_BYTES);
        $id = $this->switchBoard->extract(($this->index + CC::ANNUAIRE_SIZE_BYTES + $this->annuaireSize), CC::ID_BYTES);
        $this->id = Utils::unpack($id, CC::ID_BYTES);
    }

    private function readNext()
    {
        //$next = substr($this->catalog, ($this->index + CC::ANNUAIRE_SIZE_BYTES + $this->annuaireSize + CC::ID_BYTES), CC::NEXT_PART_BYTES);
        $next = $this->switchBoard->extract(($this->index + CC::ANNUAIRE_SIZE_BYTES + $this->annuaireSize + CC::ID_BYTES), CC::NEXT_PART_BYTES);
        $this->next = Utils::unpack($next, CC::NEXT_PART_BYTES);
    }

    public function getAnnuaireSize()
    {
        if ((null === $this->annuaireSize) && (false === $this->read)) {
            $this->read();
        }

        return $this->annuaireSize;
    }

    public function getId()
    {
        if ((null === $this->id) && (false === $this->read)) {
            $this->read();
        }

        return $this->id;
    }

    public function getNext()
    {
        if ((null === $this->next) && (false === $this->read)) {
            $this->read();
        }

        return $this->next;
    }

    public function getIndex()
    {
        return $this->index;
    }
}