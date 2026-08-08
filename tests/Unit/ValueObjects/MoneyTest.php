<?php

use App\ValueObjects\Money;

test('creates money from cents and zero factory', function () {
    $money = Money::fromCents(1250);
    expect($money->cents)->toBe(1250);
    expect($money->toPrimitive())->toBe(1250);
    expect($money->toDecimal())->toBe(12.5);

    $zero = Money::zero();
    expect($zero->cents)->toBe(0);
    expect($zero->isZero())->toBeTrue();
});

test('throws exception when creating with negative amount', function (int|float|string $invalidValue) {
    expect(fn () => is_int($invalidValue) ? new Money($invalidValue) : Money::fromDecimal($invalidValue))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'negative int' => -1,
    'negative cents' => -500,
    'negative float' => -10.50,
    'negative string decimal' => '-25.00',
]);

test('creates money from valid decimal inputs', function (float|int|string $input, int $expectedCents) {
    $money = Money::fromDecimal($input);

    expect($money->cents)->toBe($expectedCents);
})->with([
    'float with two decimals' => [10.50, 1050],
    'float with single decimal' => [10.5, 1050],
    'integer as decimal' => [10, 1000],
    'string with dot' => ['150.75', 15075],
    'string with comma' => ['150,75', 15075],
    'small fractional string' => ['0.05', 5],
    'zero string' => ['0.00', 0],
]);

test('throws exception for non-numeric decimal input', function () {
    expect(fn () => Money::fromDecimal('invalid-number'))
        ->toThrow(InvalidArgumentException::class);
});

test('formats monetary amount to currency string', function () {
    $money = Money::fromCents(123456); // 1234.56
    $formatted = $money->toFormattedString();

    expect($formatted)->toContain('1.234,56');
});

test('performs addition and subtraction', function () {
    $m1 = Money::fromCents(2000); // 20.00
    $m2 = Money::fromCents(550);  // 5.50

    $sum = $m1->add($m2);
    expect($sum->cents)->toBe(2550);

    $diff = $m1->subtract($m2);
    expect($diff->cents)->toBe(1450);
});

test('throws exception when subtraction results in negative amount', function () {
    $small = Money::fromCents(100);
    $large = Money::fromCents(500);

    expect(fn () => $small->subtract($large))
        ->toThrow(InvalidArgumentException::class);
});

test('performs comparison checks', function () {
    $ten = Money::fromCents(1000);
    $twenty = Money::fromCents(2000);
    $zero = Money::zero();

    expect($ten->equals(Money::fromCents(1000)))->toBeTrue();
    expect($ten->equals($twenty))->toBeFalse();
    expect($ten->equals(null))->toBeFalse();

    expect($ten->isPositive())->toBeTrue();
    expect($zero->isPositive())->toBeFalse();
    expect($zero->isZero())->toBeTrue();

    expect($twenty->isGreaterThan($ten))->toBeTrue();
    expect($ten->isLessThan($twenty))->toBeTrue();
    expect($ten->isGreaterThanOrEqual($ten))->toBeTrue();
    expect($ten->isLessThanOrEqual($twenty))->toBeTrue();
});

test('serializes to string and json', function () {
    $money = Money::fromCents(5000);

    expect((string) $money)->toBe('5000');
    expect(json_encode($money))->toBe('5000');
});

test('instantiates via fromPrimitive with various types', function () {
    $fromMoney = Money::fromPrimitive(Money::fromCents(1000));
    expect($fromMoney->cents)->toBe(1000);

    $fromIntCents = Money::fromPrimitive(1000);
    expect($fromIntCents->cents)->toBe(1000);

    $fromStringIntCents = Money::fromPrimitive('1000');
    expect($fromStringIntCents->cents)->toBe(1000);

    $fromFloat = Money::fromPrimitive(10.50);
    expect($fromFloat->cents)->toBe(1050);

    $fromDecimalStr = Money::fromPrimitive('10.50');
    expect($fromDecimalStr->cents)->toBe(1050);

    $fromCommaDecimalStr = Money::fromPrimitive('10,50');
    expect($fromCommaDecimalStr->cents)->toBe(1050);
});

test('throws exception for invalid primitive input', function () {
    expect(fn () => Money::fromPrimitive(['invalid-array']))
        ->toThrow(InvalidArgumentException::class);
});
