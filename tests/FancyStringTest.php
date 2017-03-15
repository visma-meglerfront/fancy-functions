<?php
	namespace Adepto\Fancy\Tests;

	use Adepto\Fancy\FancyString;
	
	/**
	* @backupGlobals disabled
	* @backupStaticAttributes disabled
	*/
	class FancyStringTest extends \PHPUnit\Framework\TestCase {

		public function testToKebapCase() {
			$map = [
				'this-is-a-test'		=>	'this-is-a-test',
				'thisIsATest'			=>	'this-is-a-test',
				'this_is_a_test'		=>	'this-is-a-test',
				'thisIsATest'			=>	'this-is-a-test',
				'ThisIsAtest'			=>	'this-is-atest',
				'This_thingIs-Weird'	=>	'this-thing-is-weird'
			];

			foreach ($map as $original => $transformed) {
				$this->assertEquals($transformed, FancyString::toKebapCase($original), $original);
			}
		}

		public function testToSnakeCase() {
			$map = [
				'this_is_a_test'		=>	'this_is_a_test',
				'thisIsATest'			=>	'this_is_a_test',
				'this-is-a-test'		=>	'this_is_a_test',
				'thisIsATest'			=>	'this_is_a_test',
				'ThisIsAtest'			=>	'this_is_atest',
				'This_thingIs-Weird'	=>	'this_thing_is_weird'
			];

			foreach ($map as $original => $transformed) {
				$this->assertEquals($transformed, FancyString::toSnakeCase($original), $original);
			}
		}

		public function testToCamelCase() {
			$map = [
				'thisIsATest'			=>	'thisIsATest',
				'this-is-a-test'		=>	'thisIsATest',
				'this_is_a_test'		=>	'thisIsATest',
				'thisIsATest'			=>	'thisIsATest',
				'ThisIsAtest'			=>	'thisIsAtest',
				'This_thingIs-Weird'	=>	'thisThingIsWeird'
			];

			foreach ($map as $original => $transformed) {
				$this->assertEquals($transformed, FancyString::toCamelCase($original), $original);
			}
		}

		public function testToLowercase() {
			$map = [
				'somestring'		=>	'somestring',
				'someString' 		=>	'somestring',
				'someSTRING'		=>	'somestring',
				'sømestring'		=>	'sømestring',
				'sØmestring'		=>	'sømestring',
				'SÖmeßtring'		=>	'sömeßtring',
				'sØmÉßtrÍNG'		=>	'søméßtríng'
			];

			foreach ($map as $original => $transformed) {
				$this->assertEquals($transformed, FancyString::toLowercase($original), $original);
			}
		}

		public function testToUppcase() {
			$map = [
				'SOMESTRING'		=>	'SOMESTRING',
				'somestring'		=>	'SOMESTRING',
				'someString'		=>	'SOMESTRING',
				'someßtring'		=>	'SOMESSTRING',
				'sømestring'		=>	'SØMESTRING',
				'sŒmeßtring'		=>	'SŒMESSTRING',
				'sœméßtring'		=>	'SŒMÉSSTRING'
			];

			foreach ($map as $original => $transformed) {
				$this->assertEquals($transformed, FancyString::toUppercase($original), $original);
			}
		}

		public function testEllipsisCenter() {
			$str = 'This is å løng string with special characters';
			$ellipsisCenter = FancyString::ellipsisCenter($str, 12, '…');

			$this->assertEquals('This i…acters', $ellipsisCenter);
		}

		public function testEllipsisEnd() {
			$str = 'This is å løng string with special characters';
			$ellipsisCenter = FancyString::ellipsisEnd($str, 12, '…');

			$this->assertEquals('This is å lø…', $ellipsisCenter);
		}

		public function testRemoveWhitespace() {
			$this->assertEquals('adepto', FancyString::removeWhitespace('a    de  p t o'));
		}
	}
