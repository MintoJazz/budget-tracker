<?php

use App\EntryLineDirection;
use App\JournalEntryStatus;
use App\LedgerAccountType;
use App\Models\EntryLine;
use App\Models\JournalEntry;
use App\Models\LedgerAccount;
use App\ValueObjects\AccountCode;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\DB;

test('records a balanced double entry transaction with multiple debits and credits', function () {
    $caixa = LedgerAccount::create([
        'code' => new AccountCode('1.1.01'),
        'name' => 'Caixa Geral',
        'type' => LedgerAccountType::BALANCE,
        'normal_balance' => EntryLineDirection::DEBIT,
    ]);

    $banco = LedgerAccount::create([
        'code' => new AccountCode('1.1.02'),
        'name' => 'Banco Itaú',
        'type' => LedgerAccountType::BALANCE,
        'normal_balance' => EntryLineDirection::DEBIT,
    ]);

    $receita = LedgerAccount::create([
        'code' => new AccountCode('3.1.01'),
        'name' => 'Receita de Serviços',
        'type' => LedgerAccountType::RESULT,
        'normal_balance' => EntryLineDirection::CREDIT,
    ]);

    // Transação: Prestação de serviços recebendo R$ 400 em dinheiro e R$ 600 em conta bancária
    $entry = DB::transaction(function () use ($caixa, $banco, $receita) {
        $journal = JournalEntry::create([
            'date' => '2026-08-07',
            'description' => 'Recebimento de contrato de consultoria',
            'status' => JournalEntryStatus::POSTED,
        ]);

        EntryLine::create([
            'journal_entry_id' => $journal->id,
            'ledger_account_id' => $caixa->id,
            'amount' => Money::fromDecimal('400.00'),
            'direction' => EntryLineDirection::DEBIT,
        ]);

        EntryLine::create([
            'journal_entry_id' => $journal->id,
            'ledger_account_id' => $banco->id,
            'amount' => Money::fromDecimal('600.00'),
            'direction' => EntryLineDirection::DEBIT,
        ]);

        EntryLine::create([
            'journal_entry_id' => $journal->id,
            'ledger_account_id' => $receita->id,
            'amount' => Money::fromDecimal('1000.00'),
            'direction' => EntryLineDirection::CREDIT,
        ]);

        return $journal;
    });

    $entry->load('lines');

    $totalDebits = $entry->lines
        ->where('direction', EntryLineDirection::DEBIT)
        ->reduce(fn (Money $carry, EntryLine $line) => $carry->add($line->amount), Money::zero());

    $totalCredits = $entry->lines
        ->where('direction', EntryLineDirection::CREDIT)
        ->reduce(fn (Money $carry, EntryLine $line) => $carry->add($line->amount), Money::zero());

    expect($totalDebits->cents)->toBe(100000);
    expect($totalCredits->cents)->toBe(100000);
    expect($totalDebits->equals($totalCredits))->toBeTrue();
});

test('calculates accurate ledger account balances through Money VO arithmetic', function () {
    $banco = LedgerAccount::create([
        'code' => '1.1.02',
        'name' => 'Banco Conta Movimento',
        'type' => LedgerAccountType::BALANCE,
        'normal_balance' => EntryLineDirection::DEBIT,
    ]);

    $aluguel = LedgerAccount::create([
        'code' => '4.1.01',
        'name' => 'Despesas de Aluguel',
        'type' => LedgerAccountType::RESULT,
        'normal_balance' => EntryLineDirection::DEBIT,
    ]);

    // Entrada inicial: R$ 5.000,00 no Banco
    $entry1 = JournalEntry::create([
        'date' => '2026-08-01',
        'description' => 'Aporte inicial',
        'status' => JournalEntryStatus::POSTED,
    ]);

    EntryLine::create([
        'journal_entry_id' => $entry1->id,
        'ledger_account_id' => $banco->id,
        'amount' => Money::fromDecimal('5000.00'),
        'direction' => EntryLineDirection::DEBIT,
    ]);

    // Pagamento de aluguel: R$ 1.200,00
    $entry2 = JournalEntry::create([
        'date' => '2026-08-05',
        'description' => 'Pagamento de Aluguel',
        'status' => JournalEntryStatus::POSTED,
    ]);

    EntryLine::create([
        'journal_entry_id' => $entry2->id,
        'ledger_account_id' => $aluguel->id,
        'amount' => Money::fromDecimal('1200.00'),
        'direction' => EntryLineDirection::DEBIT,
    ]);

    EntryLine::create([
        'journal_entry_id' => $entry2->id,
        'ledger_account_id' => $banco->id,
        'amount' => Money::fromDecimal('1200.00'),
        'direction' => EntryLineDirection::CREDIT,
    ]);

    // Cálculo do saldo do banco: Débitos - Créditos
    $banco->load('lines');
    $bancoDebits = $banco->lines
        ->where('direction', EntryLineDirection::DEBIT)
        ->reduce(fn (Money $carry, EntryLine $line) => $carry->add($line->amount), Money::zero());

    $bancoCredits = $banco->lines
        ->where('direction', EntryLineDirection::CREDIT)
        ->reduce(fn (Money $carry, EntryLine $line) => $carry->add($line->amount), Money::zero());

    $saldoBanco = $bancoDebits->subtract($bancoCredits);

    expect($saldoBanco->cents)->toBe(380000); // R$ 3.800,00
    expect($saldoBanco->toDecimal())->toBe(3800.0);
    expect($saldoBanco->toFormattedString())->toContain('3.800,00');
});

test('rolls back atomic journal transactions on failure', function () {
    $caixa = LedgerAccount::create([
        'code' => '1.1.01',
        'name' => 'Caixa',
        'type' => LedgerAccountType::BALANCE,
        'normal_balance' => EntryLineDirection::DEBIT,
    ]);

    try {
        DB::transaction(function () use ($caixa) {
            $journal = JournalEntry::create([
                'date' => '2026-08-07',
                'description' => 'Transação que falhará',
                'status' => JournalEntryStatus::DRAFT,
            ]);

            EntryLine::create([
                'journal_entry_id' => $journal->id,
                'ledger_account_id' => $caixa->id,
                'amount' => Money::fromCents(5000),
                'direction' => EntryLineDirection::DEBIT,
            ]);

            throw new RuntimeException('Falha simulada no processamento contábil');
        });
    } catch (RuntimeException) {
        // Exceção esperada
    }

    expect(JournalEntry::where('description', 'Transação que falhará')->exists())->toBeFalse();
    expect(EntryLine::count())->toBe(0);
});
