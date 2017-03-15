<?php
	namespace Adepto\Fancy\Tests;
	
	use Adepto\Fancy\FancyFunctions;

	/**
	* @backupGlobals disabled
	* @backupStaticAttributes disabled
	*/
	class FunctionsTest extends \PHPUnit\Framework\TestCase {

		public function testStrToHex() {
			$str = 'bfx';
			$hex = FancyFunctions::strToHex($str);

			$this->assertEquals('626678', $hex);
			$this->assertEquals($str, FancyFunctions::hexToStr($hex));
		}

		public function testMakeClickable() {
			$original = 'This is http://adepto.as/';
			$clickable = FancyFunctions::makeClickable($original);

			$this->assertContains('<a target="_blank" href="http://adepto.as/">', $clickable);
		}

		public function testIsCLI() {
			$this->assertTrue(FancyFunctions::isCLI());
		}

		public function testEscapeCSVField() {
			$fields = [
				[
					'Some Test',
					'Some Test'
				],

				[
					"Some\nTest",
					"Some Test"
				],

				[
					"Some; Test",
					"\"Some; Test\""
				],

				[
					"11 N Curly Willow Cir\r\nThe Woodlands, Texas 77375, USA",
					"11 N Curly Willow Cir The Woodlands, Texas 77375, USA"
				],
			];

			foreach ($fields as $f) {
				$this->assertEquals($f[1], FancyFunctions::escapeCSVField($f[0], ';'));
			}
		}

		public function testAnyEmpty() {
			$hasEmpty = [ 42, true, '', 'adepto' ];
			$notEmpty = [ 42, true, 'bfx', 'adepto' ];

			$this->assertTrue(FancyFunctions::anyEmpty(...$hasEmpty));
			$this->assertFalse(FancyFunctions::anyEmpty(...$notEmpty));
		}

		public function testAllEmpty() {
			$isEmpty = [ '', null, 0 ];
			$notEmpty = [ '', true, 42 ];

			$this->assertTrue(FancyFunctions::allEmpty(...$isEmpty));
			$this->assertFalse(FancyFunctions::allEmpty(...$notEmpty));
		}
	}