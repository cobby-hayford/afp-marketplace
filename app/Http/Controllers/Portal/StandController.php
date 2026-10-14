<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\StandRequest;
use App\Models\Country;
use App\Models\Organisation;
use App\Models\Stand;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StandController extends Controller
{
    public function edit(Request $r): Response
    {
        $s = Stand::where('created_by', $r->user()->id)->first();

        return Inertia::render('portal/stand', ['stand' => $s, 'countries' => Country::orderBy('name')->get(['id', 'name']), 'organisations' => Organisation::where(fn ($q) => $q->where('created_by', $r->user()->id)->orWhereHas('members', fn ($x) => $x->where('users.id', $r->user()->id)))->get(['id', 'name', 'approval_status'])]);
    }

    public function save(StandRequest $r): RedirectResponse
    {
        $u = $r->user();
        $s = Stand::firstOrNew(['created_by' => $u->id]);
        $d = $r->safe()->except(['logo', 'cover']);
        if ($d['organisation_id'] ?? null) {
            $org = Organisation::findOrFail($d['organisation_id']);
            abort_unless($org->managedBy($u), 403);
        }$d['profile_id'] = ($d['organisation_id'] ?? null) ? null : $u->profile->id;
        $d['slug'] = $s->slug ?: SlugService::unique(Stand::class, $d['display_name']);
        $linkedOrganisation = ($d['organisation_id'] ?? null) ? Organisation::find($d['organisation_id']) : null;
        $d['is_public'] = $u->profile?->status === 'approved' && (! $linkedOrganisation || $linkedOrganisation->approval_status === 'approved');
        $d['verification_status'] = $linkedOrganisation?->verification_status ?? $u->profile?->verification_status ?? 'self_declared';
        foreach (['logo', 'cover'] as $f) {
            if ($r->hasFile($f)) {
                if ($s->{$f.'_path'}) {
                    Storage::disk('public')->delete($s->{$f.'_path'});
                }$d[$f.'_path'] = $r->file($f)->store('stands', 'public');
            }
        }$s->fill($d)->save();

        return back()->with('success', 'Digital Stand saved.');
    }
}
