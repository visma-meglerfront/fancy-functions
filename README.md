# Fancy Functions

This is a very convenient collection of commonly used functions for arrays, colors, DateTime, math, NLP and strings.

## Installation

Add this to `composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/adeptoas/fancy-functions"
    }
],

"require": {
    "adeptoas/fancy": "^1.0.0"
}
```

Make sure to merge your `require`-blocks!

## Usage

### Abstract FancyArray

```php
static hasEmptyValues(array $arr): bool
```
Check if an array has any empty values.

```php
static flatten(array $array): array
```
Flatten an array from a tree-like structure. Does NOT retain keys!

```php
static flattenAssoc(array $arr, string $glue = '_'): array
```
Flatten an array from a tree-like structure. As opposed to flatten this DOES retain keys. Nested keys are joined by {@param glue} in the flattened array.

```php
static flattenValues(array $arr, string $glue = '_'): array
```
Flatten an array from a tree-like structure. This copies values from the origin array into a list of sequential flat values. Nested keys are joined by {@param glue} in the flattened array

```php
static isSequential(array $arr): bool
```
Check whether a given array is sequential. Sequential arrays are specified by having numeric keys in ascending order.

```php
static isAssociative(array $arr): bool
```
Check whether a given array is associative.

```php
static arrayToObject(array $arr): stdClass
```
Convert an array to an object. This deep-copies everything from the array to the object.

```php
static objectToArray(stdClass $obj): array
```
Convert an object to an array. This deep-copies everything from the object to the array.

```php
static difference(...$arrays): array
```
Get the difference between a base array and many other arrays. The difference comes back in an array like so:
```[
    'add'     => [ elements to add ],
    'remove'  => [ elements to remove ],
    'count'   => [ number of changes ]
]
```

```php
static dbEncode(array $toEncode): array
```
Encodes longer arrays for storing in DB.

```php
static dbDecode($toDecode): array
```
Decodes array values stored in the DB.

```php
static findHighestCount(array $arr, $countSelf = false): int
```
Find the highest (recursive) count in an array.

```php
static clone($source): array
```
Copy an array for use with SOAP servers.

```php
static moveElement(array $arr, $old, $new): array
```
Move an element in an array.

```php
static replaceElement(array $arr, $oldElement, $newElement): array
```
Replace an element in an array. Do not pass the indexes!

```php
static replaceElements(array $arr, array $oldElements, array $newElements): array
```
Replace multiple elements in an array at once.

```php
static appendElement(array $arr, $elements, int $position = -1): array
```
Append one or more elements to an array at $position.

```php
static uniqueCallback(array $arr, callable $cb, bool $map = false): array
```
Remove duplicates from an array by asking the callback for a string-representation of the current element before comparison.

```php
static hasAll(array $haystack, array $needles): bool
```
Check if all $needles are in the array.

```php
static hasAny(array $haystack, array $needles): bool
```
Check if any elements from $needles is in the array $haystack.

```php
static assertType(array $array, string $class)
```
Check if all elements of $array have the type $class. Throws Exception if this is not case, otherwise does nothing.

```php
static diffAssocRecursive(array $first, array $second): array
```
Get all keys and elements that are in the first but not in the second array.

```php
static colonAccess(array $arr, string $index, string $delimiter = ':')
```
Reduce n-dimensional access to 1-dimensional access.

```php
static toCSV(array $arr, string $delimiter = ';'): string
```
Convert an array to a CSV string. First array are the headings, subsequent arrays the contents.

```php
static toXML(array $arr, array $namespaces = []): string
```
Convert an array to an XML string. Provide namespaces as a key/value array and use the namespace prefix
in the keys of the source array, e.g. "xsd:schema".

```php
static flipSequential(array $arr, $default = 0): array
```
Flip key and value in an array with a default value.

### Abstract FancyColor

```php
static adjustBrightness(string $hex, int $steps): string
```
Adjust a colors' brightness.

```php
static getLuminance(string $hex): int
```
Get the luminance of a color.

```php
static hexToHSB(string $hex): array
```
Convert a hex color to HSB values.

```php
static RGBtoHSB(int $r, int $g, int $b): array
```
Convert an RGB color to an HSB value.

```php
static getOppositeLuminanceColorFor(string $hex): string
```
Get the opposite luminance color for $hex.

```php
static getOppositeLuminanceKeywordFor(string $hex): string
```
Get the opposite luminance color keyword for $hex.

### FancyDateTime extends DateTime

```php
static fromTimestamp(int $ts): FancyDateTime
```
Create FancyDateTime from a timestamp.

```php
static fromMySQL(string $mySQL): FancyDateTime
```
Create FancyDateTime from a MySQL string.

```php
static todayAtMidnight(): FancyDateTime
```
Shorthand for getting the start of current day.

```php
static now(): FancyDateTime
```
Create FancyDateTime with the current date and time.

```php
static epoch(): FancyDateTime
```

```php
static fromDateTime(DateTimeInterface $other): FancyDateTime
```
Copy the value of another DateTimeInterface

```php
static createFromFormat($format, $time, $timezone = null): string
```
Create FancyDateTime from a specific format or a list of formats.
If $format is an array all those formats will be tried out until a match
is found.

```php
static tryFormats(array $formats, string $time, $timezone = null): FancyDateTime
```
Try creating FancyDateTime from a string usung diffrent formats.

```php
static timestampToDate(int $ts, $format = 'd.m.Y'): string
```
Convert a timestamp to a given date format.

```php
isWeekend(): bool
```
Check if this date is on a weekend.

```php
isWeekday(int $day): bool
```
Check if this date is a specific weekday.
0 = Sunday

```php
roundToMidnight(): FancyDateTime
```
Round time to midnight.

```php
startOfMinute(): FancyDateTime
```
Set time to the start of the minute.

```php
endOfMinute(): FancyDateTime
```
Set time to the end of the minute.

```php
startOfHour(bool $cascade = false): FancyDateTime
```
Set time to the start of the hour.

```php
endOfHour(bool $cascade = false): FancyDateTime
```
Set time to one second before the hour ends.

```php
startOfDay(bool $cascade = false): FancyDateTime
```
Normalize this date to morning midnight.

```php
endOfDay(bool $cascade = false): FancyDateTime
```
Normalize this date to evening midnight.

```php
startOfWeek(bool $cascade = false): FancyDateTime
```
Set date to the start of the first day of the week.

```php
endOfWeek(bool $cascade = false): FancyDateTime
```
Set date to the end of the last day of the week.

```php
startOfMonth(bool $cascade = false): FancyDateTime
```
Set date to the start of the first day of the month.

```php
endOfMonth(bool $cascade = false): FancyDateTime
```
Set date to the end of the last day of the month.

```php
startOfYear(bool $cascade = false): FancyDateTime
```
Set date to the start of the first day of the year.

```php
endOfYear(bool $cascade = false): FancyDateTime
```
Set date to the end of the last day of the year.

```php
yesterday(): FancyDateTime
```
Set date to the day before.

```php
tomorrow(): FancyDateTime
```
Set the date to tomorrow.

```php
isDivisibleByMinutes(DateTime $end, $minutes): bool
```
Checks whether this date is divisible by a certain factor of minutes.

```php
equalsDay(DateTime $dt)
```
Check if two FancyDateTimeobjects have the same day.

```php
equalsSecond(DateTime $dt)
```
Check if $dt is equal to the FancyDateTime this method is called from. Accurate to the second.

```php
toMySQL():string
```
Format this date for use MySQL.

```php
static getFirstAndLastPossibleDate($timestamp, $interval, $offset = 0): array
```
Get the first and last possible dates for a given timeframe based in a timestamp.

```php
static timeDiffToString(int $diff): string
```
Convert a time difference in seconds to a human-readable string.

```php
static normalizeBirthdate($format, $birthdate, $returnType = 'string')
```
Takes a birthdate and normalizes it.

```php
static getCurrentTimestamp(): int
```
Get the current timestamp in regard to our timezone settings and configurations.

```php
static isValid(string $input): bool
```
Checks, if the input can be used to create a FancyDateTimeobject.

```php
static interval(DateTimeInterface $startdate, DateTimeInterface $enddate): array
```
Get all dates between startdate and enddate in an array.

### Abstract FancyFunctions

```php
static classImplements($class, $interface): bool
```
Checks whether a class implements an interface or not.

```php
static strToHex($string): string
```
Convert a string to a hex number.

```php
static hexToStr($hex): string
```
Convert a hex number to a string.

```php
static imageToPNGData($file): string
```
Convert a file to PNG data. Returns the file as a data URI: data:image/png;base64,<data>

```php
static imageBlobToPNGData($imageBlob): string
```
Convert image blob data (in PNG format) to PNG data.

```php
static imageBlobToJPEGData($imageBlob): string
```
Convert image blob data (in JPEG format) to JPEG data.

```php
static imageBlobToSVGData($imageBlob): string
```
Convert image blob data (in SVG format) to SVG data.

```php
static makeClickable($str): string
```
Make all links in $str clickable.

```php
static isCLI(): bool
```
Check if PHP is running in CLI.

```php
static escapeCSVField($field, string $delimiter = ';'): string
```
Escape a single CSV value.

```php
static anyEmpty(...$vars): bool
```
Check if any of the passed parameters are empty.

```php
static allEmpty(...$vars): bool
```
Check if all of the empty parameters are empty.

```php
static between($nr, $first, $last): bool
```
Check if a number is between two values. Between means: 5 is >= 3 and <= 9.

```php
static curry(callable $fn, ...$args)
```
Curry a function from left. Currying means to bind some arguments to a function and return a new function with the passed arguments bound.
i.e.: You have a function that takes two integers and adds them up. If you now want to auto-add "2" to all items in an array of numbers, you can use curry to pre-bind "2" to your function and then just pass it to array_map:
         ```php 
         $addedTwo = array_map(curry('add2', 2), $arrayOfNumbers);
         ```

```php
static curryRight(callable $fn, ...$args)
```
Curry a function from right. For documentation on how currying works and what it does, {@see FancyFunctions::curry}.

```php
static stringToCSSClass($str, $prefix = ''): string
```
Create string suitable as a css class.

```php
static issetByReference(&$varVal): bool
```
Check if a value is set by reference.

```php
static isAJAXrequest(array $s = null): bool
```
Check if a request is an ajax request.

```php
static assertType($object, string $class)
```
Check if the object is instance of a class. Throws an exception if this is not the case, otherwise does nothing.

### Abstract: FancyMath

```php
static permute(array $values, $length, $repetitive = false, $ordered = true, $inclusive = false, $bottom = 1): array
```
Permute values.

```php
static binCoeff($n, $k): int
```
Calculates binomial coefficient.

```php
static fact($n): int
```
Calculates factorials.

```php
static powLimit($n, $exp): int
```
Calculates limited factorials as pseudo-power.

### FancyNLP

 ```php
static is(string $input, string $match, int $errorMargin = 0): bool
 ```
 Compares two words, including the possibility to accept a given margin of error. This is to avoid typos as specified by Levenshtein algorithm

```php
static extrapolateSymbols(string $input): string
```
Generate a replacement string where special symbols from a lot of languages are replaced by standard ISO alphanumeric characters

```php
static permuteAndExtrapolateSymbols(string $input): array
```
Generate a replacement set of strings where special symbols from a lot of languages COULD BE replaced by standard ISO alphanumeric characters.

### Abstract: FancyString

```php 
toKebapCase(string $str): string
```
Convert a string to kebap-case.

```php
toSnakeCase(string $str): string
```
Convert a string to snake_case.

```php
toCamelCase(string $str): string
```
Convert a string to camelCase.

```php
toLowerCase(string $str): string
```
Convert a string to lowercase.

```php
toUpperCase(string $str): string
```
Convert a string to uppercase.

```php
static ellipsisCenter($str, $maxLen, $char = '…'): string
```
 Add an ellipsis (…) to the center of the string if it is too long.

 ```php
static ellipsisEnd($str, $maxLen, $char = '…'): string
 ```
 Add an ellipsis (…) to the end of the string if it is too long.

 ```php
static randString($length = 12, $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
 ```
 Generate a random string based on a character set.

 ```php
static removeWhitespace($var)
 ```
 Remove whitespace from $var. This is useful for comparing values which can contain whitespaces. $var can be either string or array.

 ```php
static slugify($text): string
 ```
 Returns the text in a way that humans and computers can read it.

