<?php

namespace App\Enums;

enum FeedFetchStatus: string
{
    case Success = 'success';
    case Partial = 'partial';
    case Failed = 'failed';
}
