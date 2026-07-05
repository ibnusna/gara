<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken;











class GaraPersonalAccessToken extends PersonalAccessToken
{
    protected $connection = 'mysql_auth';
    protected $table = 'personal_access_tokens';
}
