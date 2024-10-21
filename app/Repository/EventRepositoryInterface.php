<?php
namespace App\Repository;

use Illuminate\Http\JsonResponse;

interface EventRepositoryInterface extends CrudInterface
{
    public function addMember($eventId, $memberId, int $isRegistered = 0, $utm = null): JsonResponse;

    public function deleteMember($eventId, $memberId): JsonResponse;

    public function deleteEventMember(int $eventMemberId): JsonResponse;

    public function addField($eventId, $fieldId): JsonResponse;

    public function getCurrentEvent($select = ['*'], $withRelations = true);

    public function changeIsCurrent($model): void;

    public function getCurrentSpeaker();

    public function uploadImage($file);

    public function activateEventMembers(int $eventId, array $membersId);

    public function getCurrentMembers(int $eventId);

    public function getFieldsByMemberId(int $eventId, int $memberId);

    public function getEventCompetitions(int $eventId);

    public function getById(int $id);
}
