<?php 

require_once __DIR__ . '/../../vendor/autoload.php'; // Autoload files using Composer autoload

use Lyx\Strings\Str;

define("C_TEST", 'Const test!');

lyx_println(Str::format("This is a simple {0} for {1} ('{0}', '{1}')", [
  'format',
  'testing'
]));

lyx_println(Str::format("This is an asoc keys {example} for {purpose} [{C_TEST}] ('{example}', '{purpose}')", [
  'example' => 'Case',
  'purpose' => 'Testing'
]));

lyx_println(Str::format("This is an asoc keys {example.arg1} for {purpose.a1} [{C_TEST}] ('{example.arg2}', '{purpose.a2}')", [
  'example' => [
    'arg1' => 'Example - Arg 1',
    'arg2' => 'Ex - Arg 2'
  ],
  'purpose' => [
    'a1' => 'Testing 1',
    'a2' => 'Test 2'
  ]
]));

// lyx_println(Str::format("Numbers: {0} | {1} | {2} / {0,.3} | {1,3.4} | {2,/.2}", $decNums));
// lyx_println(Str::format("Numbers: {0} | {1} | {2} / {0,.3} | {1,3.4} | {2,/.2}", $decNums, [
//   'dec_point' => ',',
//   'thousands_sep' => '.'
// ]));
// lyx_println(Str::format("Num bases: {0} | {1} / 0x{0:X,6} | 0x{1:x} / 0{0:O} | 0{1:O} / 0b{0:B} | 0b{1:B,16}", $hexNums));

$nums = [
  123,
  1.23,
  19000321,
  0xEE,
  0xA5F
];

$numCases = [
  'single_numbers' => [
    'format' => "{0} / {1} / {2}"
  ],
  'zero_padding/dec_point = ./thousand_sep = ,' => [
    'format' => "{0,.3} / {1,3.4} / {2,/.2}",
    'options' => [
      'dec_point' => ',',
      'thousands_sep' => '.'
    ]
  ],
  'zero_padding/dec_point = ,/thousand_sep = .' => [
    'format' => "{0,.3} / {1,3.4} / {2,/.2}"
  ],
  'base/hex' => [
    'format' => "{3:x} / 0x{3:X,6} / {4:x} / 0x{4:X,4}"
  ],
  'base/oct' => [
    'format' => "{3:o} / 0{3:o,5} / {4:o} / 0{4:o,4}"
  ],
  'base/bin' => [
    'format' => "{3:b} / 0b{3:b,8} / {4:b} / 0b{4:b,16}"
  ]
];

foreach($numCases as $name => $case) {
  echo "{$name}: ";
  lyx_println(Str::format($case['format'], $nums, $case['options'] ?? []));
}
