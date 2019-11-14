<?php

namespace Wamania\BrewSearch\Dictionary;

class Constant
{
    /**
     * Taille de l'annuaire sur 8bits
     */
    const ANNUAIRE_SIZE_BYTES = 1;

    /**
     * Lettres sur 16bits
     */
    const LETTER_BYTES = 2;

    /**
     * Position des lettres sur 32bits
     */
    const LETTER_POSITION_BYTES = 4;

    /**
     * Id sur 32bits
     * @SwitchBoard
     */
    const ID_BYTES = 4;

    /**
     * Next part of annuaire sur 32bits
     */
    const NEXT_PART_BYTES = 4;

    /**
     * self::ANNUAIRE_SIZE_BYTES
     *	+ self::LETTER_BYTES + self::LETTER_POSITION_BYTES
     *	+ self::ID_BYTES
     *	+ self::NEXT_PART_BYTES
     * @var integer
     */
    const FULL_STAGE_PART_SIZE = 15;
}