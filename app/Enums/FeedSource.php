<?php

namespace App\Enums;

enum FeedSource: string
{
    case Rechtspraak = 'rechtspraak';
    case Curia = 'curia';
    case Echr = 'echr';
}
