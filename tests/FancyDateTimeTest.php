<?php
	namespace Adepto\Fancy\Tests;
	
	use Adepto\Fancy\FancyDateTime;

	/**
	* @backupGlobals disabled
	* @backupStaticAttributes disabled
	*/
	class FancyDateTimeTest extends \PHPUnit\Framework\TestCase {
		public function testFromTimestamp() {
			$timestamp = '1466077020';
			$date = '16.06.2016, 13:37';
			$extdt = FancyDateTime::fromTimestamp($timestamp);

			$this->assertEquals($timestamp, $extdt->getTimestamp());
			$this->assertEquals($date, $extdt->format('d.m.Y, H:i'));
		}

		public function testFromDateTime() {
			$timestamp = '1466077020';

			$extDt = FancyDateTime::fromTimestamp($timestamp);
			$cloneExtDt = FancyDateTime::fromDateTime($extDt);

			$this->assertEquals($extDt->getTimestamp(), $timestamp);
			$this->assertEquals($cloneExtDt->getTimestamp(), $timestamp);
			$this->assertEquals($extDt, $cloneExtDt);

			$cloneExtDt->modify('+10 seconds');
			$this->assertEquals($cloneExtDt->getTimestamp(), '1466077030');

			$this->assertNotEquals($extDt, $cloneExtDt);
		}

		public function testTodayAtMidnight() {
			$midnight = FancyDateTime::todayAtMidnight();
			$manualMidnight = (new FancyDateTime())->setTime(0, 0, 0);

			$this->assertEquals($midnight, $manualMidnight);
			$this->assertEquals($midnight->getTimestamp(), $manualMidnight->getTimestamp());
		}

		public function testToMySQL() {
			$extdt = new FancyDateTime();

			$this->assertEquals(date('Y-m-d H:i'), $extdt->toMySQL());
		}

		public function testWeekend() {
			$dates = [
				new FancyDateTime('Last Saturday'),
				new FancyDateTime('This Monday'),
				new FancyDateTime('Next Sunday'),
				new FancyDateTime('First Wednesday of Last Month')
			];

			$expectationsBinary = [
				true,
				false,
				true,
				false
			];

			$expectationsDays = [
				FancyDateTime::WEEKDAY_SATURDAY,
				FancyDateTime::WEEKDAY_MONDAY,
				FancyDateTime::WEEKDAY_SUNDAY,
				FancyDateTime::WEEKDAY_WEDNESDAY
			];

			for ($i = 0, $max = count($dates); $i < $max; $i++) {
				$this->assertEquals($expectationsBinary[$i], $dates[$i]->isWeekend());
				$this->assertTrue($dates[$i]->isWeekday($expectationsDays[$i]));
			}
		}

		public function testDiffNow() {
			$dt = new \DateTime();
			$extdt = new FancyDateTime();

			$this->assertEquals($dt->diff(clone $dt)->d, $extdt->diff()->d);
		}

		public function testCreateFromFormat() {
			$this->assertInstanceOf('Adepto\\Fancy\\FancyDateTime', FancyDateTime::createFromFormat('d.m.Y', '30.01.1996'));
			$this->assertInstanceOf('Adepto\\Fancy\\FancyDateTime', FancyDateTime::createFromFormat([ 'd.m.Y', 'dmy' ], '30.01.1996'));
		}

		public function testTimestampToDate() {
			$this->assertEquals('30.01.1996', FancyDateTime::timestampToDate(mktime(0, 0, 0, 1, 30, 1996), 'd.m.Y'));
			$this->assertEquals('2016-02-27 00:42:01', FancyDateTime::timestampToDate(mktime(0, 42, 1, 2, 27, 2016), 'Y-m-d H:i:s'));
		}

		/**
		 * @expectedException TypeError
		 */
		public function testTimestampToDateError() {
			FancyDateTime::timestampToDate('Trololololololol', 'Y');
		}

		/**
		 * @depends testFromTimestamp
		 */
		public function testRoundToMidnight() {
			$morning = FancyDateTime::fromTimestamp(370944000); //3. 10. 81, 9:00 AM
			$evening = FancyDateTime::fromTimestamp(370993380); //3. 10. 81, 10:43 PM
			$noon = FancyDateTime::fromTimestamp(370954800); //3. 10. 81, 12:00 PM (um zwölfe mittags!)

			$this->assertEquals($morning->roundToMidnight()->getTimestamp(), 370911600); //3. 10. 81, 12:00 AM
			$this->assertEquals($evening->roundToMidnight()->getTimestamp(), 370998000); //4. 10. 81, 12:00 AM
			$this->assertEquals($noon->roundToMidnight()->getTimestamp(), 370998000); //4. 10. 81, 12:00 AM
		}

		public function testIsDivisibleByMinutes() {
			$config = [
				[
					'start'		=>	new FancyDateTime('01.01.2016, 0:01'),
					'end'		=>	new FancyDateTime('01.01.2016, 0:10'),
					'factor'	=>	480,
					'assert'	=>	false
				],

				[
					'start'		=>	new FancyDateTime('01.01.2016, 11:58'),
					'end'		=>	new FancyDateTime('01.01.2016, 12:26'),
					'factor'	=>	360,
					'assert'	=>	true
				]
			];

			foreach ($config as $c) {
				$this->assertEquals($c['assert'], $c['start']->isDivisibleByMinutes($c['end'], $c['factor']), $c['start']->format('H:i') . ' - ' . $c['end']->format('H:i'));
			}
		}

		public function testNormalizeBirthdate() {
			$birthdates = [
				[
					'year'			=>	1996,
					'realDate'		=>	'30.01.1996',
					'format'		=>	'dmy',
					'formattedDate'	=>	'300196',
					'timestamp'		=>	822956400
				],

				[
					'year'			=>	1967,
					'realDate'		=>	'05.03.1967',
					'format'		=>	'dmy',
					'formattedDate'	=>	'050367',
					'timestamp'		=>	-89254800
				],

				[
					'year'			=>	1999,
					'realDate'		=>	'24.12.1999',
					'format'		=>	'd-m-y',
					'formattedDate'	=>	'24-12-99',
					'timestamp'		=>	945990000,
				],

				[
					'year'			=>	1926,
					'realDate'		=>	'24.12.1926',
					'format'		=>	'd.m-y',
					'formattedDate'	=>	'24.12-26',
					'timestamp'		=>	-1357693200
				]
			];

			foreach ($birthdates as $arr) {
				$object = FancyDateTime::normalizeBirthdate($arr['format'], $arr['formattedDate'], 'object');
				$string = FancyDateTime::normalizeBirthdate($arr['format'], $arr['formattedDate'], 'string');
				$integer = FancyDateTime::normalizeBirthdate($arr['format'], $arr['formattedDate'], 'integer');

				$this->assertInstanceOf('Adepto\\Fancy\\FancyDateTime', $object);
				$this->assertEquals($arr['year'], $object->format('Y'));
				$this->assertEquals($arr['realDate'], $string);
				$this->assertEquals($arr['timestamp'], $integer);
			}
		}

		/**
		 * @expectedException InvalidArgumentException
		 */
		public function testInvalidNormalizeBirthdate() {
			FancyDateTime::normalizeBirthdate('dmy', '000000', 'object');
		}

		public function testTimeDiffToString() {
			$diffs = [
				[
					'seconds'		=>	90,
					'string'		=>	'1 minute, 30 seconds'
				],

				[
					'seconds'		=>	6,
					'string'		=>	'6 seconds'
				],

				[
					'seconds'		=>	120,
					'string'		=>	'2 minutes'
				],

				/* [
					'seconds'		=>	180122,
					'string'		=>	'2 days, 2 hours, 2 minutes'
				] */
			];

			foreach ($diffs as $diff) {
				$this->assertEquals($diff['string'], FancyDateTime::timeDiffToString($diff['seconds']));
			}
		}

		public function testFirstAndLastPossibleDate() {
			$timestamp = 822956400; // 30.01.1996
			$intervals = [ 'month', 'day' ];

			foreach ($intervals as $interval) {
				$dates = FancyDateTime::getFirstAndLastPossibleDate($timestamp, $interval);
				
				$this->assertInstanceOf('Adepto\\Fancy\\FancyDateTime', $dates['first']);
				$this->assertInstanceOf('Adepto\\Fancy\\FancyDateTime', $dates['last']);

				switch ($interval) {
					case 'month':
						$this->assertEquals('01.01.1996', $dates['first']->format('d.m.Y'));
						$this->assertEquals('31.01.1996', $dates['last']->format('d.m.Y'));
						break;

					case 'day':
						$this->assertEquals('00:00', $dates['first']->format('H:i'));
						$this->assertEquals('23:59', $dates['last']->format('H:i'));
						break;
				}
			}
		}

		public function testEqualsDay() {
			$this->assertTrue((new FancyDateTime('24.04.2016, 01:02'))->equalsDay(new \DateTime('24.04.2016, 13:37')));
			$this->assertFalse((new FancyDateTime('24.03.2016, 00:01'))->equalsDay(new FancyDateTime('24.04.2016, 00:01')));
		}

		public function testInterval() {
			$this->assertEquals(FancyDateTime::interval(new FancyDateTime('10.03.2017'), new FancyDateTime('13.03.2017')),
			['10.03.2017', '11.03.2017', '12.03.2017', '13.03.2017']);
		}
	}