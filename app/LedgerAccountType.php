<?php

namespace App;

enum LedgerAccountType: string {
    case ASSET = 'ASSET';         // Ativo (Bancos, Clientes a Receber, Estoque)
    case LIABILITY = 'LIABILITY'; // Passivo (Fornecedores a Pagar, Impostos a Pagar)
    case EQUITY = 'EQUITY';       // Patrimônio Líquido
    case REVENUE = 'REVENUE';     // Receitas (Vendas de Serviços/Produtos)
    case EXPENSE = 'EXPENSE';     // Despesas (Aluguel, Custos, Tarifas)
}
