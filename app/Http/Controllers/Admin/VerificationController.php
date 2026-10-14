<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FestivalRecognition;
use App\Models\Organisation;
use App\Models\Profile;
use App\Models\VerificationRecord;
use App\Notifications\MarketplaceNotification;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationController extends Controller
{
    public function index(): Response
    {
        $festivals = FestivalRecognition::with([
            'project:id,title,slug,owner_id',
            'project.owner:id,first_name,last_name',
            'country:id,name',
        ])->whereIn('verification_status', ['self_declared', 'pending_verification'])
            ->latest()->limit(100)->get()->map(fn (FestivalRecognition $festival) => [
                'id' => $festival->id,
                'festival_name' => $festival->festival_name,
                'country' => $festival->country?->only(['id', 'name']),
                'year' => $festival->year,
                'selection_type' => $festival->selection_type,
                'award_category' => $festival->award_category,
                'evidence_url' => $festival->evidence_url,
                'evidence_download_url' => $festival->evidence_path ? route('admin.verifications.festival-evidence', $festival) : null,
                'verification_status' => $festival->verification_status,
                'project' => $festival->project ? [
                    'id' => $festival->project->id,
                    'title' => $festival->project->title,
                    'slug' => $festival->project->slug,
                    'owner' => $festival->project->owner?->only(['id', 'first_name', 'last_name']),
                ] : null,
            ]);

        return Inertia::render('admin/verifications', [
            'profiles' => Profile::with(['user:id,first_name,last_name', 'country:id,name'])
                ->whereIn('verification_status', ['self_declared', 'pending_verification'])->latest()->limit(100)->get(),
            'organisations' => Organisation::with('country:id,name')
                ->whereIn('verification_status', ['self_declared', 'pending_verification'])->latest()->limit(100)->get(),
            'festivals' => $festivals,
        ]);
    }

    public function festivalEvidence(FestivalRecognition $festival): StreamedResponse
    {
        abort_unless($festival->evidence_path, 404);
        abort_unless(Storage::disk('private')->exists($festival->evidence_path), 404);

        return Storage::disk('private')->download(
            $festival->evidence_path,
            'festival-evidence-'.$festival->id.'.'.pathinfo($festival->evidence_path, PATHINFO_EXTENSION)
        );
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:profile,organisation,festival'],
            'id' => ['required', 'integer'],
            'status' => ['required', 'in:pending_verification,verified,rejected'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ]);
        $model = match ($data['type']) {
            'profile' => Profile::class,
            'organisation' => Organisation::class,
            'festival' => FestivalRecognition::class,
        };
        $record = $model::findOrFail($data['id']);
        $record->forceFill(['verification_status' => $data['status']])->save();
        if ($record instanceof Profile || $record instanceof Organisation) {
            $record->stands()->update(['verification_status' => $data['status']]);
        }

        VerificationRecord::create([
            'verifiable_type' => $record->getMorphClass(),
            'verifiable_id' => $record->id,
            'type' => $data['type'] === 'profile' ? 'association' : ($data['type'] === 'organisation' ? 'company' : 'festival'),
            'status' => $data['status'],
            'reviewed_by' => $request->user()->id,
            'notes' => $data['notes'] ?? null,
            'reviewed_at' => now(),
        ]);
        AuditService::record('verification.'.$data['status'], $record, ['type' => $data['type']]);

        if ($data['status'] === 'verified') {
            $owner = match ($data['type']) {
                'profile' => $record->user,
                'organisation' => $record->creator,
                'festival' => $record->project->owner,
            };
            $owner?->notify(new MarketplaceNotification(
                'Verification granted',
                ucfirst($data['type']).' verification was approved.',
                $data['type'] === 'festival' ? '/my-projects/'.$record->project->slug.'/edit' : ($data['type'] === 'profile' ? '/profile' : '/organisations'),
                'success'
            ));
        }

        return back()->with('success', 'Verification status updated.');
    }
}
