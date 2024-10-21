<?php
namespace App\Repository;

interface SpeakerRepositoryInterface extends CrudInterface
{
    public function makeCurrent($id, $eventId);
    public function getCurrentEventSpeakers($eventId);
}
