<?php

use App\Http\Controllers\Api\AnswerController;
use App\Http\Controllers\Api\EventMemberController;
use App\Http\Controllers\Api\FieldController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PromocodeController;
use App\Http\Controllers\Api\SpeakerController;
use App\Http\Controllers\Api\TelegramHistory;
use App\Http\Controllers\Api\v1\AbilitiesController;
use App\Http\Controllers\Api\v1\EventAuthController;
use App\Http\Controllers\Api\v1\MailingController;
use App\Http\Controllers\Api\v1\PermissionController;
use App\Http\Controllers\Api\v1\RoleController;
use App\Http\Controllers\Api\v1\TransactionController;
use App\Jobs\MailingJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BaseKeyboardController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\v1\EventController as v1EventController;
use App\Http\Controllers\Api\ButtonController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\TelegramController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\InlineButtonController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VipMemberController;
use App\Http\Controllers\Api\CompetitionController;
use App\Http\Controllers\Api\TaskController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register']);
Route::get('/outsystems/get-program', [EventController::class, 'getProgram']);
Route::get('/outsystems/download-program', [EventController::class, 'downloadProgram']);
Route::group(['middleware' => ['auth:sanctum', 'cors']], function () {
    Route::get('/test/revoke-permission', [RoleController::class, 'revokeRole']);
    Route::post('mailing-to-not-event-members', [TemplateController::class, 'mailingToNotEventMembers']);
    Route::post('mailing', [TemplateController::class, 'mailing']);
    Route::prefix('events')->group(function() {
        Route::controller(EventController::class)->group(function() {
            Route::post('{eventId}/activate-member', 'activateMember');
            Route::post('{eventId}/add-member/{memberId}', 'addMember');
            Route::post('{eventId}/delete-member/{memberId}', 'deleteMember');
            Route::post('{eventId}/add-field/{fieldId}', 'addField');
            Route::post('upload/{id}', 'uploadProgram');
            Route::get('member/{memberId}', 'getFieldsByMemberId');
            Route::post('member/{memberId}/editMemberFieldValues', 'editMemberFieldValues');
        });
        Route::controller(ExportController::class)->group(function() {
            Route::get('export/eventMembers/{eventId}', 'exportEventMembers');
            Route::get('export/competitionMembers/{eventId}', 'exportCompetitionMembers');
            Route::get('export/logCompetition/{eventId}', 'exportLogCompetition');
            Route::get('export/exportResultOfCompetition/{eventId}', 'exportResultOfCompetition');
        });
    });
    Route::prefix('v1')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::controller(v1EventController::class)->group(function () {
            Route::prefix('events')->group(function () {
                Route::get('/{id}', 'show');
                Route::get('members/{id}', 'eventMembers');
                Route::post('delete-member/{event_member_id}', 'deleteEventMember');
                Route::post('get-program/{id}', 'getEventProgram');
                Route::post('get-speakers/{id}', 'getEventSpeakers');
            });
        });

        Route::post('mailing', [MailingController::class, 'messageSending']);
        Route::post('event/import-member/{id}', [v1EventController::class, 'importMember']);
        Route::get('transaction', [TransactionController::class, 'index']);
        Route::get('transaction/{transaction}', [TransactionController::class, 'show']);
        Route::get('abilities', [AbilitiesController::class, 'index']);

        Route::put('permission/{roleId}/{permission}', [PermissionController::class, 'set']);
        Route::delete('permission/{roleId}/{permission}', [PermissionController::class, 'remove']);

        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class);
    });

    Route::post('fields/add-button', [FieldController::class, 'addButton']);
    Route::post('fields/delete-button', [FieldController::class, 'deleteButton']);
    Route::post('fields/filter', [FieldController::class, 'getCurrentEventFilterList']);
    Route::post('speakers/make-current', [SpeakerController::class, 'makeCurrent']);
    Route::post('base-fields', [FieldController::class, 'getBaseFields']);
    Route::put('base-fields', [FieldController::class, 'saveBaseFields']);
    Route::get('base-keyboards', [BaseKeyboardController::class, 'getBaseKeyboards']);
    Route::post('base-keyboards', [BaseKeyboardController::class, 'saveBaseKeyboards']);
    Route::get('event-members', [EventMemberController::class, 'index']);
    Route::get('event-telegram-members', [EventMemberController::class, 'eventTelegramMembers']);
    Route::get('event-competitions', [EventController::class, 'getEventCompetitions']);
    Route::get('members/export/members', [ExportController::class, 'exportMembers']);
    Route::get('members/get-events/{id}', [MemberController::class, 'getMemberEvents']);
    Route::get('export/report', [ExportController::class, 'exportReport']);
    Route::get('export/promocode', [ExportController::class, 'exportPromocode']);
    Route::get('histories', [TelegramHistory::class, 'index']);
    Route::apiResource('events', EventController::class);
    Route::apiResource('members', MemberController::class);
    Route::apiResource('templates', TemplateController::class);
    Route::apiResource('inlinebuttons', InlineButtonController::class);
    Route::apiResource('buttons', ButtonController::class);
    Route::apiResource('speakers', SpeakerController::class);
    Route::apiResource('fields', FieldController::class);
    Route::apiResource('users', UserController::class);
    Route::apiResource('competitions', CompetitionController::class);
    Route::apiResource('tasks', TaskController::class);
    Route::apiResource('promocodes', PromocodeController::class);
});

Route::get('get-fields', [FieldController::class, 'getFields']);
Route::get('member', [VipMemberController::class, 'getByEmail']);
Route::get('get-member-by-email-and-phone', [VipMemberController::class, 'getByEmailAndPhone'])->middleware('token');
Route::post('vip-members/save', [VipMemberController::class, 'saveVipMembers']);
Route::post('vip-members/save/pay', [VipMemberController::class, 'saveVipMembersWithPayment']);
Route::post('registrate', [VipMemberController::class, 'registrate']);
Route::post('get-program', [TelegramController::class, 'getProgram']);
Route::post('get-speaker', [TelegramController::class, 'getSpeaker']);
Route::post('get-member', [TelegramController::class, 'getMember']);
Route::post('save-callback', [TelegramController::class, 'callbackAction']);
Route::post('message', [TelegramController::class, 'message']);
Route::middleware('telegram.auth')->post('start', [TelegramController::class, 'start']);
Route::get('command', [Controller::class, 'command']);
Route::get('payment/{app}/pay', [PaymentController::class, 'pay']);
Route::get('payment/{app}/fail', [PaymentController::class, 'fail']);
Route::post('payment/{app}/success', [PaymentController::class, 'success']);
Route::post('send/mail', function (Request $request) {
    MailingJob::dispatch($request->get('email'), $request->get('message'), $request->get('fullname'));
});

Route::get('find/member/{eventMemberId}/{token}', [MemberController::class, 'findByEventMemberId']);
Route::post('set/member/{eventMemberId}/{token}', [MemberController::class, 'setMemberCame']);

Route::get('getFirstRoundWinners', [AnswerController::class, 'getFirstRoundWinners']);
Route::get('getSecondRoundWinners', [AnswerController::class, 'getSecondRoundWinners']);
Route::get('getMembersOfAnswer', [AnswerController::class, 'index']);
Route::get('deleteMembersOfAnswer', [AnswerController::class, 'destroyAll']);
Route::group(['middleware' => ['auth:sanctum', 'cors', 'role:manager']], function () {
    Route::get('/search/member', [EventAuthController::class, 'check']);
    Route::get('/set/member/here', [EventAuthController::class, 'set']);
    Route::get('/qr/generate', [EventAuthController::class, 'generateQR']);
});

Route::get('/setYoutubeWinner/{name}', [AnswerController::class, 'setYoutubeWinner']);
Route::get('/getYoutubeWinners', [AnswerController::class, 'getYoutubeWinners']);


Route::controller(\App\Http\Controllers\Api\v1\PaymentController::class)->group(function () {
    Route::prefix('v1')->group(function () {
        Route::get('{app}/pay', 'pay');
        Route::get('{app}/fail', 'fail');
        Route::post('{app}/success', 'success');
    });
});
Route::get('/v1/event/{eventMember}/pay', [v1EventController::class, 'generatePayment']);
Route::get('/v1/send/mail', [MailingController::class, 'mailing'])->middleware('mailing');
Route::post('/v1/message', [\App\Http\Controllers\Api\v1\TelegramController::class, 'message']);
Route::get('/v1/event-options/{id}', [EventController::class, 'getOptions'])->middleware('outsystems');
