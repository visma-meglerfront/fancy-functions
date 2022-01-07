<?php
	namespace Adepto\Fancy;
	
	/**
	 * Class FancyMath
	 * Does some FancyMaths
	 *
	 * @author suushie_maniac, FeistyBall
	 * @version 1.0
	 * @package as.adepto.fancy
	 *
	 */
	abstract class FancyMath {
		/**
		 * Permute values.
		 *
		 * @param array $values     values
		 * @param int   $length     length of the permutations
		 * @param bool  $repetitive repeat permutations allowed?
		 * @param bool  $ordered    order of the values important?
		 * @param bool  $inclusive  include permutations that are smaller than length?
		 * @param int   $bottom     limit inclusive
		 *
		 * @return array
		 */
		public static function permute(array $values, int $length, bool $repetitive = false, bool $ordered = true, bool $inclusive = false, int $bottom = 1): array {
			if ($length <= 1 || $length > 7) {
				return array_map(function($value) {
					return [$value];
				}, $values);
			}

			$permuted = [];
			$subPermuted = self::permute($values, $length - 1, $repetitive, $ordered, false, $length);

			foreach ($values as $value) {
				foreach ($subPermuted as $subValue) {
					if ($repetitive || !in_array($value, $subValue)) {
						$newValue = array_merge([$value], $subValue);

						if (!$ordered) {
							foreach ($permuted as $oldValue) {
								if (array_count_values($oldValue) == array_count_values($newValue)) {
									continue 2;
								}
							}
						}

						$permuted[] = $newValue;
					}
				}
			}

			return $inclusive && $length > $bottom ? array_merge($permuted, self::permute($values, $length - 1, $repetitive, $ordered, true, $bottom)) : $permuted;
		}

		/**
		 * Calculates binomial coefficient.
		 *
		 * @param int $n The top thingy
		 * @param int $k The bottom thingy
		 *
		 * @return int
		 */
		public static function binCoeff(int $n, int $k): int {
			return self::fact($n) / (self::fact($k) * self::fact($n - $k));
		}

		/**
		 * Calculates factorial.
		 * CAUTION: Gets v-e-r-y big very quick
		 *
		 * @param int $n The base for factorial
		 *
		 * @return int
		 */
		public static function fact(int $n): int {
			if ($n <= 1) return 1;
			return $n * self::fact($n - 1);
		}

		/**
		 * Calculates limited factorials as pseudo-power.
		 *
		 * @param int $n   base
		 * @param int $exp exponent: how often to multiply
		 *
		 * @return int
		 */
		public static function powLimit(int $n, int $exp): int {
			if ($exp <= 0) return 1;
			if ($exp == 1) return $n;
			return $n * self::powLimit($n - 1, $exp - 1);
		}
	}