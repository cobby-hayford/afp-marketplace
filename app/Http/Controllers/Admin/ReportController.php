<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/reports', ['reports' => Report::latest()->paginate(30)]);
    }

    public function update(Request $r, Report $report): RedirectResponse
    {
        $d = $r->validate(['status' => ['required', 'in:open,under_review,resolved,dismissed'], 'admin_decision' => ['nullable', 'string', 'max:5000']]);
        $report->update([...$d, 'decided_by' => in_array($d['status'], ['resolved', 'dismissed']) ? $r->user()->id : null, 'decided_at' => in_array($d['status'], ['resolved', 'dismissed']) ? now() : null]);
        AuditService::record('report.'.$d['status'], $report);

        return back();
    }
}
