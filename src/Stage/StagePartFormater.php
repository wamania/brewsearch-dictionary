<?php

namespace Wamania\BrewSearch\Dictionary\Stage;

use Wamania\BrewSearch\Dictionary\Constant as CC;
use Wamania\BrewSearch\Dictionary\Utils\Pack;

class StagePartFormater
{
    /** @var integer */
    private $annuaireSize;

    /** @var array */
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

    public function __construct()
    {
        $this->annuaireSize = 0;
        $this->annuaire = array();
        $this->id = 0;
        $this->next = 0;
    }

    public function setAnnuaire(array $annuaire): void
    {
        $this->annuaire = $annuaire;
        $this->annuaireSize = (CC::LETTER_BYTES + CC::LETTER_POSITION_BYTES) * count($annuaire);
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setNext(int $next): void
    {
        $this->next = $next;
    }

    public function setStagePartReader(StagePartReader $stagePartReader): void
    {
        $this->setAnnuaire($stagePartReader->getAnnuaire());
        $this->setId($stagePartReader->getId());
        $this->setNext($stagePartReader->getNext());
    }

    public function toString(): string
    {
        $str = Pack::pack($this->annuaireSize, CC::ANNUAIRE_SIZE_BYTES);

        if (null != $this->annuaire) {
            foreach ($this->annuaire as $letter => $position) {
                $str .= Pack::pack($letter, CC::LETTER_BYTES);
                $str .= Pack::pack($position, CC::LETTER_POSITION_BYTES);
            }
        }

        $str .= Pack::pack($this->id, CC::ID_BYTES);
        $str .= Pack::pack($this->next, CC::NEXT_PART_BYTES);

        return $str;
    }
}
