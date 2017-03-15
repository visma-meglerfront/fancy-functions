<?php
	namespace Adepto\Fancy;

	/**
	 * Class FancyNLP
	 * Includes utility methods for working with natural language
	 *
	 * @author suushie_maniac, FeistyBall
	 * @version 1.0
	 * @package as.adepto.fancy
	 */
	abstract class FancyNLP {
		// "DEFAULT" set stolen from http://stackoverflow.com/a/16427125
		const LANGUAGE_SYMBOLS = [
			'ъ' => '-', 'Ь' => '-', 'Ъ' => '-', 'ь' => '-',
			'Ă' => 'A', 'Ą' => 'A', 'À' => 'A', 'Ã' => 'A', 'Á' => 'A', 'Æ' => 'AE', 'Â' => 'A', 'Å' => 'A', 'Ä' => 'Ae',
			'Þ' => 'B',
			'Ć' => 'C', 'ץ' => 'C', 'Ç' => 'C',
			'È' => 'E', 'Ę' => 'E', 'É' => 'E', 'Ë' => 'E', 'Ê' => 'E',
			'Ğ' => 'G',
			'İ' => 'I', 'Ï' => 'I', 'Î' => 'I', 'Í' => 'I', 'Ì' => 'I',
			'Ł' => 'L',
			'Ñ' => 'N', 'Ń' => 'N',
			'Ø' => 'O', 'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'Oe',
			'Ş' => 'S', 'Ś' => 'S', 'Ș' => 'S', 'Š' => 'S',
			'Ț' => 'T',
			'Ù' => 'U', 'Û' => 'U', 'Ú' => 'U', 'Ü' => 'Ue',
			'Ý' => 'Y',
			'Ź' => 'Z', 'Ž' => 'Z', 'Ż' => 'Z',
			'â' => 'a', 'ǎ' => 'a', 'ą' => 'a', 'á' => 'a', 'ă' => 'a', 'ã' => 'a', 'Ǎ' => 'a', 'а' => 'a', 'А' => 'a', 'å' => 'a', 'à' => 'a', 'א' => 'a', 'Ǻ' => 'a', 'Ā' => 'a', 'ǻ' => 'a', 'ā' => 'a', 'ä' => 'ae', 'æ' => 'ae', 'Ǽ' => 'ae', 'ǽ' => 'ae',
			'б' => 'b', 'ב' => 'b', 'Б' => 'b', 'þ' => 'b',
			'ĉ' => 'c', 'Ĉ' => 'c', 'Ċ' => 'c', 'ć' => 'c', 'ç' => 'c', 'ц' => 'c', 'צ' => 'c', 'ċ' => 'c', 'Ц' => 'c', 'Č' => 'c', 'č' => 'c', 'Ч' => 'ch', 'ч' => 'ch',
			'ד' => 'd', 'ď' => 'd', 'Đ' => 'd', 'Ď' => 'd', 'đ' => 'd', 'д' => 'd', 'Д' => 'D', 'ð' => 'd',
			'є' => 'e', 'ע' => 'e', 'е' => 'e', 'Е' => 'e', 'Ə' => 'e', 'ę' => 'e', 'ĕ' => 'e', 'ē' => 'e', 'Ē' => 'e', 'Ė' => 'e', 'ė' => 'e', 'ě' => 'e', 'Ě' => 'e', 'Є' => 'e', 'Ĕ' => 'e', 'ê' => 'e', 'ə' => 'e', 'è' => 'e', 'ë' => 'e', 'é' => 'e',
			'ф' => 'f', 'ƒ' => 'f', 'Ф' => 'f',
			'ġ' => 'g', 'Ģ' => 'g', 'Ġ' => 'g', 'Ĝ' => 'g', 'Г' => 'g', 'г' => 'g', 'ĝ' => 'g', 'ğ' => 'g', 'ג' => 'g', 'Ґ' => 'g', 'ґ' => 'g', 'ģ' => 'g',
			'ח' => 'h', 'ħ' => 'h', 'Х' => 'h', 'Ħ' => 'h', 'Ĥ' => 'h', 'ĥ' => 'h', 'х' => 'h', 'ה' => 'h',
			'î' => 'i', 'ï' => 'i', 'í' => 'i', 'ì' => 'i', 'į' => 'i', 'ĭ' => 'i', 'ı' => 'i', 'Ĭ' => 'i', 'И' => 'i', 'ĩ' => 'i', 'ǐ' => 'i', 'Ĩ' => 'i', 'Ǐ' => 'i', 'и' => 'i', 'Į' => 'i', 'י' => 'i', 'Ї' => 'i', 'Ī' => 'i', 'І' => 'i', 'ї' => 'i', 'і' => 'i', 'ī' => 'i', 'ĳ' => 'ij', 'Ĳ' => 'ij',
			'й' => 'j', 'Й' => 'j', 'Ĵ' => 'j', 'ĵ' => 'j', 'я' => 'ja', 'Я' => 'ja', 'Э' => 'je', 'э' => 'je', 'ё' => 'jo', 'Ё' => 'jo', 'ю' => 'ju', 'Ю' => 'ju',
			'ĸ' => 'k', 'כ' => 'k', 'Ķ' => 'k', 'К' => 'k', 'к' => 'k', 'ķ' => 'k', 'ך' => 'k',
			'Ŀ' => 'l', 'ŀ' => 'l', 'Л' => 'l', 'ł' => 'l', 'ļ' => 'l', 'ĺ' => 'l', 'Ĺ' => 'l', 'Ļ' => 'l', 'л' => 'l', 'Ľ' => 'l', 'ľ' => 'l', 'ל' => 'l',
			'מ' => 'm', 'М' => 'm', 'ם' => 'm', 'м' => 'm',
			'ñ' => 'n', 'н' => 'n', 'Ņ' => 'n', 'ן' => 'n', 'ŋ' => 'n', 'נ' => 'n', 'Н' => 'n', 'ń' => 'n', 'Ŋ' => 'n', 'ņ' => 'n', 'ŉ' => 'n', 'Ň' => 'n', 'ň' => 'n',
			'о' => 'o', 'О' => 'o', 'ő' => 'o', 'õ' => 'o', 'ô' => 'o', 'Ő' => 'o', 'ŏ' => 'o', 'Ŏ' => 'o', 'Ō' => 'o', 'ō' => 'o', 'ø' => 'o', 'ǿ' => 'o', 'ǒ' => 'o', 'ò' => 'o', 'Ǿ' => 'o', 'Ǒ' => 'o', 'ơ' => 'o', 'ó' => 'o', 'Ơ' => 'o', 'œ' => 'oe', 'Œ' => 'oe', 'ö' => 'oe',
			'פ' => 'p', 'ף' => 'p', 'п' => 'p', 'П' => 'p',
			'ק' => 'q',
			'ŕ' => 'r', 'ř' => 'r', 'Ř' => 'r', 'ŗ' => 'r', 'Ŗ' => 'r', 'ר' => 'r', 'Ŕ' => 'r', 'Р' => 'r', 'р' => 'r',
			'ș' => 's', 'с' => 's', 'Ŝ' => 's', 'š' => 's', 'ś' => 's', 'ס' => 's', 'ş' => 's', 'С' => 's', 'ŝ' => 's', 'Щ' => 'sch', 'щ' => 'sch', 'ш' => 'sh', 'Ш' => 'sh', 'ß' => 'ss',
			'т' => 't', 'ט' => 't', 'ŧ' => 't', 'ת' => 't', 'ť' => 't', 'ţ' => 't', 'Ţ' => 't', 'Т' => 't', 'ț' => 't', 'Ŧ' => 't', 'Ť' => 't', '™' => 'tm',
			'ū' => 'u', 'у' => 'u', 'Ũ' => 'u', 'ũ' => 'u', 'Ư' => 'u', 'ư' => 'u', 'Ū' => 'u', 'Ǔ' => 'u', 'ų' => 'u', 'Ų' => 'u', 'ŭ' => 'u', 'Ŭ' => 'u', 'Ů' => 'u', 'ů' => 'u', 'ű' => 'u', 'Ű' => 'u', 'Ǖ' => 'u', 'ǔ' => 'u', 'Ǜ' => 'u', 'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'У' => 'u', 'ǚ' => 'u', 'ǜ' => 'u', 'Ǚ' => 'u', 'Ǘ' => 'u', 'ǖ' => 'u', 'ǘ' => 'u', 'ü' => 'ue',
			'в' => 'v', 'ו' => 'v', 'В' => 'v',
			'ש' => 'w', 'ŵ' => 'w', 'Ŵ' => 'w',
			'ы' => 'y', 'ŷ' => 'y', 'ý' => 'y', 'ÿ' => 'y', 'Ÿ' => 'y', 'Ŷ' => 'y',
			'Ы' => 'y', 'ž' => 'z', 'З' => 'z', 'з' => 'z', 'ź' => 'z', 'ז' => 'z', 'ż' => 'z', 'ſ' => 'z', 'Ж' => 'zh', 'ж' => 'zh',
			'-' => ' '
		];

		/**
		 * Compares two words, including the possibility to accept a given margin of error
		 * This is to avoid typos as specified by Levenshtein algorithm
		 *
		 * @param string $input The (user) input to check
		 * @param string $match The "correct target word"
		 * @param int $errorMargin The number of typos to accept, specified as Levenshtein distance
		 *
		 * @return bool If the two words are similar or "identical" given the specified margin of error
		 */
		public static function is(string $input, string $match, int $errorMargin = 0): bool {
			if ($input == $match) {
				return true;
			} else {
				return levenshtein($input, $match) <= $errorMargin;
			}
		}

		/**
		 * Generate a replacement string where special symbols from a lot of languages
		 * are replaced by standard ISO alphanumeric characters
		 *
		 * @param string $input The input word to walk over
		 *
		 * @return string The standardized string
		 */
		public static function extrapolateSymbols(string $input) {
			$usedDefaults = array_filter(self::LANGUAGE_SYMBOLS, function ($key) use ($input) {
				return mb_strpos($input, $key) !== false;
			}, ARRAY_FILTER_USE_KEY);

			$defSymbols = array_keys($usedDefaults);
			$defReplacements = array_values($usedDefaults);

			return str_replace($defSymbols, $defReplacements, $input);
		}

		/**
		 * Generate a replacement set of strings where special symbols from a lot of languages
		 * COULD BE replaced by standard ISO alphanumeric characters
		 *
		 * @param string $input The input word to walk over
		 *
		 * @return array All possible extrapolated ISO strings
		 */
		public static function permuteAndExtrapolateSymbols(string $input): array {
			$solutions = [ [$input] ];

			$symbolSet = array_filter(self::LANGUAGE_SYMBOLS, function ($key) use ($input) {
				return mb_strpos($input, $key) !== false;
			}, ARRAY_FILTER_USE_KEY);

			$reducedSymbolSet = array_map(function ($key, $replacement) {
				return $key . ':=>:' . $replacement;
			}, array_keys($symbolSet), $symbolSet);

			$permutedSymbols = FancyMath::permute($reducedSymbolSet, count($reducedSymbolSet), false, false, true, 1);

			foreach ($permutedSymbols as $subset) {
				$replacementSet = [];

				foreach ($subset as $item) {
					$split = explode(':=>:', $item);

					if (count($split) != 2) {
						continue;
					}

					$replacementSet[$split[0]] = $split[1];
				}

				$symbols = array_keys($replacementSet);
				$replacements = array_values($replacementSet);

				$extrapolated = str_replace($symbols, $replacements, $input);
				$solutions[count($replacementSet)][] = $extrapolated;
			}

			return $solutions;
		}
	}