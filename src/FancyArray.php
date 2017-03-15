<?php
	namespace Adepto\Fancy;

	/**
	 * FancyArray
	 *
	 * @author bluefirex, FeistyBall
	 * @version 1.0
	 * @package as.adepto.fancy
	 */
	abstract class FancyArray {
		/**
		 * Check if an array has any empty values.
		 * Empty meaning:
		 * 	- empty($value) would return true for any non-numeric values
		 *
		 * @param  array $arr Array
		 *
		 * @return boolean
		 */
		public static function hasEmptyValues(array $arr): bool {
			if (count($arr) < 1) return true;

			foreach ($arr as $a) {
				if (is_array($a)) {
					$chk = self::hasEmptyValues($a);
				} else if (is_numeric($a)) {
					$chk = false;
				} else {
					$chk = empty($a);
				}

				if ($chk) return true;
			}

			return false;
		}

		/**
		 * Flatten an array from a tree-like structure.
		 * Does NOT retain keys!
		 *
		 * @param  array  $array Array to flatten
		 *
		 * @return array
		 */
		public static function flatten(array $array): array {
			$ret = [];

			array_walk_recursive($array, function($a) use (&$ret) {
				$ret[] = $a;
			});

			return $ret;
		}

		/**
		 * Flatten an array from a tree-like structure.
		 * As opposed to {@see flatten} this DOES retain keys, however.
		 * Nested keys are joined by {@param glue} in the flattened array
		 *
		 * @param  array  $arr             The array to flatten
		 * @param  string $glue            The glue to join nested keys. '_' by default.
		 *
		 * @return  array  The flattened array with (optionally joined) nested keys
		 */
		public static function flattenAssoc(array $arr, string $glue = '_'): array {
			$flattened = [];

			foreach ($arr as $key => $value) {
				if (is_array($value)) {
					$flattenedInner = self::flattenAssoc($value, $glue);

					foreach ($flattenedInner as $flattenedKey => $flattenedValue) {
						$flattened[$key . $glue . $flattenedKey] = $flattenedValue;
					}
				} else {
					$flattened[$key] = $value;
				}
			}

			return $flattened;
		}

		/**
		 * Flatten an array from a tree-like structure.
		 * This copies values from the origin array into a list of sequential flat values
		 * Nested keys are joined by {@param glue} in the flattened array
		 *
		 * @param  array  $arr             The array to flatten
		 * @param  string $glue            The glue to join nested keys. '_' by default.
		 *
		 * @return  array  The flattened array with (optionally joined) nested key values
		 */
		public static function flattenValues(array $arr, string $glue = '_'): array {
			$flattened = [];

			foreach ($arr as $key => $value) {
				if (is_array($value)) {
					$flattenedInner = self::flattenValues($value, $glue);

					foreach ($flattenedInner as $flattenedValue) {
						$flattened[] = $key . $glue . $flattenedValue;
					}
				} else {
					$flattened[] = $value;
				}
			}

			return $flattened;
		}

		/**
		 * Check whether a given array is sequential
		 * Sequential arrays are specified by having numeric keys in ascending order
		 * They are represented by [] in JSON
		 *
		 * @param  array  $arr  The array to check
		 * @return  bool  Whether the array is sequential or not
		 */
		public static function isSequential(array $arr): bool {
			return $arr == array_values($arr);
		}

		/**
		 * Check whether a given array is associative
		 * Sequential arrays are specified by having generic/arbitrary (string) keys in unspecified order
		 * They are represented by {} in JSON
		 *
		 * @param  array  $arr  The array to check
		 * @return  bool  Whether the array is associative or not
		 */
		public static function isAssociative(array $arr): bool {
			return !self::isSequential($arr);
		}

		/**
		 * Convert an array to an object. This deep-copies everything
		 * from the array to the object.
		 * Note: The object is a reference. If you return this from a method
		 * any other function can modify it!
		 *
		 * @param  array  $arr Array to convert
		 * @return stdClass
		 */
		public static function arrayToObject(array $arr): \stdClass {
			$obj = new \stdClass();

			foreach ($arr as $key => $val) {
				if (is_array($val)) {
					$obj->$key = self::arrayToObject($val);
				} else {
					$obj->$key = $val;
				}
			}

			return $obj;
		}

		/**
		 * Convert an object to an array. This deep-copies everything
		 * from the object to the array.
		 *
		 * @param  stdClass  $obj Object to convert
		 * @return array
		 */
		public static function objectToArray(\stdClass $obj): array {
			$arr = [];

			foreach ($obj as $key => $val) {
				if (is_object($val)) {
					$arr[$key] = self::objectToArray($val);
				} else {
					$arr[$key] = $val;
				}
			}

			return $arr;
		}

		/**
		 * Get the difference between a base arrays and many other arrays.
		 * The difference comes back in an array like so:
		 * [
		 *     'add'     => [ elements to add ],
		 *     'remove'  => [ elements to remove ],
		 *     'count'   => [ number of changes ]
		 * ]
		 *
		 * Example:
		 *     arrayDifference([1, 2], [2, 3], [3, 9])
		 *     -> add: 3, 9 (because those aren't in the first array)
		 *     -> remove: 1 (because that elements doesn't appear anywhere else)
		 *     -> #changes: 3
		 *
		 * @throws InvalidArgumentException if less than two arrays were passed
		 *
		 * @return array
		 */
		public static function difference(...$arrays): array {
			if (count($arrays) < 2) {
				throw new \InvalidArgumentException('At least two arrays required.');
			}

			$baseArray = array_shift($arrays);
			$x = array_merge(...$arrays);
			$toAdd = array_diff($x, $baseArray);
			$toRemove = array_diff($baseArray, $x);

			return [
				'add'		=>	array_values($toAdd),
				'remove'	=>	array_values($toRemove),
				'count'		=>	count($toAdd) + count($toRemove)
			];
		}

		/**
		 * Encodes longer arrays for storing in DB
		 *
		 * @param array $toEncode The array values to encode
		 * @return string The base64 encoded JSON
		 */
		public static function dbEncode(array $toEncode) {
			return base64_encode(json_encode($toEncode));
		}

		/**
		 * Deocdes longer array values stored in the DB
		 *
		 * @param $toDecode string The encoded value from the Database
		 * @return array The decoded PHP array
		 */
		public static function dbDecode($toDecode) {
			return json_decode(base64_decode($toDecode));
		}

		/**
		 * Find the highest (recursive) count in an array.
		 *
		 * @param  array   $arr       Array to count
		 * @param  boolean $countSelf Whether to count the base arrays' size as well
		 *
		 * @return int
		 */
		public static function findHighestCount(array $arr, $countSelf = false) {
			if (count($arr) < 1) return 0;

			$highestCount = $countSelf ? count($arr) : 0;

			foreach ($arr as $a) {
				if (is_array($a)) {
					$count = self::findHighestCount($a, true);

					if ($count > $highestCount) {
						$highestCount = $count;
					}
				}
			}

			return $highestCount;
		}

		/**
		 * Copy an array for use with stupid SOAP servers.
		 * I know: PHP copys arrays itself, but: SOAP is special, as always.
		 *
		 * @param  array $source Source Array
		 *
		 * @return array
		 */
		public static function clone($source): array {
			$result = [];

			foreach ($source as $key => $item) {
				$result[$key] = is_array($item) ? self::clone($item) : $item;
			}

			return $result;
		}

		/**
		 * Move an element in an array.
		 *
		 * @param  array $arr Array
		 * @param  int   $old Old Position
		 * @param  int   $new New Position
		 *
		 * @return array
		 */
		public static function moveElement(array $arr, $old, $new): array {
			if (is_int($old)) {
				$tmp = array_splice($arr, $old, 1);

				array_splice($arr, $new, 0, $tmp);

				$output = $arr;
			} else if (is_string($old)) {
				$indexToMove = array_search($old, array_keys($arr));
				$itemToMove = $arr[$old];

				array_splice($arr, $indexToMove, 1);

				$i = 0;
				$output = [];

				foreach ($arr as $key => $item) {
					if ($i == $new) {
						$output[$old] = $itemToMove;
					}

					$output[$key] = $item;
					$i++;
				}
			}

			return $output;
		}

		/**
		 * Replace an element in an array.
		 * Do not pass the indexes!
		 *
		 * @param  array  $arr        Array
		 * @param  mixed  $oldElement Old Element
		 * @param  mixed  $newElement New Element
		 *
		 * @return array
		 */
		public static function replaceElement(array $arr, $oldElement, $newElement): array {
			$index = array_search($oldElement, $arr);
			$arr[$index] = $newElement;

			return $arr;
		}

		/**
		 * Replace multiple elements in an array at once.
		 * Do not pass the indexes!
		 *
		 * @param  array  $arr         Array
		 * @param  array  $oldElements Old Elements
		 * @param  array  $newElements New Elements
		 *
		 * @return array
		 */
		public static function replaceElements(array $arr, array $oldElements, array $newElements): array {
			for ($i = 0; $i < count($oldElements); $i++) {
				$arr = self::replaceElement($arr, $oldElements[$i], $newElements[$i]);
			}

			return $arr;
		}

		/**
		 * Append one or more elements to an array at $position.
		 * Beware: If $elements is an object, the properties are used (because of type casting).
		 * To mitigate this, make $elements an array with only one object one it.
		 *
		 * @param  array        $arr       Source Array
		 * @param  array|mixed  $elements  Elements to add
		 * @param  integer      $position  Position to add in. Special Values: -1 is the last position, 0 the first.
		 *
		 * @return array
		 */
		public static function appendElement(array $arr, $elements, int $position = -1): array {
			if ($position === -1) {
				$position = count($arr);
			}

			array_splice($arr, $position, 0, $elements);

			return $arr;
		}

		/**
		 * Remove duplicates from an array by asking the callback for a string-representation of
		 * the current element before comparison.
		 *
		 * @param  array                $arr Array to remove duplicates from
		 * @param  callable             $cb  Callback receives one argument: the current value being investigated
		 * @param  bool|boolean = false $map If true, results are defined by the callback. If false, original values are being used.
		 *
		 * @return array
		 */
		public static function uniqueCallback(array $arr, callable $cb, bool $map = false): array {
			$result = [];
					
			foreach ($arr as $a) {
				$cbReturn = $cb($a);
				$result[$cbReturn] = $map ? $cbReturn : $a;
			}
			
			return $result;
		}

		/**
		 * Check if all of the given elements are in the array.
		 * 
		 * @param  array   $haystack The array
		 * @param  array   $needles  The values that should be in the array
		 * 
		 * @return boolean
		 */
		public static function hasAll(array $haystack, array $needles): bool {
			foreach ($needles as $needle) {
				if (!in_array($needle, $haystack)) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Check if any of the given elements are in the array.
		 * 
		 * @param  array   $haystack The array
		 * @param  array   $needles  The elements that might be in the array.
		 * 
		 * @return boolean
		 */
		public static function hasAny(array $haystack, array $needles): bool {
			foreach ($needles as $needle) {
				if (in_array($needle, $haystack)) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Check that all elements of the array are of a certain class.
		 * 
		 * @param  array  $array The array
		 * @param  string $class The class
		 * 
		 * @return boolean
		 */
		public static function assertType(array $array, string $class): bool {
			foreach ($array as $object) {
				FancyFunctions::assertType($object, $class);
			}
		}

		/**
		 * Return all the key-value pairs that are in the first but not the second array.
		 * 
		 * @param  array  $first  First array
		 * @param  array  $second Second array
		 * 
		 * @return array
		 */
		public static function diffAssocRecursive(array $first, array $second): array {
			$diff = [];

			foreach ($first as $key => $value) {
				if (array_key_exists($key, $second)) {
					if (is_array($value)) {
						$recursiveDiff = self::diffAssocRecursive($value, (array) $second[$key]);
						if (count($recursiveDiff)) $diff[$key] = $recursiveDiff;
					} else if ($value != $second[$key]) {
						$diff[$key] = $value;
					}
				} else {
					$diff[$key] = $value;
				}
			}

			return $diff;
		}

		/**
		 * Reduce n-dimensional access to 1-dimensional access.
		 * 
		 * @param  array  $arr       the array
		 * @param  string $index     the index
		 * @param  string $delimiter delimiter
		 * 
		 * @return mixed
		 */
		public static function colonAccess(array $arr, string $index, string $delimiter = ':') {
			$accessors = array_filter(explode($delimiter, $index));

			if (count($accessors) == 1) {
				$accessor = (string) $accessors[0];
				return $arr[$accessor];
			} else if (count($accessors) > 1) {
				$accessor = array_shift($accessors);
				$remaining = implode(':', $accessors);

				return self::colonAccess($arr[$accessor], $remaining);
			} else {
				return null;
			}
		}

		/**
		 * Convert an array to a CSV string
		 * First array are the headings, subsequent arrays the contents
		 *
		 * @param  array  $arr       Array
		 * @param  string $delimiter Delimiter to use, defaults to ';' for use with stupid Excel
		 *
		 * @return string
		 */
		public static function toCSV(array $arr, string $delimiter = ';'): string {
			return implode("\n", array_map(function($fields) use($delimiter) {
				return implode($delimiter, FancyFunctions::escapeCSVFields($fields));
			}, $arr));
		}

		/**
		 * Flip key and value in an array with a default value.
		 * 
		 * @param  array   $arr     Array you want to flip 
		 * @param  integer $default Default value
		 * 
		 * @return array
		 */
		public static function flipSequential(array $arr, $default = 0): array {
			return array_map(function($elem) use($default) {
				return $default;
			}, array_flip($arr));
		}
	}
