<?php

namespace App\Enums;

enum PublicationStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case UnderReview = 'under_review';
    case ChangesRequested = 'changes_requested';
    case Published = 'published';
    case Rejected = 'rejected';
    case Archived = 'archived';
    case Suspended = 'suspended';
}
