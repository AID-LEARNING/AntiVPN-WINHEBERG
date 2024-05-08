<?php

namespace AntiVPN\utils;

use pmmp\thread\ThreadSafe;
use pmmp\thread\ThreadSafeArray;
use pocketmine\thread\NonThreadSafeValue;

class Utils
{

	public static function arrayToThread(array $array): ThreadSafeArray
	{
		$threadArray = new ThreadSafeArray();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$threadArray[$key] = Utils::arrayToThread($value);
			} elseif (!(is_scalar($value) || is_null($value) || $value instanceof ThreadSafe)){
				$threadArray[$key] = igbinary_serialize($value);
			}else{
				$threadArray[$key] = $value;
			}
		}
		return $threadArray;
	}

	public static function threadToArray(ThreadSafeArray $threadArray): array
	{
		$array = [];
		foreach ($threadArray as $key => $value) {
			if ($value instanceof NonThreadSafeValue) {
				$array[$key] = $value->deserialize();
			}elseif($value instanceof ThreadSafeArray){
				$array[$key] = self::threadToArray($value);
			} else{
				$array[$key] = $value;
			}
		}
		return $array;
	}

}