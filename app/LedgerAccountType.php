<?php

namespace App;

enum LedgerAccountType: string
{
    case RESULT = 'RESULT';
    case BALANCE = 'BALANCE';
    case OPEN = 'OPEN';
}
