<?php

class Statistics
{
	public static function average(array $values)
	{
		return (float) (array_sum($values) / count($values));
	}

	public static function variance(array $values)
	{
		$ave = self::average($values);

		$variance = 0.0;
		foreach($values as $val) {
			$variance += pow($val - $ave, 2);
		}
		return (float) ($variance / count($values));
	}

	public static function standardDeviation(array $values)
	{
		$variance = self::variance($values);
		return (float) sqrt($variance);
	}

	public static function standardScore($target, array $arr)
	{
		return ($target - self::average($arr)) / self::standardDeviation($arr) * 10 + 50;
	}

	public static function updateDeviationArr($inputArr)
	{
		$retArr = array();
		foreach ($inputArr as $val) {
			array_push($retArr, self::standardScore($val, $inputArr));
		}
		return $retArr;
	}
}
