<?php

namespace App\Enums;

enum VerificationStatus: string
{
    case SelfDeclared = 'self_declared';
    case Pending = 'pending_verification';
    case Verified = 'verified';
    case Rejected = 'rejected';
}
