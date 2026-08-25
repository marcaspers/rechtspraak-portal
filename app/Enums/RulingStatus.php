<?php

namespace App\Enums;

enum RulingStatus: string
{
    case Pending = 'pending';
    case FilteredOut = 'filtered_out';
    case TextFetched = 'text_fetched';
    case Summarized = 'summarized';
    case Failed = 'failed';
}
