<?php

use App\EntryLineDirection;
use App\JournalEntryStatus;
use App\LedgerAccountType;
use App\Models\EntryLine;
use App\Models\JournalEntry;
use App\Models\LedgerAccount;
use App\ValueObjects\Money;
use Carbon\CarbonInterface;

test('casts date to Carbon date and status to JournalEntryStatus enum', function () {
    $entry = JournalEntry::create([
        'date' => '2026-08-07',
        'description' => 'Pagamento de aluguel',
        'status' => JournalEntryStatus::POSTED,
    ]);

    $entry->refresh();

    expect($entry->date)->toBeInstanceOf(CarbonInterface::class);
    expect($entry->date?->format('Y-m-d'))->toBe('2026-08-07');
    expect($entry->status)->toBe(JournalEntryStatus::POSTED);
});

test('defaults status to DRAFT when not explicitly specified', function () {
    $entry = JournalEntry::create([
        'date' => '2026-08-07',
        'description' => 'Lançamento provisório',
    ]);

    $entry->refresh();

    expect($entry->status)->toBe(JournalEntryStatus::DRAFT);
});

test('has lines relationship with EntryLine', function () {
    $entry = JournalEntry::create([
        'date' => '2026-08-07',
        'description' => 'Venda de mercadorias',
    ]);

    $cashAccount = LedgerAccount::create([
        'code' => '1.1.01',
        'name' => 'Caixa',
        'type' => LedgerAccountType::BALANCE,
        'normal_balance' => EntryLineDirection::DEBIT,
    ]);

    $revenueAccount = LedgerAccount::create([
        'code' => '3.1.01',
        'name' => 'Receita de Vendas',
        'type' => LedgerAccountType::RESULT,
        'normal_balance' => EntryLineDirection::CREDIT,
    ]);

    $debitLine = EntryLine::create([
        'journal_entry_id' => $entry->id,
        'ledger_account_id' => $cashAccount->id,
        'amount' => Money::fromDecimal('150.00'),
        'direction' => EntryLineDirection::DEBIT,
    ]);

    $creditLine = EntryLine::create([
        'journal_entry_id' => $entry->id,
        'ledger_account_id' => $revenueAccount->id,
        'amount' => Money::fromDecimal('150.00'),
        'direction' => EntryLineDirection::CREDIT,
    ]);

    expect($entry->lines)->toHaveCount(2);
    expect($entry->lines->pluck('id')->all())->toBe([$debitLine->id, $creditLine->id]);
});
