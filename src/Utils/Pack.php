<?php

namespace Wamania\BrewSearch\Dictionary\Utils;

class Pack
{
	public static function unpack($value, $size, $multi = false)
	{
		$format = self::packFormat($size);
		if ($multi) {
		    $format .= '*';
		}
		$unpack = unpack($format, $value);

		if ($multi) {
		    $return = $unpack;
        } else {
            $return = (isset($unpack[1]) ? $unpack[1] : null);
	   }

	   return $return;
	}

	public static function pack($value, $size)
	{
		$format = self::packFormat($size);
		return pack($format, $value);
	}

	public static function packFormat($size)
	{
	    if (!is_numeric($size)) {
	        return $size;
	    }

		$pack = null;

		switch($size) {
			case 4:
				$pack = 'V';
				break;
			case 2:
				$pack = 'S';
				break;
			case 1:
			default:
				$pack = 'C';
				break;
		}

		return $pack;
	}

	/**
	 * Pack an array of values
	 *
	 * @param string $format
	 * @param array $arr
	 * @return string
	 */
	public static function array_pack(array $arr, $size)
	{
	    $format = self::packFormat($size);
	    $format .= '*';
	    return call_user_func_array('pack', array_merge(array($format), $arr));
	}
}