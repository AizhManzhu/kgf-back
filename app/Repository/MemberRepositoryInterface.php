<?php
namespace App\Repository;

interface MemberRepositoryInterface extends CrudInterface
{
    public function getByName(string $name);

    public function getByTelegramId(string $telegramId, bool $withBaseFields = false);

    public function isRegistered($telegramId);

    public function getByEmail(string $email, string $lastName);

    public function getByAttributes(array $selects, array $keyValues);
}
