<?php

use App\ValueObjects\AccountCode;

test('creates account code from valid formats', function (string $code) {
    $accountCode = new AccountCode($code);

    expect($accountCode->value)->toBe(trim($code));
    expect($accountCode->toPrimitive())->toBe(trim($code));
    expect((string) $accountCode)->toBe(trim($code));
})->with([
    'top level single digit' => '1',
    'top level multi digit' => '10',
    'two levels' => '1.1',
    'three levels with leading zero' => '1.1.01',
    'four levels' => '1.1.01.001',
    'trimmed code' => '  2.1.03  ',
]);

test('throws exception for invalid account code formats', function (string $invalidCode) {
    expect(fn () => new AccountCode($invalidCode))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'empty string' => '',
    'spaces only' => '   ',
    'alphabetic string' => 'abc',
    'alphanumeric mixed' => '1.a.01',
    'double dot' => '1..1',
    'trailing dot' => '1.1.',
    'leading dot' => '.1.1',
    'negative number' => '-1.01',
    'special characters' => '1.1-01',
]);

test('calculates hierarchy level correctly', function (string $code, int $expectedLevel) {
    $accountCode = new AccountCode($code);

    expect($accountCode->level())->toBe($expectedLevel);
})->with([
    ['1', 1],
    ['1.1', 2],
    ['1.1.01', 3],
    ['1.1.01.001', 4],
    ['1.1.01.001.0001', 5],
]);

test('extracts code segments accurately', function () {
    $accountCode = new AccountCode('1.20.300.4000');

    expect($accountCode->segments())->toBe(['1', '20', '300', '4000']);
});

test('resolves parent code correctly', function () {
    $child = new AccountCode('1.1.01');
    $parent = $child->parentCode();

    expect($parent)->not->toBeNull();
    expect($parent?->value)->toBe('1.1');

    $grandParent = $parent?->parentCode();
    expect($grandParent)->not->toBeNull();
    expect($grandParent?->value)->toBe('1');

    $topParent = $grandParent?->parentCode();
    expect($topParent)->toBeNull();
});

test('identifies child and parent relationships', function () {
    $root = new AccountCode('1');
    $group = new AccountCode('1.1');
    $account = new AccountCode('1.1.01');
    $other = new AccountCode('2.1.01');

    expect($account->isChildOf($group))->toBeTrue();
    expect($account->isChildOf($root))->toBeTrue();
    expect($account->isChildOf($other))->toBeFalse();

    expect($group->isParentOf($account))->toBeTrue();
    expect($root->isParentOf($account))->toBeTrue();
    expect($other->isParentOf($account))->toBeFalse();
});

test('compares equality with other AccountCode instances', function () {
    $code1 = new AccountCode('1.1.01');
    $code2 = new AccountCode('1.1.01');
    $code3 = new AccountCode('1.1.02');

    expect($code1->equals($code2))->toBeTrue();
    expect($code1->equals($code3))->toBeFalse();
    expect($code1->equals(null))->toBeFalse();
});

test('serializes to json correctly', function () {
    $accountCode = new AccountCode('1.1.01');

    expect(json_encode($accountCode))->toBe('"1.1.01"');
});

test('instantiates via fromPrimitive factory', function () {
    $fromStr = AccountCode::fromPrimitive('1.1.01');
    expect($fromStr)->toBeInstanceOf(AccountCode::class);
    expect($fromStr->value)->toBe('1.1.01');

    $fromInstance = AccountCode::fromPrimitive($fromStr);
    expect($fromInstance)->toBe($fromStr);
});
