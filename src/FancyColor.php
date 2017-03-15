<?php
	namespace Adepto\Fancy;

	/**
	 * FancyColor
	 *
	 * @author bluefirex, FeistyBall
	 * @version 1.0
	 * @package as.adepto.fancy
	 */
	abstract class FancyColor {
		/**
		 * Adjust a colors' brightness.
		 * http://stackoverflow.com/questions/3512311/how-to-generate-lighter-darker-color-with-php
		 *
		 * @param  string  $hex   color
		 * @param  integer $steps Steps to adjust. positive = lighter, negative = darker
		 *
		 * @return string
		 */
		public static function adjustBrightness(string $hex, int $steps): string {
			// Steps should be between -255 and 255. Negative = darker, positive = lighter
			$steps = max(-255, min(255, $steps));

			// Normalize into a six character long hex string
			$hex = str_replace('#', '', $hex);
			
			if (strlen($hex) == 3) {
				$hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
			}

			// Split into three parts: R, G and B
			$color_parts = str_split($hex, 2);
			$return = '#';

			foreach ($color_parts as $color) {
				$color   = hexdec($color); // Convert to decimal
				$color   = max(0, min(255, $color + $steps)); // Adjust color
				$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
			}

			return $return;
		}

		/**
		 * Get the luminance of a color.
		 * This is a perceived value by humans.
		 *
		 * @param  string $hex Hex Color value (# is removed automatically)
		 *
		 * @return int
		 */
		public static function getLuminance(string $hex): string {
			$hex = str_replace('#', '', $hex);

			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));

			// (0.2126*R + 0.7152*G + 0.0722*B) -> http://stackoverflow.com/questions/596216/formula-to-determine-brightness-of-rgb-color
			$luminance = (0.2126 * $r + 0.7152 * $g + 0.722 * $b);

			return $luminance;
		}

		/**
		 * Convert a hex color to HSB values.
		 *
		 * @param  string $hex Hex Color value (# is removed automatically)
		 *
		 * @return array
		 */
		public static function hexToHSB(string $hex): array {
			$hex = str_replace('#', '', $hex);

			if (strlen($hex) == 3) {
				$hex_expanded = '';

				for ($i = 0; $i < 3; $i++) {
					$hex_expanded .= str_repeat(substr($hex, $i, 1), 2);
				}

				$hex = $hex_expanded;
			}

			$r = hexdec(substr($hex, 0, 2));
			$g = hexdec(substr($hex, 2, 2));
			$b = hexdec(substr($hex, 4, 2));

			return self::RGBtoHSB($r, $g, $b);
		}

		/**
		 * Convert an RGB color to an HSB value.
		 *
		 * @param int $r Red
		 * @param int $g Green
		 * @param int $b Blue
		 *
		 * @return array
		 */
		public static function RGBtoHSB(int $r, int $g, int $b): array {
			// Convert the RGB byte-values to percentages
			$r = $r / 255;
			$g = $g / 255;
			$b = $b / 255;

			// Calculate a few basic values, the maximum value of R,G,B, the
			//   minimum value, and the difference of the two (chroma).
			$maxRGB = max($r, $g, $b);
			$minRGB = min($r, $g, $b);
			$chroma = $maxRGB - $minRGB;

			// Value (also called Brightness) is the easiest component to calculate,
			//   and is simply the highest value among the R,G,B components.
			// We multiply by 100 to turn the decimal into a readable percent value.
			$computedV = 100 * $maxRGB;

			// Special case if hueless (equal parts RGB make black, white, or grays)
			// Note that Hue is technically undefined when chroma is zero, as
			//   attempting to calculate it would cause division by zero (see
			//   below), so most applications simply substitute a Hue of zero.
			// Saturation will always be zero in this case, see below for details.
			if ($chroma == 0) {
				return [ 0, 0, $computedV ];
			}

			// Saturation is also simple to compute, and is simply the chroma
			//   over the Value (or Brightness)
			// Again, multiplied by 100 to get a percentage.
			$computedS = 100 * ($chroma / $maxRGB);

			// Calculate Hue component
			// Hue is calculated on the "chromacity plane", which is represented
			//   as a 2D hexagon, divided into six 60-degree sectors. We calculate
			//   the bisecting angle as a value 0 <= x < 6, that represents which
			//   portion of which sector the line falls on.
			if ($r == $minRGB) {
				$h = 3 - (($g - $b) / $chroma);
			} elseif ($b == $minRGB) {
				$h = 1 - (($r - $g) / $chroma);
			} else { // $g == $minRGB
				$h = 5 - (($b - $r) / $chroma);
			}

			// After we have the sector position, we multiply it by the size of
			//   each sector's arc (60 degrees) to obtain the angle in degrees.
			$computedH = 60 * $h;

			// If the computed hue is bigger than 360,
			//   calculate its modulo 360 so it fits in range from 0 to 360.
			if ($computedH >= 360) {
				$computedH = $computedH % 360;
			}

			return [ $computedH, $computedS, $computedV ];
		}

		/**
		 * Get the opposite luminance color for $hex.
		 *
		 * @param  string $hex Hex Color Value (# is removed automatically)
		 *
		 * @return string      '000000' or 'ffffff'
		 */
		public static function getOppositeLuminanceColorFor(string $hex): string {
			return self::getLuminance($hex) > 336 ? '000000' : 'ffffff';
		}

		/**
		 * Get the opposite luminance color keyword for $hex.
		 *
		 * @param  string $hex Hex Color Value (# is removed automatically)
		 *
		 * @return string      'dark' or 'light'
		 */
		public static function getOppositeLuminanceKeywordFor(string $hex): string {
			return self::getOppositeLuminanceColorFor($hex) == '000000' ? 'dark' : 'light';
		}
	}