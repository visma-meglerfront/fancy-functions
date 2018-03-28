<?php
	namespace Adepto\Fancy;

	/**
	 * Class FancyClosure
	 * Implements basic features and idioms from functional languages into PHP
	 *
	 * @package as.adepto.fancy
	 * @author suushie_maniac
	 * @version 1.0
	 */
	abstract class FancyClosure {
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
		 * @param  callable  $fn    Original closure/callable to curry
		 * @param  mixed     $args  Arguments to bind from left
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
		 * @param  callable $fn    Original closure/callable to curry
		 * @param  mixed    $args  Arguments to bind from right
		 *
		 * @return callable
		 */
		public static function curryRight(callable $fn, ...$args) {
			return function(...$additionalArgs) use($fn, $args) {
				return $fn(...$additionalArgs, ...array_reverse($args));
			};
		}

		/**
		 * A constant closure that always returns a given value
		 * Really. No matter what happens. Even if the world ends.
		 *
		 * @param mixed $val The value to ALWAYS return
		 *
		 * @return \Closure A function that always returns this value
		 */
		public static function always($val) {
			return function () use ($val) {
				return $val;
			};
		}

		/**
		 * A closure that compares every parameter passed to it to the value $cmp
		 *
		 * @param mixed  $cmp     The value to compare to
		 * @param bool   $strict  Whether to use strict comparison (=== instead of ==)
		 *
		 * @return \Closure A function that compares its argument to $cmp
		 */
		public static function equal($cmp, bool $strict = false) {
			return function ($val) use ($cmp, $strict) {
				return $strict ? $val === $cmp : $val == $cmp;
			};
		}

		/**
		 * A closure that returns the opposite of what its (predicate) parameter callable would return
		 *
		 * @param  callable  $fn  The callable to negate
		 *
		 * @return \Closure The negated function
		 */
		public static function negate(callable $fn) {
			return function (...$args) use ($fn) {
				return !$fn(...$args);
			};
		}

		/**
		 * A function that always returns what it receives
		 * without even thinking about modifying it
		 *
		 * @return \Closure The identity function
		 */
		public static function identity() {
			return function ($arg) {
				return $arg;
			};
		}

		/**
		 * Compose several functions into one.
		 * ATTENTION! The last function gets called first
		 *
		 * compose(f1, f2, ..., fn) yields f1(f2(...(fn(args))))
		 *
		 * @param  callable[]  ...$fns  The functions to compose
		 *
		 * @return callable The composed function
		 */
		public static function compose(...$fns) {
			return array_reduce(array_reverse($fns), function ($carry, $item) {
				return function (...$args) use ($carry, $item) {
					return $item($carry(...$args));
				};
			}, self::identity());
		}

		/**
		 * A closure accessing an array at a certain index, similar to {@see array_column}
		 *
		 * @param  string|int  $index  The index at which to access the array
		 *
		 * @return \Closure The closure that accesses any given array at $index
		 */
		public static function arrayAccess($index) {
			return function ($val) use ($index) {
				return $val[$index];
			};
		}

		/**
		 * Similar to {@see self::arrayAccess} but works with objects instead
		 *
		 * @param  string  $index  The access index
		 *
		 * @return \Closure The closure that accesses any given object at $index
		 */
		public static function objectAccess($index) {
			return function ($val) use ($index) {
				return $val->{$index};
			};
		}

		/**
		 * Create a closure that invokes a certain method on every argument passed to it
		 *
		 * @param  string  $methodName  The name of the method to call
		 * @param  array   ...$params   Optional parameters to pass to each method call
		 *
		 * @return \Closure The closure to invoke $methodName
		 */
		public static function invoke(string $methodName, ...$params) {
			return function ($val) use ($methodName, $params) {
				return call_user_func([$val, $methodName], ...$params);
			};
		}

		/**
		 * Create a closure that invokes $fn with all arguments passed to the closure as arguments to $fn
		 *
		 * @param  callable  $fn  The callable to invoke each time
		 *
		 * @return \Closure The invoking closure
		 */
		public static function invoking(callable $fn) {
			return function (...$args) use ($fn) {
				return call_user_func($fn, ...$args);
			};
		}

		/**
		 * Variadic addition closure
		 *
		 * @return \Closure
		 */
		public static function fnAdd() {
			return function (...$args) {
				return array_reduce($args, function ($carry, $item) {
					return $carry + $item;
				}, 0);
			};
		}

		/**
		 * Variadic subtraction closure
		 *
		 * @return \Closure
		 */
		public static function fnSubtract() {
			return function (...$args) {
				return array_reduce($args, function ($carry, $item) {
					return $carry - $item;
				}, $args[0] * 2);
			};
		}

		/**
		 * Variadic multiplication closure
		 *
		 * @return \Closure
		 */
		public static function fnMultiply() {
			return function (...$args) {
				return array_reduce($args, function ($carry, $item) {
					return $carry * $item;
				}, 1);
			};
		}

		/**
		 * Variadic division closure
		 *
		 * @return \Closure
		 */
		public static function fnDivide() {
			return function (...$args) {
				return array_reduce($args, function ($carry, $item) {
					return $carry / $item;
				}, $args[0] ** 2);
			};
		}

		/**
		 * Returns a closure that unpacks every array passed to it into variadic arguments for $fn
		 *
		 * @param  callable  $fn  The callable for which to unpack arguments
		 *
		 * @return \Closure A prepared closure
		 */
		public static function callUnpacked(callable $fn) {
			return function (array $arr) use ($fn) {
				return $fn(...$arr);
			};
		}

		/**
		 * Returns a closure that packs variadic args passed to it into an array passed to $fn
		 *
		 * @param  callable  $fn  The callable for which to pack arguments
		 *
		 * @return \Closure A prepared closure
		 */
		public static function callPacked(callable $fn) {
			return function (...$args) use ($fn) {
				return $fn($args);
			};
		}

		/**
		 * Closure for PHP super-global "empty" function
		 *
		 * @return \Closure
		 */
		public static function fnEmpty() {
			return function ($val) {
				return empty($val);
			};
		}

		/**
		 * Closure for PHP super-global "isset" function
		 *
		 * @return \Closure
		 */
		public static function fnIsset() {
			return function ($val) {
				return isset($val);
			};
		}

		/**
		 * Closure for PHP super-global "echo" function
		 *
		 * @return \Closure
		 */
		public static function fnEcho() {
			return function ($val) {
				echo $val;
			};
		}

		/**
		 * Like {@see self::fnEcho} but additionally prints a new line at the end
		 *
		 * @return \Closure
		 */
		public static function fnEchoEOL() {
			return function ($val) {
				echo $val . PHP_EOL;
			};
		}

		/**
		 * Closure for casting variables to other types
		 *
		 * @param  string  $toType  The type to cast to, according to settype
		 *
		 * @return \Closure
		 */
		public static function casting(string $toType) {
			return function ($var) use ($toType) {
				settype($var, $toType);
				return $var;
			};
		}
	}