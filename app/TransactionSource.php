<?php

namespace App;

enum TransactionSource: string
{
    case Manual = 'manual';
    case Ai = 'ai';
}
