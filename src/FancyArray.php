<?php
	namespace Adepto\Fancy;

	use Exception;
	use InvalidArgumentException;
	use OutOfBoundsException;
	use stdClass;
	use TypeError;
	use UnexpectedValueException;
	use XMLWriter;
	
	/**
	 * FancyArray
	 *
	 * @author bluefirex, FeistyBall
	 * @version 2.0
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
		 * Nested keys are joined by $glue in the flattened array
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
		 * Nested keys are joined by $glue in the flattened array
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
		 * Check whether a given array is sequential.
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
		public static function arrayToObject(array $arr): stdClass {
			$obj = new stdClass();

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
		 * @param stdClass  $obj Object to convert
		 *
		 * @return array
		 */
		public static function objectToArray(stdClass $obj): array {
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
		 * @param array $arrays
		 *
		 * @return array if less than two arrays were passed
		 */
		public static function difference(...$arrays): array {
			if (count($arrays) < 2) {
				throw new InvalidArgumentException('At least two arrays required.');
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
		public static function dbEncode(array $toEncode): string {
			return base64_encode(json_encode($toEncode));
		}

		/**
		 * Decodes longer array values stored in the DB
		 *
		 * @param $toDecode string The encoded value from the Database
		 *
		 * @return array The decoded PHP array
		 */
		public static function dbDecode(string $toDecode): array {
			return json_decode(base64_decode($toDecode));
		}

		/**
		 * Find the highest (recursive) count in an array.
		 *
		 * @param array $arr       Array to count
		 * @param bool  $countSelf Whether to count the base arrays' size as well
		 *
		 * @return int
		 */
		public static function findHighestCount(array $arr, bool $countSelf = false): int {
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
		 * I know: PHP copies arrays itself, but: SOAP is special, as always.
		 *
		 * @param array $source Source Array
		 *
		 * @return array
		 */
		public static function clone(array $source): array {
			$result = [];

			foreach ($source as $key => $item) {
				$result[$key] = is_array($item) ? self::clone($item) : $item;
			}

			return $result;
		}

		/**
		 * Move an element in an array.
		 *
		 * @param array      $arr Array
		 * @param int|string $old Old Position
		 * @param int        $new New Position
		 *
		 * @return array
		 */
		public static function moveElement(array $arr, $old, int $new): array {
			if (is_int($old)) {
				$tmp = array_splice($arr, $old, 1);
				array_splice($arr, $new, 0, $tmp);
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
				
				return $output;
			}

			return $arr;
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
		 * @param  bool = false $map If true, results are defined by the callback. If false, original values are being used.
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
		 * Check if all the given elements are in the array.
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
		 * @param array  $array The array
		 * @param string $class The class
		 *
		 * @throws Exception
		 */
		public static function assertType(array $array, string $class) {
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
		 * Return all the key-value pairs that are in the first and in the second array.
		 *
		 *
		 * @param array $first
		 * @param array $second
		 *
		 * @return array
		 */
		public static function intersectKeysRecursive(array $first, array $second): array {
			foreach ($first as $key => $value) {
				if (!isset($second[$key])) {
					unset($first[$key]);
				} else if (is_array($value)) {
					$first[$key] = self::intersectKeysRecursive($value, (array) $second[$key]);
				}
			}
			
			return $first;
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
			$accessors = array_filter(explode($delimiter, $index), function ($access) {
				return strlen($access) > 0;
			});

			if (count($accessors) == 1) {
				$accessor = (string) $accessors[0];
				return $arr[$accessor];
			} else if (count($accessors) > 1) {
				$accessor = array_shift($accessors);
				$remaining = implode($delimiter, $accessors);

				return self::colonAccess($arr[$accessor] ?? [], $remaining, $delimiter);
			} else {
				return null;
			}
		}

		/**
		 * Same idea as {@see colonAccess}, but writes instead of reading.
		 *
		 * @param array  $arr       The array to write to
		 * @param string $key       The (joined) key under which to set the new element
		 * @param $value mixed      The value to set
		 * @param string $delimiter The delimiter for splitting multi-dimensional keys. Defaults to ':'
		 *
		 * @return array
		 */
		public static function colonInject(array $arr, string $key, $value, string $delimiter = ':'): array {
			$accessors = array_filter(explode($delimiter, $key), function ($val) {
				return strlen($val) > 0;
			});

			if (count($accessors) == 1) {
				$accessor = (string) $accessors[0];
				$arr[$accessor] = $value;
			} else if (count($accessors) > 1) {
				$accessor = array_shift($accessors);
				$remaining = implode($delimiter, $accessors);

				$arr[$accessor] = self::colonInject($arr[$accessor] ?? [], $remaining, $value, $delimiter);
			}

			return $arr;
		}

		/**
		 * L-inear A-rray D-ownward A-ccess
		 * Takes a linear input array, i.e. a sequential list that has one-dimensional associative arrays as elements,
		 * and extracts all given keys from every one of these elements.
		 * The groups together a new result nested by the relevant key's values in each element
		 *
		 * @param array $arr  The linear array to regroup
		 * @param array $keys The keys to regroup after
		 *
		 * @return array
		 * @noinspection SpellCheckingInspection
		 */
		public static function ladaGroup(array $arr, array $keys): array {
			$delimiter = '%%LADA%%';
			$lada = [];

			foreach ($arr as $item) {
				$key = implode($delimiter, array_map(function (string $key) use ($item) {
					return $item[$key];
				}, $keys));

				foreach ($keys as $subKey) {
					unset($item[$subKey]);
				}

				$lada = self::colonInject($lada, $key, $item, $delimiter);
			}

			return $lada;
		}

		/**
		 * See Python
		 *
		 * @param  array[]  ...$arr  The arrays to zip
		 * @return array The zipped array
		 */
		public static function zip(array ...$arr): array {
			return array_map([static::class, 'splat'], ...$arr);
		}

		/**
		 * Pack the supplied variadic arguments into one single array
		 *
		 * @param  array  ...$args  Different arguments to pack
		 *
		 * @return array The packed array
		 */
		public static function splat(...$args): array {
			return $args;
		}

		/**
		 * Count how deeply nested an array is
		 *
		 * @param  array  $arr
		 *
		 * @return  int
		 */
		public static function depth(array $arr): int {
			// use ternary question because max() complains about empty arrays (duh…)
			return 1 + ($arr ? max(array_map(function ($val) {
				return is_array($val) ? self::depth($val) : 0;
			}, $arr)) : 0);
		}

		/**
		 * Count all elements within an array, including potential sub-arrays
		 *
		 * @param  array  $arr  The array to count
		 *
		 * @return int
		 */
		public static function deepCount(array $arr): int {
			return array_sum(array_map(function ($val) {
				return is_array($val) ? self::deepCount($val) : 1;
			}, $arr));
		}

		/**
		 * Count the flattened array.
		 * This is just a convenience alias for count(flatten($arr))
		 *
		 * @param  array  $arr  The array to count
		 *
		 * @return  int
		 */
		public static function flatCount(array $arr): int {
			return count(self::flattenAssoc($arr));
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
			$fp = fopen('php://temp', 'w+');
			
			if ($fp === false) {
				throw new \RuntimeException('Could not open memory stream.');
			}
			
			foreach ($arr as $fields) {
				if (fputcsv($fp, $fields, $delimiter) === false) {
					throw new InvalidArgumentException('Could not convert fields to CSV: ' . implode($delimiter, $fields));
				}
			}
			
			rewind($fp);
			$csvString = stream_get_contents($fp);
			fclose($fp);
			
			return $csvString;
		}
		
		/**
		 * Convert an array to an XML string.
		 * Namespaced tags are supported by simply including it in the key name, e.g. "xsd:schema". Make sure
		 * to wrap your array in a root element and declare your namespaces.
		 *
		 * This function requires XMLWriter (ext-xmlwriter).
		 *
		 * This is how the conversion works:
		 *
		 * - key/value pairs:
		 *     <key>Value</key>
		 *
		 * - sequential array as a child:
		 *     <child>
		 *         <key>Value</key>
		 *     </child>
		 *     <child>
		 *         <key>Value</key>
		 *     </child>
		 *
		 * Example:
		 *     [
		 *         'key'    =>  'value',
		 *         'child'  =>  [
		 *              [ 'a' => 1 ],
		 *              [ 'a' => 2 ]
		 *          ]
		 *     ]
		 *
		 * becomes:
		 *     <?xml version="1.0" charset="utf-8" ?>
		 *     <key>Value</key>
		 *     <child>
		 *         <a>1</a>
		 *     </child>
		 *     <child>
		 *         <a>2</a>
		 *     </child>
		 *
		 * @param array $array Source Array
		 * @param array $namespaces List of namespaces to declare on the root element, key is the prefix, value the URL the namespace uses.
		 *                          An empty key represents the default namespace (xmlns="…").
		 *
		 * @throws InvalidArgumentException If namespaces are declared but there is more than one root element.
		 *
		 * @return string
		 */
		public static function toXML(array $array, array $namespaces = []): string {
			$writer = new XMLWriter();
			
			$writer->openMemory();
			$writer->setIndent(true);
			$writer->startDocument('1.0', 'utf-8');
			
			self::appendToXML($array, $writer, null, $namespaces);
			
			$writer->endDocument();
			
			return $writer->outputMemory();
		}
		
		protected static function appendToXML($thing, XMLWriter $writer, $parentKey = null, array $namespaces = []) {
			if (!is_array($thing)) {
				$writer->writeElement($parentKey, $thing);
				return;
			}
			
			if (self::isSequential($thing)) {
				foreach ($thing as $subArray) {
					$writer->startElement($parentKey);
					self::appendToXML($subArray, $writer, $parentKey, $namespaces);
					$writer->endElement();
				}
				
				return;
			}
			
			if ($parentKey === null && count($thing) > 1 && count($namespaces)) {
				throw new InvalidArgumentException('Source array must have exactly one root element when using namespaces.');
			}
			
			foreach ($thing as $key => $item) {
				if (is_array($item)) {
					if (self::isSequential($item)) {
						self::appendToXML($item, $writer, $key, $namespaces);
					} else {
						$writer->startElement($key);
						
						if ($parentKey === null) {
							foreach ($namespaces as $prefix => $url) {
								$writer->writeAttribute(empty($prefix) ? 'xmlns' : 'xmlns:' . $prefix, $url);
							}
						}
						
						self::appendToXML($item, $writer, $key, $namespaces);
						$writer->endElement();
					}
				} else {
					self::appendToXML($item, $writer, $key, $namespaces);
				}
			}
		}

		/**
		 * Flip key and value in an array with a default value.
		 * 
		 * @param array  $arr     Array you want to flip
		 * @param int    $default Default value
		 * 
		 * @return array
		 */
		public static function flipSequential(array $arr, int $default = 0): array {
			return array_map(function() use($default) {
				return $default;
			}, array_flip($arr));
		}
		
		/**
		 * Map an array recursively, meaning visiting only the leafs
		 *
		 * @param callable $callback     Callback to apply. Return the new value here.
		 * @param array    $array        Array to map
		 * @param bool     $preserveKeys Whether to preserve keys, defaults to true
		 *
		 * @return array
		 */
		public static function mapRecursive(callable $callback, array $array, bool $preserveKeys = true): array {
			$func = function ($item) use (&$func, &$callback) {
				return is_array($item) ? array_map($func, $item) : call_user_func($callback, $item);
			};
			
			$mapped = array_map($func, $array);
			
			if ($preserveKeys) {
				return array_combine(array_keys($array), $mapped);
			}
			
			return $mapped;
		}

		/**
		 * Check if all elements of an array fulfill a given predicate closure
		 *
		 * @param array $arr
		 * @param callable $fn
		 *
		 * @return bool
		 */
		public static function all(array $arr, callable $fn): bool {
			foreach ($arr as $item) {
				if (!$fn($item)) {
					return false;
				}
			}

			return true;
		}

		/**
		 * Check if any element in the array happens to fulfill a given predicate closure
		 *
		 * @param array $arr
		 * @param callable $fn
		 *
		 * @return bool
		 */
		public static function any(array $arr, callable $fn): bool {
			foreach ($arr as $item) {
				if ($fn($item)) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Partition any array into a sublist of entries that fulfill the predicate, and a sublist of entries that don't
		 *
		 * @param  array     $arr  The array to partition
		 * @param  callable  $fn   The predicate function to partition with
		 *
		 * @return array The partitioned array, where the first sub-array yields all "true" results, and the second array all "false" results
		 */
		public static function partition(array $arr, callable $fn): array {
			return [
				array_filter($arr, $fn),
				array_filter($arr, FancyClosure::negate($fn))
			];
		}

		/**
		 * Trim array to the first occurrence of an element php would evaluate to false.
		 *
		 * @param array $arr
		 *
		 * @return array
		 */
		public static function trim(array $arr): array {
			$rev = array_values(array_reverse($arr));
			$length = count($arr);
			
			for ($i = 0; $i < count($rev); $i++) {
				if (!!$rev[$i]) {
					if ($i === 0) {
						return $arr;
					}
					
					$length = -$i;
					break;
				}
			}
			
			return array_slice($arr, 0, $length);
		}
		
		/**
		 * Check if an array matches specific strings or other values, supports wildcards.
		 * e.g.: your array is [ 'hello' => 'there' ] and your specification requires [ 'hello' => 'th*' ], it would match
		 * Supports nesting of up to 255 arrays (PHP limitation).
		 *
		 * @param array $arr        Array to check
		 * @param array $shouldHave Specification, see description
		 * @param bool  $throw      if true, exceptions are thrown
		 *
		 * @return bool
		 * @throws TypeError                if value types don't match and $throw is true
		 * @throws UnexpectedValueException if value has same type but is still not the same and $throw is true
		 * @throws OutOfBoundsException     if key could not be found and $throw is true
		 */
		public static function matches(array $arr, array $shouldHave, bool $throw = true): bool {
			$matches = true;

			try {
				foreach ($shouldHave as $key => $value) {
					if (!array_key_exists($key, $arr)) {
						throw new OutOfBoundsException('Key missing: ' . $key);
					} else if (is_array($value) && is_array($arr[$key])) { # technically "else if" is not necessary, but we want to be consistent here
						$matches &= self::matches($arr[$key], $value, $throw);
					} else if (is_array($value) || is_array($arr[$key])) {
						throw new TypeError('Type mismatch at key "' . $key . '"');
					} else if (!fnmatch($value, $arr[$key])) {
						throw new UnexpectedValueException('Value mismatch at key "' . $key . '"');
					}
				}
			} catch (Exception $e) {
				if ($throw) {
					throw $e;
				}

				return false;
			}

			return $matches;
		}
	}
