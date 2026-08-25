<?php

namespace App\Enums;

enum LlmProvider: string
{
    case Anthropic = 'anthropic';
    case OpenAi = 'openai';
    case Ollama = 'ollama';
}
