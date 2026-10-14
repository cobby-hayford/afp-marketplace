<?php

namespace App\Enums;

enum ViewRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Declined = 'declined';
    case Revoked = 'revoked';
    case Expired = 'expired';
}
