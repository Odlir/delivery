<?php

namespace App\Repositories;

use App\Models\NotificationToken;
use Illuminate\Http\Request;

class NotificationTokenRepository
{
    public function register(Request $request)
    {
        $errors = NotificationToken::validate($request, [
            'token' => 'required|string',
            'owner' => 'in:role_user,restaurant',
            'role_id' => 'required_if:owner,role_user|exists:roles,id',
            'user_id' => 'required_if:owner,role_user|exists:users,id',
            'restaurant_id' => 'required_if:owner,restaurant|exists:restaurants,id'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        if($request->owner == 'role_user')
        {
            $notificationToken = NotificationToken::where('role_user_role_id', $request->role_id)
                ->where('role_user_user_id', $request->user_id)
                ->first();

            if($notificationToken == null)
            {
                $notificationToken = new NotificationToken();
                $notificationToken->role_user_role_id = $request->role_id;
                $notificationToken->role_user_user_id = $request->user_id;
            }
        }
        else{
            $notificationToken = NotificationToken::where('restaurant_id', $request->restaurant_id)->first();

            if($notificationToken == null)
            {
                $notificationToken = new NotificationToken();
                $notificationToken->restaurant_id = $request->restaurant_id;
            }
        }
        
        $notificationToken->token = $request->token;

        $notificationToken->save();

        return $notificationToken;
    }

    public function test()
    {
        return FCM::test();
    }
}