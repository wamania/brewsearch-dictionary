<?php

namespace Wamania\BrewSearch\Dictionary\Stage;

use Wamania\BrewSearch\Dictionary\Constant as CC;
use Wamania\BrewSearch\Dictionary\Utils\Utils;

class StagePartFormater
{
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
     * Constructor
     */
    public function __construct()
    {
        $this->annuaireSize = 0;
        $this->annuaire = array();
        $this->id = 0;
        $this->next = 0;
    }

    public function getAnnuaireSize()
    {
        return $this->annuaireSize;
    }

    public function getAnnuaire()
    {
        return $this->annuaire;
    }

    public function setAnnuaire($annuaire)
    {
        $this->annuaire = $annuaire;
        $this->annuaireSize = (CC::LETTER_BYTES + CC::LETTER_POSITION_BYTES) * count($annuaire);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getNext()
    {
        return $this->next;
    }

    public function setNext($next)
    {
        $this->next = $next;
    }

    public function setStagePartReader(StagePartReader $stagePartReader)
    {
        $this->setAnnuaire($stagePartReader->getAnnuaire());
        $this->setId($stagePartReader->getId());
        $this->setNext($stagePartReader->getNext());
    }

    public function __toString()
    {
        $str = Utils::pack($this->annuaireSize, CC::ANNUAIRE_SIZE_BYTES);

        if (null != $this->annuaire) {
            foreach ($this->annuaire as $letter => $position) {
                $str .= Utils::pack($letter, CC::LETTER_BYTES);
                $str .= Utils::pack($position, CC::LETTER_POSITION_BYTES);
            }
        }

        $str .= Utils::pack($this->id, CC::ID_BYTES);
        $str .= Utils::pack($this->next, CC::NEXT_PART_BYTES);

        return $str;
    }
}