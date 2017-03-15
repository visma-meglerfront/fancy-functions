<?php
	namespace Adepto\Fancy;
	
	/**
	 * FancyDateTime
	 * Extended version of the built-in DateTime class.
	 *
	 * @author bluefirex, suushie_maniac, FeistyBall
	 * @version 1.5
	 * @package as.adepto.fancy
	 */
	class FancyDateTime extends \DateTime {
		const FORMAT_MYSQL = 'Y-m-d H:i';

		const WEEKDAY_MONDAY = 1;
		const WEEKDAY_TUESDAY = 2;
		const WEEKDAY_WEDNESDAY = 3;
		const WEEKDAY_THURSDAY = 4;
		const WEEKDAY_FRIDAY = 5;
		const WEEKDAY_SATURDAY = 6;
		const WEEKDAY_SUNDAY = 0;
		
		/**
		 * Create FancyDateTime from a timestamp.
		 *
		 * @param  mixed $ts Timestamp
		 *
		 * @return FancyDateTime
		 */
		public static function fromTimestamp(int $ts) {
			return (new self())->setTimestamp($ts);
		}

		/**
		 * Create FancyDateTime from a MySQL string
		 *
		 * @param string $mySQL MySQL database stamp
		 *
		 * @return FancyDateTime
		 */
		public static function fromMySQL(string $mySQL) {
			return self::createFromFormat(self::FORMAT_MYSQL, $mySQL);
		}

		/**
		 * Shorthand for getting the start of current day
		 * Normalized to 00:00:00
		 *
		 * @return FancyDateTime
		 */
		public static function todayAtMidnight() {
			return (new self())->setTime(0, 0, 0);
		}

		/**
		 * Basically convenience method, but allows for very readable code
		 * No need to imply an empty constructor infers the current date and time
		 *
		 * @return FancyDateTime Current instant in history. Make it a special one! :D
		 */
		public static function now(): FancyDateTime {
			return new self();
		}

		/**
		 * Basically convenience method, but allows for very readable code
		 * Especially useful for checking if a given date is valid or just the "default 0"
		 * as well as accessing epoch values in MySQL
		 *
		 * @return FancyDateTime
		 */
		public static function epoch(): FancyDateTime {
			return self::fromTimestamp(0);
		}

		/**
		 * Copy the value of another DateTime
		 *
		 * @param DateTimeInterface $other The FancyDateTime to clone
		 * @return FancyDateTime The cloned object
		 */
		public static function fromDateTime(\DateTimeInterface $other) {
			return self::fromTimestamp($other->getTimestamp());
		}

		/**
		 * Create FancyDateTime from a specific format or a list of formats.
		 * If $format is an array all those formats will be tried out until a match
		 * is found. If still no match is found, InvalidDateFormatException is thrown.
		 *
		 * Parsing a single format does not raise an exception to comply with standard
		 * DateTime behaviour.
		 *
		 * @param string|array $format   Format(s) to try
		 * @param string       $time     The time thing to parse
		 * @param DateTimeZone $timezone A timezone
		 *
		 * @return bool|FancyDateTime
		 * @throws InvalidDateFormatException If $format is an array of formats but $time didn't match one
		 */
		public static function createFromFormat($format, $time, $timezone = null) {
			if (is_array($format)) {
				// reverse because private method tryFormats uses "pop" instead of "shift" for performance reasons
				return self::tryFormats(array_reverse($format), $time, $timezone);
			} else {
				$dt = \DateTime::createFromFormat($format, $time, $timezone);

				if ($dt instanceof \DateTime) {
					return self::fromTimestamp($dt->getTimestamp());
				}

				return $dt;
			}
		}

		/**
		 * Try creating FancyDateTime from a string usung diffrent formats.
		 * 
		 * @param  array  $formats  		The formats that will be tried
		 * @param  string $time     		The time that is used to create the FancyDateTiemobject
		 * @param  DateTimeZone $timezone 	A timezone
		 * 
		 * @return FancyDateTime
		 * @throws InvalidDateFormatException 	If no formats are given
		 * @throws Exception 					If no FancyDateTime can be created
		 */
		private static function tryFormats(array $formats, string $time, $timezone = null): FancyDateTime {
			if (!count($formats)) {
				throw new \InvalidDateFormatException();
			}

			$format = array_pop($formats);

			try {
				$dt = self::createFromFormat($format, $time, $timezone);

				if (!$dt) {
					throw new Exception();
				}

				return $dt;
			} catch (Exception $e) {
				return self::tryFormats($formats, $time, $timezone);
			}
		}

		/**
		 * Convert a timestamp to a given date format.
		 *
		 * @param  int    $ts     Timestamp
		 * @param  string $format Format, default = 'd.m.Y'
		 *
		 * @return string         Formatted date
		 */
		public static function timestampToDate(int $ts, $format = 'd.m.Y') {
			return self::fromTimestamp($ts)->format($format);
		}

		/**
		 * Check if this date is on a weekend.
		 *
		 * @return boolean
		 */
		public function isWeekend() {
			return $this->isWeekday(self::WEEKDAY_SATURDAY) || $this->isWeekday(self::WEEKDAY_SUNDAY);
		}
		
		/**
		 * Check if this date is a specific weekday.
		 * 0 = Sunday
		 * Refer to WEEKDAY_* constants in case of doubt
		 *
		 * @param  int  $day Day
		 *
		 * @return boolean
		 */
		public function isWeekday(int $day) {
			return $this->format('w') == $day;
		}
		
		// @Override
		public function diff($object = 'now', $absolute = null) {
			if (!$object instanceof \DateTime) {
				$object = new \DateTime();
			}
			
			return parent::diff($object, $absolute);
		}
		
		/**
		 * Round time to midnight.
		 *
		 * @return FancyDateTime
		 */
		public function roundToMidnight(): FancyDateTime {
			$unitDetails = [
				[
					'dtFormat'	=>	's',
					'unitSize'	=>	60,
					'name'		=>	'seconds'
				],
				[
					'dtFormat'	=>	'i',
					'unitSize'	=>	60,
					'name'		=>	'minutes'
				],
				[
					'dtFormat'	=>	'H',
					'unitSize'	=>	24,
					'name'		=>	'hours'
				]
			];

			foreach ($unitDetails as $unitDetail) {
				$dateVal = $this->format($unitDetail['dtFormat']);

				if ($dateVal != 0) {
					if ($dateVal < $unitDetail['unitSize'] / 2) {
						$this->modify('-' . $dateVal . ' ' . $unitDetail['name']);
					} else {
						$this->modify('+' . ($unitDetail['unitSize'] - $dateVal) . ' ' . $unitDetail['name']);
					}
				}
			}

			return $this;
		}

		/**
		 * Set time to the start of the minute.
		 * 
		 * @return FancyDateTime
		 */
		public function startOfMinute(): FancyDateTime {
			return $this->setTime(
				$this->format('H'),
				$this->format('i'),
				0
			);
		}

		/**
		 * Set time to the end of the minute.
		 * 
		 * @return FancyDateTime
		 */
		public function endOfMinute(): FancyDateTime {
			return $this->setTime(
				$this->format('H'),
				$this->format('i'),
				59
			);
		}

		/**
		 * Set time to the start of the hour.
		 * 
		 * @param  bool|boolean $cascade cascade option
		 * 
		 * @return FancyDateTime
		 */
		public function startOfHour(bool $cascade = false): FancyDateTime {
			return ($cascade ? $this->startOfMinute() : $this)->setTime(
				$this->format('H'),
				0,
				0
			);
		}

		/**
		 * Set time to the end of the hour.
		 * 
		 * @param  bool|boolean $cascade cascade option
		 * 
		 * @return FancyDateTime
		 */
		public function endOfHour(bool $cascade = false): FancyDateTime {
			return ($cascade ? $this->endOfMinute() : $this)->setTime(
				$this->format('H'),
				59,
				59
			);
		}

		/**
		 * Normalize this date to morning midnight
		 * Especially useful if only the date and not the time matters
		 *
		 * @return FancyDateTime
		 */
		public function startOfDay(bool $cascade = false): FancyDateTime {
			return ($cascade ? $this->startOfHour() : $this)->setTime(0, 0, 0);
		}

		/**
		 * Normalize this date to evening midnight
		 *
		 * @return FancyDateTime
		 */
		public function endOfDay(bool $cascade = false): FancyDateTime {
			return ($cascade ? $this->endOfHour() : $this)->setTime(23, 59, 59);
		}

		/**
		 * Set the date to the last day of the week.
		 * 
		 * @param  bool|boolean $cascade cascade option
		 * 
		 * @return FancyDateTime
		 */
		public function startOfWeek(bool $cascade = false): FancyDateTime {
			return ($cascade ? $this->startOfDay(true) : $this)->setISODate(
				$this->format('Y'),
				$this->format('W'),
				1
			);
		}

		/**
		 * Set the date to the last day of the week.
		 * 
		 * @param  bool|boolean $cascade cascade option
		 * 
		 * @return FancyDateTime
		 */
		public function endOfWeek(bool $cascade = false): FancyDateTime {
			return ($cascade ? $this->endOfDay(true) : $this)->setISODate(
				$this->format('Y'),
				(int) $this->format('W') + 1,
				0
			);
		}

		/**
		 * Set the date to the first day of the month.
		 * 
		 * @param  bool|boolean $cascade cascade option
		 * 
		 * @return FancyDateTime
		 */
		public function startOfMonth(bool $cascade = false): FancyDateTime {
			return ($cascade ? $this->startOfDay(true) : $this)->modify('first day of this month');
		}

		/**
		 * Set the date to the last day of the month.
		 * 
		 * @param  bool|boolean $cascade cascade option
		 * 
		 * @return FancyDateTime                
		 */
		public function endOfMonth(bool $cascade = false): FancyDateTime {
			return ($cascade ? $this->endOfDay(true) : $this)->modify('last day of this month');
		}

		/**
		 * Set date to the first day of the year.
		 * 
		 * @param  bool|boolean $cascade cascade option
		 * 
		 * @return FancyDateTime
		 */
		public function startOfYear(bool $cascade = false): FancyDateTime {
			return ($cascade ? $this->startOfMonth(true) : $this)->setDate(
				$this->format('Y'),
				1,
				1
			);
		}

		/**
		 * Set the date to the last day of the year.
		 * 
		 * @param  bool|boolean $cascade cascade option
		 * 
		 * @return FancyDateTime                
		 */
		public function endOfYear(bool $cascade = false): FancyDateTime {
			return ($cascade ? $this->endOfMonth(true) : $this)->setDate(
				$this->format('Y'),
				12,
				31
			);
		}

		/**
		 * Set the date to yesterday.
		 * 
		 * @return FancyDateTime
		 */
		public function yesterday(): FancyDateTime {
			return $this->modify('-1 day');
		}

		/**
		 * Set the date to tomorrow.
		 * 
		 * @return FancyDateTime
		 */
		public function tomorrow(): FancyDateTime {
			return $this->modify('+1 day');
		}

		/**
		 * Checks whether this date is divisible by a certain factor of minutes.
		 *
		 * @param  DateTime $end     End to check, i.e. to check whether this is true in the next 10 minutes pass a date in 10 minutes
		 * @param  int      $minutes Factor to check for
		 *
		 * @return boolean
		 */
		public function isDivisibleByMinutes(\DateTime $end, $minutes): bool {
			$midnight = self::todayAtMidnight();
			
			$interval = \DateInterval::createFromDateString('1 min');
			$times = new \DatePeriod($this, $interval, $end);
			
			foreach ($times as $time) {
				$thisMinutesSinceMidnight = $midnight->diff($time);
				$thisMinutesSinceMidnight = $thisMinutesSinceMidnight->h * 60 + $thisMinutesSinceMidnight->i;
				
				if ($thisMinutesSinceMidnight % $minutes == 0) {
					return true;
				}
			}
			
			return false;
		}

		/**
		 * Check if the given DateTimeobject has the same day.
		 * 
		 * @param  \DateTime $dt DateTimeobject
		 * 
		 * @return bolean
		 */
		public function equalsDay(\DateTime $dt): bool {
			return $dt->format('Y-m-d') == $this->format('Y-m-d');
		}

		/**
		 * Check if th given DateTimeobject equals this to the second.
		 * 
		 * @param  \DateTime $dt DateTimeobject
		 * 
		 * @return boolean        
		 */
		public function equalsSecond(\DateTime $dt): bool {
			return $dt->getTimestamp() == $this->getTimestamp();
		}
		
		/**
		 * Format this date for use MySQL.
		 *
		 * @return string
		 */
		public function toMySQL() {
			return $this->format(self::FORMAT_MYSQL);
		}
		
		public function __toString() {
			return $this->toMySQL();
		}

		// Static Stuff

		/**
		 * Get the first and last possible dates for a given timeframe
		 * based in a timestamp.
		 *
		 * @param  int $timestamp Timestamp
		 * @param  string $interval Timeframe: 'year', 'month', 'week' or 'day'
		 * @param  int $offset An optional numeric offset, relative to the given $interval unit
		 *
		 * @return array
		 */
		public static function getFirstAndLastPossibleDate($timestamp, $interval, $offset = 0) {
			$firstDate = new self();
			$firstDate->setTimestamp($timestamp);

			$sign = $offset < 0 ? '-' : '+';
			$modif = $sign . abs($offset) . ' ' . $interval;
			$firstDate->modify($modif);

			$lastDate = clone $firstDate;

			switch ($interval) {
				case 'year':
					$firstDate->modify('first day of January');
					$lastDate->modify('last day of December');
					break;

				case 'month':
					$firstDate->modify('first day of this month');
					$lastDate->modify('last day of this month');
					break;

				case 'week':
					$day = $firstDate->format('w');
					$day -= 1; // shift PHP week

					$firstDate->modify('-' . $day . ' days');
					$lastDate->modify('+'. (6 - $day) . ' days');
			}

			return [
				'first'	=>	$firstDate->setTime(0, 0, 0),
				'last'	=>	$lastDate->setTime(23, 59, 59)
			];
		}

		/**
		 * Convert a time difference in seconds to a human-readable string, i.e.:
		 * 		90 -> 1 minute, 30 seconds
		 * 		7200 -> 2 hours
		 *
		 * Maximum 3 time parts.
		 *
		 * @param  int    $diff Difference in Seconds
		 *
		 * @return string
		 */
		public static function timeDiffToString(int $diff): string {
			$now = new \DateTime();
			
			$then = new \DateTime();
			$then->add(new \DateInterval('PT' . $diff .'S'));
			
			$diff = $now->diff($then);
			$str = [];
			
			$props = [
				'y'	=>	'years',
				'm' =>	'months',
				'd' =>	'days',
				'h' =>	'hours',
				'i'	=>	'minutes',
				's' =>	'seconds'
			];
			
			foreach ($props as $prop => $human) {
				if ($diff->$prop > 0 && count($str) < 3) {
					$s = $diff->$prop . ' ' . $human;
					
					if ($diff->$prop == 1) {
						$s = substr($s, 0, -1);
					}
					
					$str[] = $s;
				}
			}
			
			return implode(', ', $str);
		}

		/**
		 * Takes a birthdate and normalizes it.
		 *
		 * @param  String $format     Format for the birthdate, e.x. dmy, d.m.Y, ...
		 * @param  String $birthdate  Birthdate in the specified $format
		 * @param  String $returnType Return Type, either 'string', 'object' or 'integer'
		 *
		 * @throws InvalidArgumentException when $birthdate cannot be parsed by FancyDateTime
		 *
		 * @return mixed
		 */
		public static function normalizeBirthdate($format, $birthdate, $returnType = 'string') {
			$dt = self::createFromFormat($format, $birthdate);

			if (!$dt || (int) $birthdate == 0) {
				throw new \InvalidArgumentException($birthdate . ' is not a valid ' . $format . ' date.');
			}

			// avoid ridiculous PHP guesses when created from 2-digit year
			// one has to be at least 16 to use our system
			if ($dt->format('Y') > date('Y') - 16) {
				$dt->modify('-100 years');
			}

			$dt->setTime(0, 0, 0);

			switch ($returnType) {
				case 'object':
					return $dt;

				case 'integer':
					return $dt->getTimestamp();

				case 'string':
				default:
					return $dt->format('d.m.Y');
			}
		}

		/**
		 * Get the current timestamp in regard to our timezone settings and configurations
		 *
		 * @return int The timestamp
		 */
		public static function getCurrentTimestamp(): int {
			return (new self())->getTimestamp();
		}

		public static function isValid(string $input) {
			try {
				$ignore = new self($input);
				return $ignore instanceof FancyDateTime;
			} catch (Exception $ex) {
				return false;
			}
		}

		/**
		 * Get all dates between startdate and enddate in an array.
		 * 
		 * @param  DateTimeInterface $startdate Start Date
		 * @param  DateTimeInterface $enddate   End Date
		 * 
		 * @return array
		 */
		public static function interval(\DateTimeInterface $startdate, \DateTimeInterface $enddate): array {
			$interval = new \DateInterval('P1D');
			$clonedEnd = clone $enddate;

			$range = new \DatePeriod($startdate, $interval, $clonedEnd->modify('+1 day'));

			$arr = [];

			foreach ($range as $date) {
				$arr[] = $date->format('d.m.Y');
			}

			return $arr;
		}
	}