<?php

namespace Tnt\Dbi\Enums;

enum TimestampFormat: string
{
    case UNIX = 'unix';
    case DATETIME = 'datetime';
}
