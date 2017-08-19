<?php
namespace Adepto\Fancy;

	/**
	 * FancyFunctions
	 * Internal functions. Nothing to see here ;)
	 * 
	 * @author bluefirex, FeistyBall
	 * @version 3.3
	 * @package as.adepto.fancy
	 */
	abstract class FancyFunctions {

		/**
		 * Checks whether a class implements an interface or not.
		 * 
		 * @param string $class Class to check
		 * @param string $interface Interface to check
		 * 
		 * @return boolean Implements?
		 */
		public static function classImplements($class, $interface) {
			$ref = new \ReflectionClass($class);

			try {
				return $ref->implementsInterface($interface);
			} catch (\Exception $e) {
				return false;
			}
		}

		/**
		 * Convert a string to a hex number.
		 *
		 * @param  string $string String
		 *
		 * @return string         hex number
		 */
		public static function strToHex($string): string {
			$hex = '';

			for ($i = 0; $i < strlen($string); $i++){
				$ord = ord($string[$i]);
				$hexCode = dechex($ord);
				$hex .= substr('0' . $hexCode, -2);
			}

			return strToUpper($hex);
		}
		
		/**
		 * Convert a hex number to a string.
		 *
		 * @param  string $hex Hex Number
		 *
		 * @return string
		 */
		public static function hexToStr($hex): string {
			$string = '';

			for ($i = 0; $i < strlen($hex) - 1; $i += 2){
				$string .= chr(hexdec($hex[$i] . $hex[$i + 1]));
			}
			
			return $string;
		}

		/**
		 * Convert a file to PNG data.
		 * Returns the file as a data URI: data:image/png;base64,<data>
		 *
		 * @param  string $file File to Convert
		 *
		 * @return string
		 */
		public static function imageToPNGData($file): string {
			if (pathinfo($file, PATHINFO_EXTENSION) == 'svg') {
				return 'data:image/svg+xml;base64,' . base64_encode(file_get_contents($file));
			}

			$img = new \Imagick();

			$img->readImageBlob(file_get_contents($file));
			$img->setImageFormat("png24");

			return 'data:image/png;base64,' . base64_encode($img->getImageBlob());
		}

		/**
		 * Convert image blob data (in PNG format) to PNG data.
		 *
		 * @param  string $imageBlob Binary PNG image blob
		 *
		 * @return string
		 */
		public static function imageBlobToPNGData($imageBlob): string {
			return 'data:image/png;base64,' . base64_encode($imageBlob);
		}

		/**
		 * Convert image blob data (in PNG format) to JPEG data.
		 *
		 * @param  string $imageBlob Binary JPEG image blob
		 *
		 * @return string
		 */
		public static function imageBlobToJPEGData($imageBlob): string {
			return 'data:image/jpeg;base64,' . base64_encode($imageBlob);
		}

		/**
		 * Convert image blob data (in SVG format) to SVG data.
		 *
		 * @param  string $imageBlob Binary JPEG image blob
		 *
		 * @return string
		 */
		public static function imageBlobToSVGData($imageBlob): string {
			return 'data:image/svg+xml;base64,' . base64_encode($imageBlob);
		}

		/**
		 * Make all links in $str clickable.
		 *
		 * @param  String $str String with links inside it
		 *
		 * @return String
		 */
		public static function makeClickable($str): string {
			$str = preg_replace('#(^|\n|">| )((ht|f)tps?://?(www\.)?([a-zA-Z0-9äöüß&§_\-/\#?.%=:;+$@*]+))#i', '$1<a target="_blank" href="$2">$2</a>', $str);
			$str = preg_replace('#(^|\n|">| )((?<!//)www\.[a-zA-Z0-9äöüß§_\-/\#?.%=:;+&$@*]+)#i', '$1<a target="_blank" href="http://$2">$2</a>', $str);

			return $str;
		}

		/**
		 * Check if PHP is running in CLI.
		 *
		 * @return boolean
		 */
		public static function isCLI(): bool {
			return php_sapi_name() === 'cli';
		}

		/**
		 * Escape a single CSV value
		 *
		 * @param  string $field     value to escape
		 * @param  string $delimiter Delimiter to escape for
		 *
		 * @return string
		 */
		public static function escapeCSVField($field, string $delimiter = ';'): string {
			$field = html_entity_decode($field);
			$field = preg_replace("#(\r)?\n#", ' ', $field);

			if (strpos($field, $delimiter) !== false) {
				$field = '"' . $field . '"';
			}

			return $field;
		}

		/**
		 * Escape multiple CSV fields
		 *
		 * @param  array  $fields    Fields to escape
		 * @param  string $delimiter Delimiter to escape for
		 *
		 * @return array
		 */
		public static function escapeCSVFields(array $fields, string $delimiter = ';'): array {
			return array_map(function($field) use($delimiter) {
				return self::escapeCSVField($field, $delimiter);
			}, $fields);
		}

		/**
		 * Check if any of the passed parameters are empty.
		 *
		 * @return boolean
		 */
		public static function anyEmpty(...$vars): bool {
			foreach ($vars as $var) {
				if (empty($var)) return true;
			}

			return false;
		}

		/**
		 * Check if all of the empty parameters are empty.
		 *
		 * @return boolean
		 */
		public static function allEmpty(...$vars): bool {
			$results = [];

			foreach ($vars as $var) {
				$results[] = (int) empty($var);
			}

			return !in_array(0, $results);
		}

		/**
		 * Check if a number is between two values.
		 * Between means:
		 * 		5 is >= 3 and <= 9.
		 *
		 * @param  int    $nr    Number to check
		 * @param  int    $first Low number
		 * @param  int    $last  High number
		 *
		 * @return boolean
		 */
		public static function between($nr, $first, $last): bool {
			return $nr >= $first && $nr <= $last;
		}

		/**
		 * Curry a function from left.
		 * Currying means to bind some arguments to a function and return a new function
		 * with the passed arguments bound.
		 *
		 * i.e.: You have a function that takes two integers and adds them up. If you now want to
		 *       auto-add "2" to all items in an array of numbers, you can use curry to pre-bind
		 *       "2" to your function and then just pass it to array_map:
		 *
		 *       $addedTwo = array_map(curry('add2', 2), $arrayOfNumbers);
		 *
		 * @param  callable $fn   Original closure/callable to curry
		 * @param           $args Arguments to bind from left
		 *
		 * @return callable
		 */
		public static function curry(callable $fn, ...$args) {
			return function(...$additionalArgs) use($fn, $args) {
				return $fn(...$args, ...$additionalArgs);
			};
		}

		/**
		 * Curry a function from right.
		 * For documentation on how currying works and what it does, {@see FancyFunctions::curry}.
		 *
		 * @param  callable $fn   Original closure/callable to curry
		 * @param           $args Arguments to bind from right
		 *
		 * @return callable
		 */
		public static function curryRight(callable $fn, ...$args) {
			return function(...$additionalArgs) use($fn, $args) {
				return $fn(...$additionalArgs, ...$args);
			};
		}

		/**
		 * Create string suitable as a css class.
		 * 
		 * @param  string $str    String
		 * @param  string $prefix Prefix
		 * 
		 * @return string         String you can use as scc class
		 */
		public static function stringToCSSClass($str, $prefix = ''): string {
			return $prefix . str_replace([
				' ',
				'æ',
				'å',
				'ø',
				'ß',
				'.',
				':'
			], [
				'-',
				'ae',
				'a',
				'o',
				'ss',
				'',
				'-'
			], strtolower($str));
		}

		/**
		 * Check if a value is set by reference.
		 *
		 * @param  mixed &$varVal Value
		 *
		 * @return boolean
		 */
		public static function issetByReference(&$varVal): bool {
			return isset($varVal);
		}


		/**
		 * Check if a request is an ajax request.
		 *
		 * @param  array|null $s Request array or $_SERVER
		 *
		 * @return boolean
		 */
		public static function isAJAXrequest(array $s = null): bool {
			if (is_null($s)) {
				$s = $_SERVER;
			}

			return !empty($s['HTTP_X_REQUESTED_WITH']) && strtolower($s['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
		}

		/**
		 * Check if the object is instance of a class.
		 * 
		 * @param  mixed  $object The object
		 * @param  string $class  The class
		 *
		 * @throws \Exception If the object is not instance of the class
		 */
		public static function assertType($object, string $class) {
			if (!($object instanceof $class)) {
				throw new \Exception('Expected class ' . $class . ', found ' . get_class($object));
			}
		}
	}