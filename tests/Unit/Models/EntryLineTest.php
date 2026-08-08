<?php

use App\EntryLineDirection;
use App\JournalEntryStatus;
use App\LedgerAccountType;
use App\Models\EntryLine;
use App\Models\JournalEntry;
use App\Models\LedgerAccount;
use App\ValueObjects\Money;

test('casts amount to Money value object when stored and retrieved', function () {
    $entry = JournalEntry::create([
        'date' => '2026-08-07',
        'description' => 'Transferência entre contas',
    ]);

    $account = LedgerAccount::create([
        'code' => '1.1.01',
        'name' => 'Caixa',
        'type' => LedgerAccountType::BALANCE,
        'normal_balance' => EntryLineDirection::DEBIT,
    ]);

    $line = EntryLine::create([
        'journal_entry_id' => $entry->id,
        'ledger_account_id' => $account->id,
        'amount' => Money::fromCents(12500),
        'direction' => EntryLineDirection::DEBIT,
    ]);

    $line->refresh();

    expect($line->amount)->toBeInstanceOf(Money::class);
    expect($line->amount?->cents)->toBe(12500);
    expect($line->amount?->toDecimal())->toBe(125.0);
    expect($line->amount?->toFormattedString())->toContain('125,00');
    expect($line->direction)->toBe(EntryLineDirection::DEBIT);
});

test('handles amount assignment from raw cents and decimal strings', function () {
    $entry = JournalEntry::create([
        'date' => '2026-08-07',
        'description' => 'Ajuste contábil',
    ]);

    $account = LedgerAccount::create([
        'code' => '1.1.02',
        'name' => 'Banco Conta Movimento',
        'type' => LedgerAccountType::BALANCE,
        'normal_balance' => EntryLineDirection::DEBIT,
    ]);

    $lineWithIntCents = EntryLine::create([
        'journal_entry_id' => $entry->id,
        'ledger_account_id' => $account->id,
        'amount' => 5000,
        'direction' => EntryLineDirection::DEBIT,
    ]);

    $lineWithIntCents->refresh();
    expect($lineWithIntCents->amount)->toBeInstanceOf(Money::class);
    expect($lineWithIntCents->amount?->cents)->toBe(5000);

    $lineWithDecimalStr = EntryLine::create([
        'journal_entry_id' => $entry->id,
        'ledger_account_id' => $account->id,
        'amount' => '75.50',
        'direction' => EntryLineDirection::CREDIT,
    ]);

    $lineWithDecimalStr->refresh();
    expect($lineWithDecimalStr->amount)->toBeInstanceOf(Money::class);
    expect($lineWithDecimalStr->amount?->cents)->toBe(7550);
});

test('belongs to journalEntry and ledgerAccount', function () {
    $entry = JournalEntry::create([
        'date' => '2026-08-07',
        'description' => 'Lançamento com relacionamentos',
        'status' => JournalEntryStatus::POSTED,
    ]);

    $account = LedgerAccount::create([
        'code' => '2.1.01',
        'name' => 'Fornecedores',
        'type' => LedgerAccountType::BALANCE,
        'normal_balance' => EntryLineDirection::CREDIT,
    ]);

    $line = EntryLine::create([
        'journal_entry_id' => $entry->id,
        'ledger_account_id' => $account->id,
        'amount' => Money::fromDecimal('500.00'),
        'direction' => EntryLineDirection::CREDIT,
    ]);

    expect($line->journalEntry)->toBeInstanceOf(JournalEntry::class);
    expect($line->journalEntry?->id)->toBe($entry->id);
    expect($line->ledgerAccount)->toBeInstanceOf(LedgerAccount::class);
    expect($line->ledgerAccount?->id)->toBe($account->id);
});

test('cascades delete when journal entry is removed', function () {
    $entry = JournalEntry::create([
        'date' => '2026-08-07',
        'description' => 'Lançamento a ser removido',
    ]);

    $account = LedgerAccount::create([
        'code' => '1.1.01',
        'name' => 'Caixa',
        'type' => LedgerAccountType::BALANCE,
        'normal_balance' => EntryLineDirection::DEBIT,
    ]);

    $line = EntryLine::create([
        'journal_entry_id' => $entry->id,
        'ledger_account_id' => $account->id,
        'amount' => Money::fromCents(1000),
        'direction' => EntryLineDirection::DEBIT,
    ]);

    expect(EntryLine::find($line->id))->not->toBeNull();

    $entry->delete();

    expect(EntryLine::find($line->id))->toBeNull();
});
