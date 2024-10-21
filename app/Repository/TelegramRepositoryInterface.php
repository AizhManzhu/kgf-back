<?php
namespace App\Repository;

interface TelegramRepositoryInterface
{
    public function getBaseKeyboard(string $name);
}
