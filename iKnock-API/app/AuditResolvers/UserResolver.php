<?php

namespace App\AuditResolvers;

use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;
use Illuminate\Support\Facades\Request;
use App\Models\User;

class UserResolver implements Resolver
{
    public static function resolve(Auditable $auditable)
    {
        $user = User::find(Request::get('user_id'));

        return $user ?? null;
    }
}
