<?php

namespace Wamania\BrewSearch\Dictionary\Stage;

use Wamania\BrewSearch\Dictionary\SwitchBoard;

class Stage
{
    /**
     * parts of global annuaire
     * @var StagePartFormater
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
    private $read;

    /**
     * Constructor
     * @param integer $index Index of first part
     */
    public function __construct($switchBoard, $index = null)
    {
        $this->switchBoard = $switchBoard;
        $this->index = $index;
        $this->parts = array();
        $this->read = false;
    }

    public function getAnnuaire()
    {
        if (false === $this->read) {
            $this->read();
        }

        $annuaire = array();
        foreach ($this->parts as $part) {
            $annuaire += $part->getAnnuaire();
        }

        return $annuaire;
    }

    /**
     * Read the whole annuaire
     * @return void
     */
    public function read()
    {
        $next = $this->index;

        do {
            $part = new StagePartReader($this->switchBoard, $next);
            $part->read();
            $next = $part->getNext();
            $this->parts[] = $part;

        } while ($next != 0);

        $this->read = true;
    }

    public function getParts()
    {
        if (empty($this->parts)) {
            $this->read();
        }
        return $this->parts;
    }

    public function getFirstPart()
    {
        if (false === $this->read) {
            $this->read();
        }

        return $this->parts[0];
    }

    public function getIds()
    {
        if (false === $this->read) {
            $this->read();
        }

        $ids = array();

        foreach ($this->parts as $part) {
            $id = $part->getId();

            if (!empty($id)) {
                $ids[$id] = true;
            }
        }

        return $ids;
    }

    /*public function getId()
    {
        if (false === $this->read) {
            $this->read();
        }

        foreach ($this->parts as $part) {
            $id = $part->getId();

            if (0 != $id) {
                return $id;
            }
        }

        return null;
    }*/

    /**
     * Add StagePart at the end of the switchBoard
     *
     * @param array|null $annuaire
     * @param integer $id
     */
    public function addPart($annuaire, $id = 0)
    {
        //$start = microtime(1);

        $insertIndex = $this->switchBoard->lastIndex();

        //echo "         get last insert | ".(microtime(1)-$start)."\n";
        //$start = microtime(1);

        // on va mettre à jour le next du dernier part précédemment ajouté
        if (null !== $this->index) {
            if (false === $this->read) {
                $this->read();
            }

            // le dernier StagePart inséré
            $lastPart = $this->parts[(count($this->parts) - 1)];
            $formater = new StagePartFormater();
            $formater->setStagePartReader($lastPart);
            $formater->setNext($insertIndex);

            /*echo "Replace next at: ".$lastPart->getIndex()." By : ".$insertIndex."\n";
            echo "Replacement string: ".$formater."\n";
            echo "Replacement size: ".strlen($formater)."\n";*/

            $this->switchBoard->replace($formater, $lastPart->getIndex());

            //echo "         replace last | ".(microtime(1)-$start)."\n";
            //$start = microtime(1);
        }

        $formater = new StagePartFormater();
        $formater->setAnnuaire($annuaire);
        $formater->setId($id);

        //echo "         formatter | ".(microtime(1)-$start)."\n";
        //$start = microtime(1);

        $this->switchBoard->add($formater);

        //echo "         add | ".(microtime(1)-$start)."\n";
    }
}