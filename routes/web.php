<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth;
use App\Http\Controllers\Portal;
use App\Http\Controllers\Public as Site;
use Illuminate\Support\Facades\Route;

Route::get('/', Site\HomeController::class)->name('home');
Route::get('/trade-hall', Site\TradeHallController::class)->name('trade-hall');
Route::get('/projects', [Site\ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{slug}', [Site\ProjectController::class, 'resolve'])->name('projects.show');
Route::get('/producers', [Site\ProducerController::class, 'index'])->name('producers.index');
Route::get('/producers/{slug}', [Site\ProducerController::class, 'resolve'])->name('producers.show');
Route::get('/stands', [Site\StandController::class, 'index'])->name('stands.index');
Route::get('/stands/{stand:slug}', [Site\StandController::class, 'show'])->name('stands.show');
Route::get('/organisations/{organisation:slug}', [Site\OrganisationController::class, 'show'])->name('organisations.show');
Route::get('/buying-requests', [Site\BuyingRequestController::class, 'index'])->name('buying-requests.index');
Route::get('/buying-requests/{buyingRequest:slug}', [Site\BuyingRequestController::class, 'show'])->where('buyingRequest', '^(?!create$).+')->name('buying-requests.show');
Route::get('/collaboration', [Site\CollaborationController::class, 'index'])->name('collaboration.index');
Route::get('/collaboration/{collaboration:slug}', [Site\CollaborationController::class, 'show'])->where('collaboration', '^(?!create$).+')->name('collaboration.show');
Route::get('/opportunities', [Site\OpportunityController::class, 'index'])->name('opportunities.index');
Route::get('/opportunities/{opportunity:slug}', [Site\OpportunityController::class, 'show'])->name('opportunities.show');
Route::get('/news', [Site\NewsController::class, 'index'])->name('news.index');
Route::get('/news/{news:slug}', [Site\NewsController::class, 'show'])->name('news.show');
Route::get('/search', Site\SearchController::class)->name('search');
Route::get('/contact', [Site\ContactController::class, 'create'])->name('contact');
Route::post('/contact', [Site\ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
Route::get('/sitemap.xml', Site\SitemapController::class)->name('sitemap');
Route::get('/project-documents/{document}/download', [Portal\ProjectDocumentController::class, 'download'])->name('project-documents.download');
foreach (['about', 'terms', 'privacy', 'marketplace-guidelines', 'copyright-policy', 'verification-policy'] as $page) {
    Route::get('/'.$page, [Site\PageController::class, 'show'])->defaults('slug', $page)->name('page.'.$page);
}

Route::middleware('guest')->group(function () {
    Route::get('/register', [Auth\RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [Auth\RegisteredUserController::class, 'store'])->middleware('throttle:registration');
    Route::get('/login', [Auth\AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [Auth\AuthenticatedSessionController::class, 'store'])->middleware('throttle:auth');
    Route::get('/forgot-password', [Auth\PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/forgot-password', [Auth\PasswordResetController::class, 'email'])->middleware('throttle:auth')->name('password.email');
    Route::get('/reset-password/{token}', [Auth\PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [Auth\PasswordResetController::class, 'reset'])->name('password.store');
});

Route::middleware(['auth', 'not-suspended'])->group(function () {
    Route::post('/logout', [Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/verify-email', [Auth\EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', [Auth\EmailVerificationController::class, 'verify'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('/email/verification-notification', [Auth\EmailVerificationController::class, 'send'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('/confirm-password', [Auth\ConfirmPasswordController::class, 'show'])->name('password.confirm');
    Route::post('/confirm-password', [Auth\ConfirmPasswordController::class, 'store']);

    Route::get('/profile', [Portal\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [Portal\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/submit', [Portal\ProfileController::class, 'submit'])->middleware('verified')->name('profile.submit');
    Route::post('/profile/documents', [Portal\ProfileDocumentController::class, 'store'])->middleware('verified')->name('profile-documents.store');
    Route::get('/profile-documents/{document}/download', [Portal\ProfileDocumentController::class, 'download'])->name('profile-documents.download');
    Route::delete('/profile-documents/{document}', [Portal\ProfileDocumentController::class, 'destroy'])->name('profile-documents.destroy');

    Route::get('/notifications', [Portal\NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [Portal\NotificationController::class, 'read'])->name('notifications.read');
    Route::patch('/notifications/read-all', [Portal\NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/saved', [Portal\FavouriteController::class, 'index'])->name('saved.index');
    Route::post('/saved/toggle', [Portal\FavouriteController::class, 'toggle'])->name('saved.toggle');
    Route::post('/reports', [Portal\ReportController::class, 'store'])->middleware('throttle:reports')->name('reports.store');

});

Route::middleware(['auth', 'verified', 'approved', 'not-suspended'])->group(function () {
    Route::get('/dashboard', Portal\DashboardController::class)->name('dashboard');
    Route::get('/settings', [Portal\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [Portal\SettingController::class, 'update'])->name('settings.update');
    Route::put('/settings/password', [Portal\SettingController::class, 'password'])->name('settings.password');
    Route::get('/organisations', [Portal\OrganisationController::class, 'index'])->name('organisations.index');
    Route::post('/organisations', [Portal\OrganisationController::class, 'store'])->name('organisations.store');
    Route::put('/organisations/{organisation}', [Portal\OrganisationController::class, 'update'])->name('organisations.update');
    Route::post('/organisations/{organisation}/submit', [Portal\OrganisationController::class, 'submit'])->name('organisations.submit');
    Route::post('/organisations/{organisation}/members', [Portal\OrganisationMemberController::class, 'store'])->name('organisations.members.store');
    Route::delete('/organisations/{organisation}/members/{user}', [Portal\OrganisationMemberController::class, 'destroy'])->name('organisations.members.destroy');
    Route::get('/my-stand', [Portal\StandController::class, 'edit'])->name('my-stand.edit');
    Route::post('/my-stand', [Portal\StandController::class, 'save'])->name('my-stand.save');

    Route::get('/my-projects', [Portal\ProjectController::class, 'index'])->name('my-projects.index');
    Route::get('/my-projects/create', [Portal\ProjectController::class, 'create'])->name('my-projects.create');
    Route::post('/my-projects', [Portal\ProjectController::class, 'store'])->name('my-projects.store');
    Route::get('/my-projects/{project:slug}/edit', [Portal\ProjectController::class, 'edit'])->name('my-projects.edit');
    Route::put('/my-projects/{project:slug}', [Portal\ProjectController::class, 'update'])->name('my-projects.update');
    Route::post('/my-projects/{project:slug}/submit', [Portal\ProjectController::class, 'submit'])->name('my-projects.submit');
    Route::delete('/my-projects/{project:slug}', [Portal\ProjectController::class, 'destroy'])->name('my-projects.destroy');
    Route::post('/my-projects/{project:slug}/team', [Portal\ProjectTeamMemberController::class, 'store'])->name('my-projects.team.store');
    Route::delete('/my-projects/{project:slug}/team/{teamMember}', [Portal\ProjectTeamMemberController::class, 'destroy'])->name('my-projects.team.destroy');
    Route::post('/my-projects/{project:slug}/media', [Portal\ProjectMediaController::class, 'store'])->name('my-projects.media.store');
    Route::post('/my-projects/{project:slug}/rights', [Portal\ProjectRightController::class, 'store'])->name('my-projects.rights.store');
    Route::delete('/my-projects/{project:slug}/rights/{right}', [Portal\ProjectRightController::class, 'destroy'])->name('my-projects.rights.destroy');
    Route::delete('/my-projects/{project:slug}/media/{media}', [Portal\ProjectMediaController::class, 'destroy'])->name('my-projects.media.destroy');
    Route::post('/my-projects/{project:slug}/festivals', [Portal\FestivalRecognitionController::class, 'store'])->name('my-projects.festivals.store');
    Route::patch('/my-projects/{project:slug}/documents/{document}', [Portal\ProjectDocumentController::class, 'update'])->name('my-projects.documents.update');
    Route::delete('/my-projects/{project:slug}/documents/{document}', [Portal\ProjectDocumentController::class, 'destroy'])->name('my-projects.documents.destroy');
    Route::delete('/my-projects/{project:slug}/festivals/{festival}', [Portal\FestivalRecognitionController::class, 'destroy'])->name('my-projects.festivals.destroy');

    Route::get('/my-buying-requests', [Portal\BuyingRequestController::class, 'index'])->name('my-buying-requests.index');
    Route::get('/buying-requests/create', [Portal\BuyingRequestController::class, 'create'])->name('buying-requests.create');
    Route::get('/my-buying-requests/{buyingRequest:slug}/edit', [Portal\BuyingRequestController::class, 'edit'])->name('buying-requests.edit');
    Route::post('/buying-requests', [Portal\BuyingRequestController::class, 'store'])->name('buying-requests.store');
    Route::put('/my-buying-requests/{buyingRequest:slug}', [Portal\BuyingRequestController::class, 'update'])->name('buying-requests.update');
    Route::post('/buying-requests/{buyingRequest:slug}/submit', [Portal\BuyingRequestController::class, 'submit'])->name('buying-requests.submit');
    Route::post('/buying-requests/{buyingRequest:slug}/responses', [Portal\BuyingRequestResponseController::class, 'store'])->middleware('throttle:responses')->name('buying-requests.responses.store');

    Route::get('/my-collaboration', [Portal\CollaborationController::class, 'index'])->name('my-collaboration.index');
    Route::get('/collaboration/create', [Portal\CollaborationController::class, 'create'])->name('collaboration.create');
    Route::get('/my-collaboration/{collaboration:slug}/edit', [Portal\CollaborationController::class, 'edit'])->name('collaboration.edit');
    Route::post('/collaboration', [Portal\CollaborationController::class, 'store'])->name('collaboration.store');
    Route::put('/my-collaboration/{collaboration:slug}', [Portal\CollaborationController::class, 'update'])->name('collaboration.update');
    Route::post('/collaboration/{collaboration:slug}/submit', [Portal\CollaborationController::class, 'submit'])->name('collaboration.submit');
    Route::post('/collaboration/{collaboration:slug}/responses', [Portal\CollaborationResponseController::class, 'store'])->middleware('throttle:responses')->name('collaboration.responses.store');

    Route::get('/view-requests', [Portal\ViewRequestController::class, 'index'])->name('view-requests.index');
    Route::post('/projects/{project:slug}/view-requests', [Portal\ViewRequestController::class, 'store'])->middleware('throttle:view-requests')->name('view-requests.store');
    Route::post('/view-requests/{viewRequest}/approve', [Portal\ViewRequestController::class, 'approve'])->name('view-requests.approve');
    Route::post('/view-requests/{viewRequest}/decline', [Portal\ViewRequestController::class, 'decline'])->name('view-requests.decline');
    Route::post('/view-requests/{viewRequest}/revoke', [Portal\ViewRequestController::class, 'revoke'])->name('view-requests.revoke');

    Route::get('/messages', [Portal\ConversationController::class, 'index'])->name('messages.index');
    Route::get('/messages/{conversation}', [Portal\ConversationController::class, 'show'])->name('messages.show');
    Route::post('/messages/{conversation}', [Portal\ConversationController::class, 'store'])->middleware('throttle:messages')->name('messages.store');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'not-suspended', 'admin'])->group(function () {
    Route::get('/', Admin\DashboardController::class)->name('dashboard');
    Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [Admin\UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/notes', [Admin\AdminNoteController::class, 'store'])->name('users.notes.store');
    Route::patch('/users/{user}/role', [Admin\UserController::class, 'role'])->name('users.role');
    Route::patch('/users/{user}/restore', [Admin\UserController::class, 'restore'])->name('users.restore');
    Route::patch('/users/{user}/suspend', [Admin\MemberModerationController::class, 'suspend'])->name('users.suspend');
    Route::get('/memberships', [Admin\MemberModerationController::class, 'index'])->name('memberships.index');
    Route::post('/memberships/{profile}/approve', [Admin\MemberModerationController::class, 'approve'])->name('memberships.approve');
    Route::post('/memberships/{profile}/transition', [Admin\MemberModerationController::class, 'transition'])->name('memberships.transition');
    Route::get('/projects', [Admin\ProjectModerationController::class, 'index'])->name('projects.index');
    Route::get('/projects/{project}', [Admin\ProjectModerationController::class, 'show'])->name('projects.show');
    Route::post('/projects/{project}/approve', [Admin\ProjectModerationController::class, 'approve'])->name('projects.approve');
    Route::post('/projects/{project}/transition', [Admin\ProjectModerationController::class, 'transition'])->name('projects.transition');
    Route::patch('/projects/{project}/feature', [Admin\ProjectModerationController::class, 'feature'])->name('projects.feature');
    Route::patch('/projects/{project}/unpublish', [Admin\ProjectModerationController::class, 'unpublish'])->name('projects.unpublish');
    Route::get('/organisations', [Admin\OrganisationModerationController::class, 'index'])->name('organisations.index');
    Route::post('/organisations/{organisation}/status', [Admin\OrganisationModerationController::class, 'update'])->name('organisations.update');
    Route::get('/buying-requests', [Admin\BuyingRequestModerationController::class, 'index'])->name('buying-requests.index');
    Route::post('/buying-requests/{buyingRequest}/status', [Admin\BuyingRequestModerationController::class, 'update'])->name('buying-requests.update');
    Route::get('/collaboration', [Admin\CollaborationModerationController::class, 'index'])->name('collaboration.index');
    Route::post('/collaboration/{collaboration}/status', [Admin\CollaborationModerationController::class, 'update'])->name('collaboration.update');
    Route::get('/opportunities', [Admin\OpportunityController::class, 'index'])->name('opportunities.index');
    Route::post('/opportunities', [Admin\OpportunityController::class, 'store'])->name('opportunities.store');
    Route::put('/opportunities/{opportunity}', [Admin\OpportunityController::class, 'update'])->name('opportunities.update');
    Route::get('/news', [Admin\NewsController::class, 'index'])->name('news.index');
    Route::get('/news/{news}/preview', [Admin\NewsController::class, 'preview'])->name('news.preview');
    Route::post('/news', [Admin\NewsController::class, 'store'])->name('news.store');
    Route::put('/news/{news}', [Admin\NewsController::class, 'update'])->name('news.update');
    Route::get('/reports', [Admin\ReportController::class, 'index'])->name('reports.index');
    Route::patch('/reports/{report}', [Admin\ReportController::class, 'update'])->name('reports.update');
    Route::get('/audit-logs', [Admin\AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/verifications', [Admin\VerificationController::class, 'index'])->name('verifications.index');
    Route::get('/verifications/festivals/{festival}/evidence', [Admin\VerificationController::class, 'festivalEvidence'])->name('verifications.festival-evidence');
    Route::patch('/verifications', [Admin\VerificationController::class, 'update'])->name('verifications.update');
    Route::get('/taxonomies', [Admin\TaxonomyController::class, 'index'])->name('taxonomies.index');
    Route::post('/taxonomies', [Admin\TaxonomyController::class, 'store'])->name('taxonomies.store');
    Route::patch('/taxonomies/{taxonomy}/{id}', [Admin\TaxonomyController::class, 'update'])->name('taxonomies.update');
    Route::get('/pages', [Admin\PageController::class, 'index'])->name('pages.index');
    Route::put('/pages/{page}', [Admin\PageController::class, 'update'])->name('pages.update');
    Route::get('/settings', [Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
});
