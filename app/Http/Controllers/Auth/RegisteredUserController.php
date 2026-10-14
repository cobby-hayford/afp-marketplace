<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Profile;
use App\Models\User;
use App\Services\SlugService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('auth/register', ['countries' => Country::orderByDesc('is_african')->orderBy('name')->get(['id', 'name']), 'accountTypes' => ['producer', 'production_company', 'distributor', 'broadcaster', 'streaming_platform', 'music_company', 'investor', 'financier', 'sales_agent', 'industry_professional', 'other']]);
    }

    public function store(Request $r): RedirectResponse
    {
        $data = $r->validate(['first_name' => ['required', 'string', 'max:100'], 'last_name' => ['required', 'string', 'max:100'], 'email' => ['required', 'email', 'max:255', 'unique:users,email'], 'password' => ['required', 'confirmed', Password::min(10)->letters()->mixedCase()->numbers()], 'country_id' => ['required', 'exists:countries,id'], 'account_type' => ['required', Rule::in(['producer', 'production_company', 'distributor', 'broadcaster', 'streaming_platform', 'music_company', 'investor', 'financier', 'sales_agent', 'industry_professional', 'other'])], 'terms' => ['accepted'], 'privacy' => ['accepted'], 'guidelines' => ['accepted']]);
        unset($data['terms'],$data['privacy'],$data['guidelines']);
        $user = User::create($data);
        $user->forceFill(['terms_accepted_at' => now(), 'privacy_accepted_at' => now(), 'guidelines_accepted_at' => now()])->save();
        $user->profile()->create([
            'slug' => SlugService::unique(Profile::class, $user->name),
            'country_id' => $user->country_id,
            'status' => 'draft',
        ]);
        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
