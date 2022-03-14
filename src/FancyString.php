<?php
	namespace Adepto\Fancy;

	/**
	 * FancyString
	 * Commonly used functions for working with strings, backed by Stringy.
	 *
	 * @author bluefirex, FeistyBall
	 * @version 1.0
	 * @package as.adepto.fancy
	 */
	abstract class FancyString {
		
		private static function delimit(string $str, string $delimiter): string {
	        $str = mb_ereg_replace('\B([A-Z])', '-\1', trim($str));
	        $str = mb_strtolower($str);
	        $str = mb_ereg_replace('[-_\s]+', $delimiter, $str);
	
			return $str;
		}

		/**
		 * Convert a string to kebab-case, i.e.:
		 *     some_string -> some-string
		 *     someString -> some-string
		 *
		 * @param  string $str String to convert
		 *
		 * @return string
		 */
		public static function toKebapCase(string $str): string {
			return self::delimit($str, '-');
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
			return self::delimit($str, '_');
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
			$str = lcfirst(trim($str));
			$str = preg_replace('/^[-_]+/', '', $str);
			
			$str = preg_replace_callback(
				'/[-_\s]+(.)?/u',
				function ($match) {
					if (isset($match[1])) {
						return mb_strtoupper($match[1]);
					}
					
					return '';
				},
				$str
			);
			
			$str = preg_replace_callback(
				'/[\d]+(.)?/u',
				function ($match) {
					return mb_strtoupper($match[0]);
				},
				$str
			);
			
			return $str;
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
			return mb_strtolower($str);
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
			return str_replace('ß', 'SS', mb_strtoupper($str));
		}
		
		/**
		 * Add an ellipsis (…) to the center of the string if it is too long.
		 *
		 * @param string $str    String to shorten
		 * @param int    $maxLen Maximum Length ($char included)
		 * @param string $char   Char to use (default: …)
		 *
		 * @return string         String with ellipsis in the center
		 */
		public static function ellipsisCenter(string $str, int $maxLen, string $char = '…'): string {
			if (mb_strlen($str) > ($maxLen + mb_strlen($char))) {
				$characters = floor($maxLen / 2);
				
				return mb_substr($str, 0, $characters) . $char . mb_substr($str, -1 * $characters);
			}

			return $str;
		}
		
		/**
		 * Add an ellipsis (…) to the end of the string if it is too long.
		 *
		 * @param string $str    String to shorten
		 * @param int    $maxLen Maximum Length ($char included)
		 * @param string $char   Char to use (default: …)
		 *
		 * @return string         String with ellipsis at the end
		 */
		public static function ellipsisEnd(string $str, int $maxLen, string $char = '…'): string {
			if (mb_strlen($str) > ($maxLen + mb_strlen($char))) {
				return mb_substr($str, 0, $maxLen) . $char;
			}

			return $str;
		}
		
		/**
		 * Generate a random string based on a character set.
		 *
		 * @param int    $length  Length of the random string
		 * @param string $charset Chars to be used for the random string
		 *
		 * @return string           Random String
		 */
		public static function randString(int $length = 12, string $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string {
			$str = '';

			for ($i = 0; $i < $length; $i++) {
				$str .= $charset[mt_rand(0, strlen($charset) - 1)];
			}

			return $str;
		}
		
		/**
		 * Remove whitespace from $var.
		 * This is useful for comparing values which can contain
		 * whitespaces.
		 *
		 * @param string|array|null $var Variable, either an array or a string
		 *
		 * @return string|array|null       Returns a cleaned array or string
		 */
		public static function removeWhitespace($var) {
			if ($var === null) {
				return null;
			}
			
			if (is_array($var)) {
				return array_map(function($val) {
					return self::removeWhitespace($val);
				}, $var);
			}
			
			return preg_replace('^|\s^', '', $var);
		}

		/**
		 * Returns the text in a way that humans and computers can read it.
		 * 
		 * @param string $text the text to slugify
		 * 
		 * @return string       the slugified text
		 */
		public static function slugify(string $text): string {
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
