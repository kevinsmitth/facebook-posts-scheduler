<?php

namespace App\Enums;

enum PostStatusEnum: string
{
    case SENT = 'sent';
    case SCHEDULED = 'scheduled';
    case FAILED = 'failed';
}
