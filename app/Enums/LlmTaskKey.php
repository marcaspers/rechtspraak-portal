<?php

namespace App\Enums;

enum LlmTaskKey: string
{
    case Classification = 'classification';
    case Summarization = 'summarization';
}
