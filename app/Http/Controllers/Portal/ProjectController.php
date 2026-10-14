<?php

namespace App\Http\Controllers\Portal;

use App\Actions\SubmitProject;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectRequest;
use App\Models\ContentType;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Language;
use App\Models\Organisation;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectStage;
use App\Models\ProjectTag;
use App\Models\Stand;
use App\Models\User;
use App\Notifications\MarketplaceNotification;
use App\Services\SlugService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('portal/projects/index', [
            'projects' => Project::managedBy($request->user())->with(['contentType', 'country', 'stage'])->latest()->paginate(15),
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('create', Project::class), 403);

        return Inertia::render('portal/projects/form', ['project' => null, 'options' => $this->options($request)]);
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('create', Project::class), 403);
        $data = $this->prepare($request);
        $data['owner_id'] = $request->user()->id;
        $data['slug'] = SlugService::unique(Project::class, $data['title']);
        $project = Project::create($data);
        $this->sync($request, $project);

        return redirect()->route('my-projects.edit', $project)->with('success', 'Project draft created.');
    }

    public function edit(Request $request, Project $project): Response
    {
        $this->authorizeUpdate($request, $project);
        $project->load([
            'genres:id,name', 'originalLanguages:id,name', 'subtitleLanguages:id,name', 'tags:id,name', 'coProductionCountries:id,name',
            'documents', 'rights', 'media', 'teamMembers.profile.user:id,first_name,last_name', 'festivalRecognitions.country:id,name',
        ]);
        $project->media->each(fn ($media) => $media->setAttribute('public_url', $media->path ? Storage::disk('public')->url($media->path) : $media->url));
        // Never serialize private storage keys into Inertia props. The portal only receives
        // document metadata plus the authorized download endpoint.
        $project->setRelation('documents', $project->documents->map(fn ($document) => [
            'id' => $document->id,
            'title' => $document->title,
            'type' => $document->type,
            'mime_type' => $document->mime_type,
            'size' => $document->size,
            'access_level' => $document->access_level,
            'download_url' => route('project-documents.download', $document),
        ]));
        $project->setRelation('festivalRecognitions', $project->festivalRecognitions->map(fn ($festival) => [
            'id' => $festival->id,
            'festival_name' => $festival->festival_name,
            'festival_country_id' => $festival->festival_country_id,
            'country' => $festival->country?->name,
            'year' => $festival->year,
            'selection_type' => $festival->selection_type,
            'award_category' => $festival->award_category,
            'evidence_url' => $festival->evidence_url,
            'verification_status' => $festival->verification_status,
        ]));

        return Inertia::render('portal/projects/form', [
            'project' => $project,
            'options' => $this->options($request),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        $this->authorizeUpdate($request, $project);
        $wasPublished = $project->publication_status === 'published';
        $project->update($this->prepare($request));
        $this->sync($request, $project);

        // Safe MVP workflow: material changes to a published listing remove it from public
        // discovery and send it back through moderation before republication.
        if ($wasPublished) {
            $project->forceFill(['publication_status' => 'under_review', 'submitted_at' => now()])->save();
            User::whereIn('role', ['administrator', 'super_administrator'])->each(
                fn ($admin) => $admin->notify(new MarketplaceNotification(
                    'Published project revised',
                    $project->title.' was edited and requires re-approval.',
                    '/admin/projects?status=under_review'
                ))
            );
        }

        return back()->with('success', $wasPublished ? 'Project saved and returned to moderation.' : 'Project saved.');
    }

    public function submit(Request $request, Project $project, SubmitProject $action): RedirectResponse
    {
        abort_unless($request->user()->can('submit', $project), 403);
        $request->validate(['copyright_declaration' => ['accepted']]);
        $action->handle($project, $request->user(), $request->ip());

        return back()->with('success', 'Project submitted for moderation.');
    }

    public function destroy(Request $request, Project $project): RedirectResponse
    {
        abort_unless($request->user()->can('delete', $project), 403);
        abort_unless(in_array($project->publication_status, ['draft', 'changes_requested', 'rejected', 'archived'], true), 422);
        $project->delete();

        return redirect()->route('my-projects.index')->with('success', 'Project removed. It remains recoverable in the database until permanent retention cleanup is explicitly performed.');
    }

    private function prepare(ProjectRequest $request): array
    {
        $data = $request->safe()->except([
            'poster', 'cover', 'documents', 'document_access', 'genres', 'languages', 'subtitle_languages', 'tags', 'co_production_countries',
        ]);

        if ($data['organisation_id'] ?? null) {
            $organisation = Organisation::findOrFail($data['organisation_id']);
            abort_unless($organisation->managedBy($request->user()), 403);
        }
        if ($data['stand_id'] ?? null) {
            $stand = Stand::findOrFail($data['stand_id']);
            abort_unless($stand->created_by === $request->user()->id || ($stand->organisation && $stand->organisation->managedBy($request->user())), 403);
        }

        foreach (['poster', 'cover'] as $field) {
            if ($request->hasFile($field)) {
                $old = $request->route('project')?->{$field.'_path'};
                if ($old) {
                    Storage::disk('public')->delete($old);
                }
                $data[$field.'_path'] = $request->file($field)->store('projects/'.$field.'s', 'public');
            }
        }

        return $data;
    }

    private function sync(ProjectRequest $request, Project $project): void
    {
        $project->genres()->sync($request->input('genres', []));
        DB::table('language_project')->where('project_id', $project->id)->delete();
        $languageRows = collect($request->input('languages', []))->map(fn ($id) => ['project_id' => $project->id, 'language_id' => $id, 'kind' => 'original'])
            ->merge(collect($request->input('subtitle_languages', []))->map(fn ($id) => ['project_id' => $project->id, 'language_id' => $id, 'kind' => 'subtitle']))
            ->unique(fn ($row) => $row['language_id'].'|'.$row['kind'])->values()->all();
        if ($languageRows !== []) {
            DB::table('language_project')->insert($languageRows);
        }
        $project->tags()->sync($request->input('tags', []));
        $project->coProductionCountries()->sync(collect($request->input('co_production_countries', []))->mapWithKeys(fn ($id) => [$id => ['relationship' => 'coproduction']])->all());

        foreach ($request->file('documents', []) as $file) {
            $path = $file->store('project-documents/'.$project->id, 'private');
            ProjectDocument::create([
                'project_id' => $project->id,
                'type' => 'other',
                'title' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
                'access_level' => $request->input('document_access', 'private'),
            ]);
        }
    }

    private function options(Request $request): array
    {
        $user = $request->user();

        return [
            'countries' => Country::orderBy('name')->get(['id', 'name']),
            'contentTypes' => ContentType::where('active', true)->orderBy('name')->get(['id', 'name']),
            'genres' => Genre::orderBy('name')->get(['id', 'name']),
            'languages' => Language::orderBy('name')->get(['id', 'name']),
            'stages' => ProjectStage::orderBy('sort_order')->get(['id', 'name']),
            'tags' => ProjectTag::where('active', true)->orderBy('name')->get(['id', 'name']),
            'organisations' => Organisation::where(fn ($q) => $q->where('created_by', $user->id)->orWhereHas('members', fn ($x) => $x->where('users.id', $user->id)))->get(['id', 'name']),
            'stands' => Stand::where(fn ($q) => $q->where('created_by', $user->id)->orWhereHas('organisation.members', fn ($x) => $x->where('users.id', $user->id)))->get(['id', 'display_name']),
            'profiles' => Profile::public()->with('user:id,first_name,last_name')->orderBy('professional_name')->limit(500)->get(['id', 'user_id', 'professional_name']),
        ];
    }

    private function authorizeUpdate(Request $request, Project $project): void
    {
        abort_unless($request->user()->can('update', $project), 403);
    }
}
