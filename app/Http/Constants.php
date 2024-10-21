<?php

namespace App\Http;

class Constants {
    const PAGINATE = 15;

    const OFFLINE = "Оффлайн";
    const ONLINE = "Онлайн";

    const REGISTRATION_MESSAGE_ONLINE = "Благодарим Вас за заявку на регистрацию на Kazakhstan Marketing Conference 2023 в онлайн формате! <br>  Вы получите информацию о подтверждении заявки на указанный электронный адрес и в чат-боте ближе к дате мероприятия. <br>
А пока вы можете пригласить своих коллег, просто перешлите им ссылку: <a href='https://clck.ru/33C5wN'>https://clck.ru/33C5wN</a>";
    const REGISTRATION_MESSAGE_OFFLINE = "Благодарим Вас за заявку на регистрацию на Kazakhstan HR-Forum 2022 в офлайн формате. <br>  Вы получите информацию о подтверждении заявки на указанный электронный адрес и в этом чат-боте ближе к дате мероприятия. <br>
А пока вы можете пригласить своих коллег, просто перешлите им ссылку: <a href='https://bit.ly/3fg5yHs'>https://bit.ly/3fg5yHs</a>";

    public static function getAcceptOffline($name, $isMail = true): string {
        $break = $isMail ? "<br>" : "\n";
        $link = $isMail ? "<a href ='https://go.2gis.com/w5vbj'>Intercontinental Almaty</a>" : "Intercontinental Almaty https://go.2gis.com/w5vbj";
        return "Здравствуйте, $name! $break
С благодарностью подтверждаем Ваше участие в Kazakhstan Marketing Conference 2023!  Мероприятие состоится 26 января 2023 года в отеле $link . Регистрация открыта с 08:00! Начало в 9:00.";
    }

    public static function getAcceptOnline($name, $isMail = true): string {
        $break = $isMail ? "<br>" : "\n";
        return "Здравствуйте, $name! $break
С благодарностью подтверждаем Ваше участие в Kazakhstan Marketing Conference 2023! Мероприятие состоится 26 января 2023 года года в онлайн формате. Начало в 09:00! Ссылку на участие в конференции вышлем накануне мероприятия. $break
А пока вы можете пригласить своих коллег, просто перешлите им ссылку: ";
    }

    public static function getMessageAfterChangeFormat($name, $isMail = true): string {
        $break = $isMail ? "<br>" : "\n";
        return "Здравствуйте, '.$name! $break $break К сожалению, мы вынуждены сообщить, что Ваша заявка на участие в формате оффлайн была переведена в формат онлайн, так как количество мест для участия вживую ограничено. $break $break С благодарностью подтверждаем Ваше участие в KMC, который состоится 26 января 2023 года в онлайн формате. Начало в 09:00! Ссылки на участие в конференции: $break $break";
    }
}
