<?php
namespace App\Repository;

use App\Models\Field;

interface FieldRepositoryInterface extends CrudInterface
{
    public function getValues(string $telegramId, EventRepositoryInterface $eventRepository, int $isBase = Field::IS_BASE);
    public function createMember($telegramId);
    public function getBaseFields();
    public function saveBaseFields(array $array): void;
}
