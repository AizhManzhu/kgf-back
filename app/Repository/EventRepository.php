<?php

namespace App\Repository;

use App\Models\Field;
use App\Models\FieldValues;
use App\Models\FieldVipValues;
use App\Models\Member;
use Exception;
use App\Models\Event;
use App\Models\EventMember;
use App\Models\Competition;
use Illuminate\Http\JsonResponse;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\File;

class EventRepository implements EventRepositoryInterface
{
    use Base;

    protected $model;

    public function __construct(Event $model)
    {
        $this->model = $model;
    }

    public function read(): JsonResponse
    {
        try {
            $events = $this->model->query()->orderByDesc('id')->paginate(20);
            return $this->handleResponse($events);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function create($model): JsonResponse
    {
        try {
            $this->changeIsCurrent($model);
            $model['price'] = 0;
            $event = $this->model->query()->create($model);
            return $this->handleResponse($event);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function readById($id): JsonResponse
    {
        try {
            $event = $this->model->query()
                ->with('speakers')
                ->with(['eventMembers' => function ($query) use ($id) {
                    $query->with(['fieldValues' => function ($subQuery) use ($id) {
                        $subQuery->where('event_id', $id);
                    }])
                    ->with(['memberPromocode' => function($subQuery) use ($id) {
                        $subQuery->where('event_id', $id);
                    }])
                    ->with('fieldVipValues');
                }])
                ->with(['fields' => function ($query) {
                    $query->where('is_base', Field::IS_NOT_BASE);
                }])
                ->with('competitions')
                ->find($id);
            return $this->handleResponse($event);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function getById(int $id)
    {
        return $this->model->query()->find($id);
    }

    public function getFieldsByMemberId($eventId, $memberId)
    {
        $fieldsOfMember = Field::query()
            ->with('buttons')
            ->with(['members' => function($query) use ($memberId, $eventId){
                $query->with(['fieldValues' => function($query) use ($eventId){
                    $query->where('event_id', $eventId);
                }])->where('members.id', $memberId);
            }])
            ->with(['vipMembers' => function($query) use ($memberId, $eventId){
                $query->with('fieldVipValues')->where('members.id', $memberId);
            }])
            ->where('event_id', $eventId)->get();
        return $fieldsOfMember;
    }

    public function update($id, $array): JsonResponse
    {
        try {
            $this->changeIsCurrent($array);
            $this->model->query()->where('id', $id)->update($array);
            return $this->handleResponse(null);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->model->query()->find($id)->delete();
            return $this->handleResponse(null);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function addMember($eventId, $memberId, int $isRegistered = 0, $utm = null): JsonResponse
    {
        try {
            EventMember::query()->updateOrCreate([
                'event_id' => $eventId,
                'member_id' => $memberId,
            ], ['event_id' => $eventId,
                'member_id' => $memberId,
                'is_registered' => $isRegistered,
                'is_activated' => 1,
                'utm' => $utm
            ]);
            return $this->readById($eventId);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function deleteMember($eventId, $memberId): JsonResponse
    {
        try {
            EventMember::query()
                ->where('event_id', $eventId)
                ->where('member_id', $memberId)
                ->delete();
            $member = Member::query()->find($memberId);
            FieldValues::query()
                ->where('event_id', $eventId)
                ->where('telegram_id', $member->telegram_id)->delete();
            return $this->handleResponse($eventId);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }
    public function deleteEventMember(int $eventMemberId): JsonResponse
    {
        try {
            $eventMember = EventMember::query()
                ->find($eventMemberId);
            $member = $eventMember->load('member')->member;
            FieldValues::query()
                ->where('event_id', $eventMember->event_id)
                ->where('telegram_id', $member->telegram_id)->delete();
            FieldVipValues::query()
                ->where('event_id', $eventMember->event_id)
                ->where('email', $member->email)->delete();
            $eventMember->delete();
            return $this->handleResponse($eventMemberId);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function addField($eventId, $fieldId): JsonResponse
    {
        try {
            return $this->readById($eventId);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function attachImage($id, $imageName): JsonResponse
    {
        try {
            $this->model->query()->where('id', $id)->update(['image' => $imageName]);
            $result = $this->model->query()->find($id);
            return $this->handleResponse($result);
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function uploadImage($file)
    {
        try {
            $imageName = $this->uploadImageWithThumbnail($file);
            $this->compressImage(storage_path('app/public/uploads') . '/' . $imageName,
                storage_path('app/public/uploads') . '/' . $imageName, 80);
            return $imageName;
        } catch (Exception $e) {
            return $this->handleError($e->getMessage());
        }
    }

    public function changeIsCurrent($model): void
    {
        if ($model['is_current'] == 1) {
            $this->model->query()->where('is_current', 1)->update(['is_current' => 0]);
        }
    }

    public function uploadImageWithThumbnail($file): string
    {
        $imageName = time() . '-' . $file->getFilename() . "." . $file->getClientOriginalExtension();
        $destinationPath = storage_path('app/public/thumbnail');
        $resizeImage = Image::make($file->getRealPath());
        $resizeImage->fit(200, 200,
            function ($constraint) {
                $constraint->aspectRatio();
            }
        )->save($destinationPath . '/' . $imageName);
        $file->move(storage_path("app/public/uploads"), $imageName);
        return $imageName;
    }

    public function checkExistOrMake(string $path): void
    {
        if (!file_exists(storage_path($path))) {
            File::makeDirectory(storage_path($path), 0777, true);
        }
    }

    private function compressImage($source, $destination, $quality): string
    {
        $imgInfo = getimagesize($source);
        $mime = $imgInfo['mime'];
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                imagejpeg($image, $destination, $quality);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                imagepng($image, $destination);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($source);
                imagegif($image, $destination);
                break;
            default:
                $image = imagecreatefromjpeg($source);
                imagejpeg($image, $destination, $quality);
        }
        return $destination;
    }

    public function getCurrentEvent($select = ['*'], $withRelations = true)
    {
        $query = $this->model->query()->select($select);
        if($withRelations) {
            $query = $query->with('speakers')
                ->with('eventMembers');
        }
        return $query->where('is_current', 1)
        ->first();
    }

    public function getCurrentSpeaker()
    {
        return $this->model->query()->with(['currentSpeaker' => function ($query) {
            $query->where('is_current', '=', 1);
        }])->where('is_current', '=', 1)->first();
    }

    /**
     * @throws Exception
     */
    public function activateEventMembers(int $eventId, array $membersId)
    {
        $eventMembersQuery = EventMember::query()->with('member')
            ->where('event_id', $eventId)
            ->whereIn('member_id', $membersId)
            ->where('is_activated', 0);
        $eventMembers = $eventMembersQuery->get();
        $eventMembersQuery->update([
            'is_activated' => 1,
        ]);
        return $eventMembers;
    }

    public function getCurrentMembers(int $eventId)
    {
        return EventMember::query()->with(['member' => function($query) use ($eventId) {
            $query->with(['fieldVipValues' => function ($subQuery) use ($eventId) {
                    $subQuery->where('event_id', $eventId);
                }]);
        }])->with('promocode')
            ->where('event_id', $eventId)->get();
    }

    public function getEventCompetitions(int $eventId)
    {
        $eventCompetitions = Competition::query()->where('event_id', $eventId)->get();
        return $eventCompetitions;
    }
}
