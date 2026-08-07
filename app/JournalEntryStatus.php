<?php

namespace App;

enum JournalEntryStatus: string {
    case DRAFT = 'draft';
    case POSTED = 'posted';
    case CANCELED = 'canceled';
}
