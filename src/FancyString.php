<?php
	namespace Adepto\Fancy;

	use function Stringy\create as s;

	/**
	 * FancyString
	 * Commonly used functions for working with strings, backed by Stringy.
	 *
	 * @author bluefirex, FeistyBall
	 * @version 1.0
	 * @package as.adepto.fancy
	 */
	abstract class FancyString {

		/**
		 * Convert a string to kebap-case, i.e.:
		 *     some_string -> some-string
		 *     someString -> some-string
		 *
		 * @param  string $str String to convert
		 *
		 * @return string
		 */
		public static function toKebapCase(string $str): string {
			return (string) s($str)->dasherize();
		}

		/**
		 * Convert a string to snake_case, i.e.:
		 *     some-string -> some_string
		 *     someString -> some_string
		 *
		 * @param  string $str String to convert
		 *
		 * @return string
		 */
		public static function toSnakeCase(string $str): string {
			return (string) s($str)->underscored();
		}

		/**
		 * Convert a string to camelCase, i.e.:
		 *     some-string -> someString
		 *     some_string -> someString
		 *
		 * @param  string $str String to convert
		 *
		 * @return string
		 */
		public static function toCamelCase(string $str): string {
			return (string) s($str)->camelize();
		}

		/**
		 * Convert a string to lowercase, correctly converting local
		 * characters, i.e. Ø -> ø
		 *
		 * @param  string $str String to convert
		 *
		 * @return string
		 */
		public static function toLowerCase(string $str): string {
			return (string) s($str)->toLowercase();
		}

		/**
		 * Convert a string to uppercase, correctly converting local
		 * characters, i.e. ß -> SS.
		 *
		 * @param  string $str String to convert
		 *
		 * @return string
		 */
		public static function toUpperCase(string $str): string {
			return (string) s($str)->toUppercase()->replace('ß', 'SS');
		}

		/**
		 * Add an ellipsis (…) to the center of the string if it is too long.
		 *
		 * @param  int    $str    String to shorten
		 * @param  int    $maxLen Maximum Length ($char included)
		 * @param  int 	  $char   Char to use (default: …)
		 *
		 * @return string         String with ellipsis in the center
		 */
		public static function ellipsisCenter($str, $maxLen, $char = '…'): string {
			if (mb_strlen($str) > ($maxLen + mb_strlen($char))) {
				$characters = floor($maxLen / 2);
				
				return mb_substr($str, 0, $characters) . $char . mb_substr($str, -1 * $characters);
			}

			return $str;
		}

		/**
		 * Add an ellipsis (…) to the end of the string if it is too long.
		 *
		 * @param  int    $str    String to shorten
		 * @param  int    $maxLen Maximum Length ($char included)
		 * @param  int 	  $char   Char to use (default: …)
		 *
		 * @return string         String with ellipsis at the end
		 */
		public static function ellipsisEnd($str, $maxLen, $char = '…'): string {
			if (mb_strlen($str) > ($maxLen + mb_strlen($char))) {
				return mb_substr($str, 0, $maxLen) . $char;
			}

			return $str;
		}

		/**
		 * Generate a random string based on a character set.
		 *
		 * @param  integer $length  Length of the random string
		 * @param  string  $charset Chars to be used for the random string
		 *
		 * @return string           Random String
		 */
		public static function randString($length = 12, $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string {
			$str = '';

			for ($i = 0; $i < $length; $i++) {
				$str .= $charset[mt_rand(0, strlen($charset) - 1)];
			}

			return $str;
		}

		/*
		 * Remove whitespace from $var.
		 * This is useful for comparing values which can contain
		 * whitespaces.
		 *
		 * @param  mixed $var  Variable, either an array or a string
		 *
		 * @return mixed       Returns a cleaned array or string
		 */
		public static function removeWhitespace($var) {
			if (is_array($var)) {
				return array_map(function($val) {
					return removeWhitespace($val);
				}, $var);
			}
			
			return preg_replace('^|\s^', '', $var);
		}

		/**
		 * Returns the text in a way that humans and computers can read it.
		 * 
		 * @param  string $text the text to slugify
		 * 
		 * @return string       the slugified text
		 */
		public static function slugify($text) {
			// replace non letter or digits by -
			$text = preg_replace('~[^\pL\d]+~u', '-', $text);

			// transliterate
			$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

			// remove unwanted characters
			$text = preg_replace('~[^-\w]+~', '', $text);

			// trim
			$text = trim($text, '-');

			// remove duplicate -
			$text = preg_replace('~-+~', '-', $text);

			// lowercase
			$text = strtolower($text);

			if (empty($text)) {
				return 'n-a';
			}

			return $text;
		}
	}
