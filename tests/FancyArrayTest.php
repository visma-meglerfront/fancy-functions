<?php
	namespace Adepto\Fancy\Tests;

	use Adepto\Fancy\FancyArray;

	/**
	* @backupGlobals disabled
	* @backupStaticAttributes disabled
	*/
	class FancyArrayTest extends \PHPUnit\Framework\TestCase {
		public function testHasEmptyValues() {
			$hasEmpty = [
				[
					''
				],

				'adepto',
				'bluefirex'
			];

			$notEmpty = [
				'bluefirex',
				42,
				[
					true,
					'hello'
				]
			];

			$this->assertTrue(FancyArray::hasEmptyValues($hasEmpty));
			$this->assertFalse(FancyArray::hasEmptyValues($notEmpty));
		}

		public function testFlatten() {
			$tree = [
				6,
				[
					9,
					0,
					6
				]
			];

			$flat = [
				6,
				9,
				0,
				6
			];

			$this->assertEquals($flat, FancyArray::flatten($tree));
		}

		public function testArrayToObject() {
			$array = [
				'hello'		=>	100,
				'there'		=>	210,
				'buddy'		=>	[
					90,
					110
				],
				'hows'		=>	[
					'it'	=>	[
						'going'		=>	true
					]
				]
			];

			$object = new \stdClass();
			$object->hello = 100;
			$object->there = 210;
			$object->buddy = (object) [ 90, 110 ];

			$object->hows = new \stdClass();
			$object->hows->it = new \stdClass();
			$object->hows->it->going = true;

			$this->assertEquals($object, FancyArray::arrayToObject($array));
		}

		public function testObjectToArray() {
			$object = new \stdClass();
			$object->hello = 100;
			$object->there = 210;
			$object->buddy = (object) [ 90, 110 ];

			$object->hows = new \stdClass();
			$object->hows->it = new \stdClass();
			$object->hows->it->going = true;

			$array = [
				'hello'		=>	100,
				'there'		=>	210,
				'buddy'		=>	[
					90,
					110
				],
				'hows'		=>	[
					'it'	=>	[
						'going'		=>	true
					]
				]
			];

			$this->assertEquals($array, FancyArray::objectToArray($object));
		}

		public function testDifferenceSimple() {
			$arr1 = [ 10, 20, 30, 40 ];
			$arr2 = [ 20, 30, 50 ];

			$diff = FancyArray::difference($arr1, $arr2);

			$this->assertEquals([ 50 ], $diff['add']);
			$this->assertEquals([ 10, 40 ], $diff['remove']);
			$this->assertEquals(3, $diff['count']);
		}

		public function testDifferenceExtended() {
			$arr1 = [
				10,
				20,
				30
			];

			$arr2 = [
				10,
				40,
				30
			];

			$arr3 = [
				10,
				90
			];

			$diff = FancyArray::difference($arr1, $arr2, $arr3);

			$this->assertEquals([
				20
			], $diff['remove']);

			$this->assertEquals([
				40,
				90
			], $diff['add']);

			$this->assertEquals(3, $diff['count']);
		}

		public function testFindHighestCount() {
			$arrays = [
				[
					'array'	=>	[
						5,
						9,
						3
					],

					'expectedCount'	=>	0
				],

				[
					'array'	=>	[
						[
							9
						],

						9,
						3
					],

					'expectedCount'	=>	1
				],

				[
					'array'	=>	[
						[
							9,
							2,

							[
								9,
								0,
								3,
								1,
								0
							]
						],

						9,
						3
					],

					'expectedCount'	=>	5
				],
			];

			foreach ($arrays as $test) {
				$this->assertEquals($test['expectedCount'], FancyArray::findHighestCount($test['array']));
			}
		}

		public function testMoveElement() {
			$original = [5, 9, 2, 0];
			$oldIndex = 3;
			$newIndex = 0;

			$moved = FancyArray::moveElement($original, 3, 0);

			$this->assertEquals($original[$oldIndex], $moved[$newIndex]);
		}

		public function testReplaceElement() {
			$original = [ 42, 1337, 'adepto' ];
			$modified = FancyArray::replaceElement($original, 1337, 9001);

			$this->assertEquals(9001, $modified[1]);
		}

		public function testReplaceElements() {
			$original = [ 42, 1337, 'adepto' ];
			$modified = FancyArray::replaceElements($original, [ 42, 1337 ], [ 0, 9001 ]);

			$this->assertEquals(0, $modified[0]);
			$this->assertEquals(9001, $modified[1]);
		}

		public function testAppendElement() {
			$map = [
				[
					'original'		=>	[1, 2, 3],
					'append'		=>	[4],
					'position'		=>	0,
					'result'		=>	[4, 1, 2, 3],
					'desc'			=>	'Add one element as array on first position'
				],

				[
					'original'		=>	[1, 2, 3],
					'append'		=>	[4],
					'position'		=>	-1,
					'result'		=>	[1, 2, 3, 4],
					'desc'			=>	'Add one elements as array on last position'
				],

				[
					'original'		=>	[1, 2, 3],
					'append'		=>	[4, 5],
					'position'		=>	2,
					'result'		=>	[1, 2, 4, 5, 3],
					'desc'			=>	'Add two elements on third position (2)'
				],

				[
					'original'		=>	[1, 2, 3],
					'append'		=>	4,
					'position'		=>	0,
					'result'		=>	[4, 1, 2, 3],
					'desc'			=>	'Add non-array value on first position'
				]
			];

			foreach ($map as $config) {
				$this->assertEquals(
					$config['result'],

					FancyArray::appendElement(
						$config['original'],
						$config['append'],
						$config['position']
					),

					$config['desc']
				);
			}
		}

		public function testHasAll() {
			$this->assertTrue(FancyArray::hasAll(['one', 'two', 'three'], ['one', 'two']));

			$this->assertFalse(FancyArray::hasAll(['one', 'two', 'three'], ['one', 'two', 'four']));
			$this->assertFalse(FancyArray::hasAll(['one', 'three'], ['one', 'two']));
		}

		public function testHasAny() {
			$this->assertTrue(FancyArray::hasAny(['one', 'two', 'three'], ['one']));
			$this->assertTrue(FancyArray::hasAny(['one', 'two', 'three'], ['one', 'two']));
			$this->assertTrue(FancyArray::hasAny(['one', 'two', 'three'], ['one', 'six', 'seven', 'nine']));

			$this->assertFalse(FancyArray::hasAny(['one', 'two', 'three'], ['four']));
			$this->assertFalse(FancyArray::hasAny(['one', 'three'], []));
		}

		public function testColonAccess() {
			$this->assertEquals(FancyArray::colonAccess(['one'	=>	['some', 'value']], 'one:0'), 'some');
			$this->assertEquals(FancyArray::colonAccess(['one'	=>	['some', 'value']], 'one!__!0', '!__!'), 'some');

			$this->assertEquals(FancyArray::colonAccess(['one'	=>	['some', ['nested', 'value']]], 'one::1::0', '::'), 'nested');
		}

		public function testFlipSequential() {
			$this->assertEquals(FancyArray::flipSequential(['one', 'two']), ['one'	=>	0, 'two'	=>	0]);
		}

		public function testDepth() {
			$this->assertEquals(FancyArray::depth([]), 1);
			$this->assertEquals(FancyArray::depth([[]]), 2);
			$this->assertEquals(FancyArray::depth([[[[[]]]]]), 5);

			$this->assertEquals(FancyArray::depth([
				'someKey'	=>	[
					'someFancyKey'	=>	[
						'someReallyFancyValue'
					]
				],
				'otherKey'	=>	[
					'someNotSoFancyValue'
				]
			]), 3);
		}

		public function testDeepCount() {
			$this->assertEquals(FancyArray::deepCount([]), 0);
			$this->assertEquals(FancyArray::deepCount(['some', 'element']), 2);

			$this->assertEquals(FancyArray::deepCount(['some', ['nested', 'element']]), 3);

			$arr = [
				'key'		=>	'value',
				'nested'	=>	[
					'array',
					'full'	=>	'of',
					[
						'surprises',
						':)'
					]
				]
			];

			$this->assertEquals(FancyArray::deepCount($arr), FancyArray::flatCount($arr));
		}
	}