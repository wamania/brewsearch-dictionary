<?php

namespace Wamania\BrewSearch\Dictionary;

class Constant
{
    /**
     * Size (in bytes) of the size of the annuaire
     * (annuaire size is dynamic)
     *
     * @var integer
     */
    const ANNUAIRE_SIZE_BYTES = 1;

    /**
     * Size of each letter (in bytes)
     *
     * @var integer
     */
    const LETTER_BYTES = 2;

    /**
     * Size of the position of a letter (in bytes)
     *
     * @var integer
     */
    const LETTER_POSITION_BYTES = 4;

    /**
     * Size the id (in bytes)
     *
     * @var integer
     */
    const ID_BYTES = 4;

    /**
     * Size of the position of the next part (in bytes)
     *
     * @var integer
     */
    const NEXT_PART_BYTES = 4;

    /**
     * self::ANNUAIRE_SIZE_BYTES
     *	+ self::LETTER_BYTES + self::LETTER_POSITION_BYTES
     *	+ self::ID_BYTES
     *	+ self::NEXT_PART_BYTES
     *
     * @var integer
     */
    const FULL_STAGE_PART_SIZE = 15;
}