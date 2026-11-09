<?php

namespace App\Enums;

enum TokenNameEnum: string
{
    case REGISTER = 'register';
    case LOGIN = 'login';
    case REFRESH = 'refresh';
    case SOCIAL = 'social_login';
}
