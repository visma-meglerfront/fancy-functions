<?php
	namespace Adepto\Fancy\Tests;
	
	use Adepto\Fancy\FancyColor;

	/**
	* @backupGlobals disabled
	* @backupStaticAttributes disabled
	*/
	class FancyColorTest extends \PHPUnit\Framework\TestCase {
		
		public function testAdjustBrightness() {
			$original = '#292c34';
			$modified = FancyColor::adjustBrightness($original, 5);

			$this->assertEquals('#2e3139', $modified);
			$this->assertEquals($original, FancyColor::adjustBrightness($modified, -5));
		}

		public function testGetLuminance() {
			$color = '#292c34';
			$luminance = floor(FancyColor::getLuminance($color));

			$this->assertEquals(77, $luminance);
		}

		public function testRGBtoHSB() {
			$colors = [
				[
					'rgb'	=>	[ 0, 0, 0 ],
					'hsb'	=>	[ 0, 0, 0 ]
				],

				[
					'rgb'	=>	[ 255, 255, 255 ],
					'hsb'	=>	[ 0, 0, 100 ]
				],

				[
					'rgb'	=>	[ 0, 153, 204 ],
					'hsb'	=>	[ 195, 100, 80 ]
				]
			];

			foreach ($colors as $color) {
				list($red, $green, $blue) = $color['rgb'];
				list($hue, $sat, $brightness) = $color['hsb'];
				list($hue2, $sat2, $brightness2) = FancyColor::RGBtoHSB($red, $green, $blue);

				$this->assertEquals($hue, $hue2, 'Hue doesn\'t match', 0.01);
				$this->assertEquals($sat, $sat2, 'Saturation doesn\'t match', 0.01);
				$this->assertEquals($brightness, $brightness2, 'Brightness doesn\'t match', 0.01);
			}
		}

		public function testHextoHSB() {
			$colors = [
				[
					'hex'	=>	'#000000',
					'hsb'	=>	[ 0, 0, 0 ]
				],

				[
					'hex'	=>	'#ffffff',
					'hsb'	=>	[ 0, 0, 100 ]
				],

				[
					'hex'	=>	'#09c',
					'hsb'	=>	[ 195, 100, 80 ]
				]
			];

			foreach ($colors as $color) {
				$hex = $color['hex'];
				list($hue, $sat, $brightness) = $color['hsb'];
				list($hue2, $sat2, $brightness2) = FancyColor::hexToHSB($hex);

				$this->assertEquals($hue, $hue2, 'Hue doesn\'t match', 0.01);
				$this->assertEquals($sat, $sat2, 'Saturation doesn\'t match', 0.01);
				$this->assertEquals($brightness, $brightness, 'Brightness doesn\'t match', 0.01);
			}
		}

		public function testOppositeLuminanceColor() {
			$light = '#e0e0e0';
			$dark = '#292c34';

			$this->assertEquals('000000', FancyColor::getOppositeLuminanceColorFor($light));
			$this->assertEquals('ffffff', FancyColor::getOppositeLuminanceColorFor($dark));
		}
	}