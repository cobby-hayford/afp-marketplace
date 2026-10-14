<?php

namespace App\Enums;

enum AccountStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case PendingReview = 'pending_review';
    case ChangesRequested = 'changes_requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
}
