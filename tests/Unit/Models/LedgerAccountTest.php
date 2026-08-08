<?php

use App\EntryLineDirection;
use App\LedgerAccountType;
use App\Models\EntryLine;
use App\Models\JournalEntry;
use App\Models\LedgerAccount;
use App\ValueObjects\AccountCode;
use App\ValueObjects\Money;

test('casts code to AccountCode value object when stored and retrieved', function () {
    $account = LedgerAccount::create([
        'code' => '1.1.01',
        'name' => 'Caixa Geral',
        'type' => LedgerAccountType::BALANCE,
        'normal_balance' => EntryLineDirection::DEBIT,
    ]);

    $account->refresh();

    expect($account->code)->toBeInstanceOf(AccountCode::class);
    expect($account->code?->value)->toBe('1.1.01');
    expect($account->code?->level())->toBe(3);
    expect($account->code?->parentCode()?->value)->toBe('1.1');
});

test('allows setting code as AccountCode instance directly', function () {
    $codeVO = new AccountCode('2.1.01');
    $account = LedgerAccount::create([
        'code' => $codeVO,
        'name' => 'Fornecedores a Pagar',
        'type' => LedgerAccountType::BALANCE,
        'normal_balance' => EntryLineDirection::CREDIT,
    ]);

    $account->refresh();

    expect($account->code)->toBeInstanceOf(AccountCode::class);
    expect($account->code?->equals($codeVO))->toBeTrue();
});

test('supports null code on account', function () {
    $account = LedgerAccount::create([
        'code' => null,
        'name' => 'Conta Sem Código',
        'type' => LedgerAccountType::RESULT,
        'normal_balance' => EntryLineDirection::CREDIT,
    ]);

    $account->refresh();

    expect($account->code)->toBeNull();
    expect($account->full_name)->toBe('Conta Sem Código');
});

test('casts type and normal_balance to their respective enums', function () {
    $account = LedgerAccount::create([
        'code' => '3.1.01',
        'name' => 'Receita de Vendas',
        'type' => LedgerAccountType::RESULT,
        'normal_balance' => EntryLineDirection::CREDIT,
    ]);

    $account->refresh();

    expect($account->type)->toBe(LedgerAccountType::RESULT);
    expect($account->normal_balance)->toBe(EntryLineDirection::CREDIT);
});

test('formats full_name accessor correctly', function () {
    $accountWithCode = new LedgerAccount([
        'code' => '1.1.01',
        'name' => 'Banco do Brasil',
    ]);

    expect($accountWithCode->full_name)->toBe('1.1.01 - Banco do Brasil');

    $accountWithoutCode = new LedgerAccount([
        'code' => null,
        'name' => 'Conta Genérica',
    ]);

    expect($accountWithoutCode->full_name)->toBe('Conta Genérica');
});

test('has lines relationship with EntryLine', function () {
    $account = LedgerAccount::create([
        'code' => '1.1.01',
        'name' => 'Caixa',
        'type' => LedgerAccountType::BALANCE,
        'normal_balance' => EntryLineDirection::DEBIT,
    ]);

    $journal = JournalEntry::create([
        'date' => '2026-08-01',
        'description' => 'Abertura de caixa',
    ]);

    $line = EntryLine::create([
        'journal_entry_id' => $journal->id,
        'ledger_account_id' => $account->id,
        'amount' => Money::fromCents(50000),
        'direction' => EntryLineDirection::DEBIT,
    ]);

    expect($account->lines)->toHaveCount(1);
    expect($account->lines->first()?->id)->toBe($line->id);
    expect($account->lines->first()?->amount)->toBeInstanceOf(Money::class);
    expect($account->lines->first()?->amount?->cents)->toBe(50000);
});
