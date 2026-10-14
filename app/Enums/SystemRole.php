<?php

namespace App\Enums;

enum SystemRole: string
{
    case Member = 'member';
    case Moderator = 'moderator';
    case Administrator = 'administrator';
    case SuperAdministrator = 'super_administrator';
}
