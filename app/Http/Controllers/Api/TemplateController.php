<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\TemplateRequest;
use App\Jobs\TelegramMailing;
use App\Models\EventMember;
use App\Models\FieldValues;
use App\Models\FieldVipValues;
use App\Models\Member;
use App\Repository\Base;
use App\Repository\EventRepository;
use App\Repository\TelegramRepository;
use App\Repository\TemplateRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Event;
use Illuminate\Support\Facades\Log;

class TemplateController extends Controller
{
    use Base;
    private $template;

    public function __construct(TemplateRepositoryInterface $template)
    {
        $this->template = $template;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->template->read();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TemplateRequest $request
     * @return JsonResponse
     */
    public function store(TemplateRequest $request): JsonResponse
    {
        return $this->template->create($request->all());
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        return $this->template->readById($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TemplateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(TemplateRequest $request, int $id): JsonResponse
    {
        return $this->template->update($id, $request->all());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        return $this->template->delete($id);
    }


    public function mailing(Request $request)
    {
        if (isset($request->text)) {
            $text = mb_convert_encoding($request->text, 'UTF-8');
        } else {
            $text = null;
        }
        $fieldId = $request->field_id;
        $templateId = $request->template_id;
        $value = $request->button_value;
        $competitionId = $request->competition;
        $imageName = null;
        $members = null;
        $telegramIds = [];
        if ($request->members){
            $members = explode(',',$request->members);
        }
        if($value === "Сompetition"){
            $event = Event::query()
                ->with(['eventMembers' => function($query) use ($members) {
                    if (!empty($members)) {
                        $query->whereIn('telegram_id', $members);
                    }
                    $query->where('is_participated', 1);
                }])
                ->where('is_current', 1)
                ->first();
            $telegramIds =  $event->eventMembers->pluck('telegram_id');
        } elseif($value === "All" || ($value=='null' && $fieldId=='null' && !empty($members))){
            $event = Event::query()
                ->with(['eventMembers' => function($query) use ($members) {
                    if (!empty($members)) {
                        $query->whereIn('telegram_id', $members);
                    }
                }])
                ->where('is_current', 1)
                ->first();
            $telegramIds =  $event->eventMembers->pluck('telegram_id');
        } else {
            if ($fieldId && $value) {
                $result = \App\Models\EventMember::query()
                    ->with('member.fieldVipValues')
                    ->whereHas('member', function ($q) use ($fieldId, $value) {
                        $q->whereNotNull('telegram_id')
                            ->whereHas('fieldVipValues', function ($q) use ($fieldId, $value) {
                                $q->where('field_id', $fieldId)->where('value', $value);
                            });
                    })->get();
                foreach ($result as $fvp) {
                    if ($fvp->member->telegram_id != null) {
                        $telegramIds[$fvp->member->telegram_id] = $fvp->member->telegram_id;
                    }
                }
//                if (!empty($members)) {
//                    $query = $query->whereIn('telegram_id', $members);
//                }
//                $telegramIds = $query->pluck('telegram_id');
            }
        }
        if ($request->hasFile('image')) {
            $imageName = time().'-'.$request->image->getFilename().".".$request->image->getClientOriginalExtension();
            $request->image->move(storage_path("app/public/uploads"), $imageName);
        }
        if (count($telegramIds)>0) {
            foreach ($telegramIds as $telegramId) {
                TelegramMailing::dispatch($telegramId, $text, $imageName, $templateId);
            }
        } else {
            Log::debug("Отправлено: ");
            Log::debug($telegramIds);
        }
    }

    public function mailingToNotEventMembers(Request $request)
    {
        $text = mb_convert_encoding($request->text, 'UTF-8');
        $imageName = null;
        $imIn = true;
        if (isset($request->send_to_all) && $request->send_to_all == true) {
            $imIn = false;
        }
        $eventMembers = EventMember::query()->whereEventId((new EventRepository(new Event()))->getCurrentEvent()->id)->pluck('member_id');
        $telegramIds = Member::query()->whereNotIn('id', $eventMembers)
            ->where('telegram_id', '<>', '0')
            ->where('telegram_id', '<>', '')
            ->orderByDesc('id')
            ->pluck('telegram_id');
        if ($request->hasFile('image')) {
            $imageName = time().'-'.$request->image->getFilename().".".$request->image->getClientOriginalExtension();
            $request->image->move(storage_path("app/public/uploads"), $imageName);
        }
        foreach ($telegramIds as $telegramId) {
            TelegramMailing::dispatch($telegramId, $text, $imageName);
        }
    }

}
