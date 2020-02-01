<?php

namespace App\Services;


class HashService
{

    public function create($firstName, $lastName, $birthday)
    {
        $salt = '$2a$07$usesomesillystringforsalt$';
        $birthday = str_replace('-', '', $birthday);
        $firstName = rtrim(substr($firstName, 0, 4));
        $lastName = trim($lastName);

        $str = $birthday . $lastName . $firstName;
        return substr(crypt($str, $salt), strlen($salt) - 1);
    }
}