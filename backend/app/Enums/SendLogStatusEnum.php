<?php

namespace App\Enums;

enum SendLogStatusEnum: string
{
    case SENT = 'sent';
    case FAILED = 'failed';
}
