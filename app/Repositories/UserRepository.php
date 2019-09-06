<?php

namespace App\Repositories;

use App\Models\ClientRegisterApplication;
use App\Models\Restaurant;
use \Illuminate\Http\Request;
use App\User;
use App\Models\CellPhoneNumber;
use App\Models\DeviceIdentifier;
use App\Models\TelephonyCompany;
use App\Models\DeliveryMan;
use App\Models\Role;
use App\Models\Address;
use App\Models\RoleUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Parser;
use Faker\Factory as Faker;
use App\Events\PasswordResetedEvent;

class UserRepository
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
            'name' => 'required|string',
            'surname' => 'string',
            'dni' => 'digits:8',
            'role_id' => 'required|exists:roles,id',
            'landline_phone' => 'digits:7',
            'client_register_application_id' => 'integer|exists:client_register_applications,id',
            'cellphone_number' => 'digits:9',
            'telephony_company' => 'string',
            'created_from_facebook' => 'boolean'
        ]);

        if($validator->fails())
            throw new \Exception( json_encode($validator->errors()),1);

        $user = User::where('email', $request->email)->first();

        if($user != null)//usuario existe
        {
            if(RoleUser::where('user_id', $user->id)->where('role_id', $request->role_id)->first() != null)
                throw new \Exception('The user is already associated to the given role', 2);
        }
        else//nuevo
        {
            $user = new User();

            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->name = $request->name;
            $user->surname = $request->surname;

            if($request->has('dni'))
            {
                if(User::where('dni', $request->dni)->first() != null)
                    throw new \Exception('DNI in use', 3);

                $user->dni = $request->dni;
            }            

            if($request->has('landline_phone'))
               $user->landline_phone = $request->landline_phone;

           if(!$request->has('created_from_facebook'))
                $request->created_from_facebook = false;

            $user->created_from_facebook = $request->created_from_facebook;

            $user->save();
        }

        if($request->has('cellphone_number') &&
            CellPhoneNumber::where('user_id', $user->id)->where('number', $request->cellphone_number)->first() == null)
        {
            $cellphoneNumber = new  CellPhoneNumber();
            $cellphoneNumber->number = $request->cellphone_number;
            $cellphoneNumber->user_id = $user->id;

            if($request->has('telephony_company'))
            {
                $cellphoneNumber->telephony_company = $request->telephony_company;
            }

            $cellphoneNumber->save();

            $user->cellphone_number = $cellphoneNumber;
        }

        $role = Role::find($request->role_id);

        $roleUser = new RoleUser();
        $roleUser->role_id = $role->id;
        $roleUser->user_id = $user->id;
        $roleUser->save();

        $user->role = $role;

        if($role->id == 3)//client
        {
            if($request->has('client_register_application_id'))
            {
                $clientApplication = ClientRegisterApplication::find($request->client_register_application_id);
                $clientApplication->user_id = $user->id;
                $clientApplication->save();

                $clientApplication->delete();
            }
        }
        elseif($role->id == 4) //delivery_man
        {
            $deliveryManRequest = Request::create('api/delivery_man', 'POST', [
                'user_id' => $user->id
            ]);

            $deliveryManRepository = new DeliveryManRepository();
            $user->delivery_man = $deliveryManRepository->create($deliveryManRequest);
        }

        return $user;
    }

    public function get(Request $request)
    {
        if(!is_null($request->id))
        {
            $user = User::find($request->id);
            $user->cellphone = CellPhoneNumber::where('user_id', $user->id)->orderBy('id', 'desc')->first();
            
            return $user;
        }

        if($request->has('role_id')){
            return User::join('roles_users', 'roles_users.user_id', 'users.id')
            ->where('roles_users.role_id', $request->role_id)
            ->with('cellphones')
            ->get();
        }

        return User::all();
    }

    public function edit(Request $request)
    { 
        $request['id'] = $request->id;

        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'target' => 'required|in:full_name,password,email,dni',
            'name' => 'required_if:target,full_name',
            'surname' => 'required_if:target,full_name',
            'email' => 'required_if:target,email|email',
            'password' => 'required_if:target,password',
            'new_password' => 'required_if:target,password',
            'dni' => 'required_if:target,dni'
        ]);

        if($validator->fails())
            throw new \Exception(json_encode($validator->errors()), 1);

        $user = User::find($request->id);
        //return $user;
        if($request->has('device_identifier'))
        {
            if(DeviceIdentifier::where('user_id', $user->id)
                    ->where('identifier', $request->identifier)->first() == null)
            {
                $deviceIdentifier = new DeviceIdentifier();
                $deviceIdentifier->identifier = $request->device_identifier;
                $deviceIdentifier->user_id = $user->id;
                $deviceIdentifier->save();

                $user->device_identifier = $deviceIdentifier;
            }
        }

        if($request->target == 'full_name')
        {
            $user->name = $request->name;
            $user->surname = $request->surname;
        }
        elseif($request->target == 'email')
        {
            if(count(User::where('email', $request->email)->get()) > 0)
            {                
                throw new \Exception('Email in use', 2);
            }

            $user->email = $request->email;
        }
        elseif($request->target == 'password')
        {
            if(!Hash::check($request->password, $user->password))
            {
                throw new \Exception('Incorrect password', 3);                
            }
            if(Hash::check($request->new_password, $user->password))
            {
                throw new \Exception('The new and current passwords are the same.', 4);
            }

            $user->password = Hash::make($request->new_password);
        }
        elseif($request->target == 'dni')
        {
            if(User::where('dni', $request->dni)->first() != null)
                throw new \Exception('DNI in use', 5);

            $user->dni = $request->dni;
        }

        $user->save();

        return $user;
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email'
        ]);

        if($validator->fails())
            throw new \Exception(json_encode($validator->errors()), 1);

        $user = User::where('email', $request->email)->first();

        $faker = Faker::create();

        $password = strtolower($faker->swiftBicNumber);

        $user->password = Hash::make($password);
        $user->save();

        $recentUser = new User();
        $recentUser->password = $password;
        $recentUser->email = $user->email;

        ///event(new PasswordResetedEvent($recentUser));

        return [$recentUser->email, $recentUser->password ];
    }

    public function delete(Request $request)
    {
        $request['user_id'] = $request->id;

        $validator = Validator::make($request->all(), [
            'admin_id' => 'required|exists:roles_users,user_id,role_id,1',
            'user_id' => 'required|exists:users,id'
        ]);

        if($validator->fails())
            throw new \Exception(json_encode($validator->errors()), 1);

        $user = User::find($request->id);

        $user->active = false;
        $user->save();
        $user->delete();

        return $user;            
    }

    public function getLoginData(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'origin' => 'required|in:app,web',
            'role_id' => 'required_if:origin,app|exists:roles_users,role_id,user_id,'.$user->id,
        ]);

        if($validator->fails())
            throw new \Exception(json_encode($validator->errors()), 1);
        
        if($request->origin == 'app')
        {
            $cellphone = CellPhoneNumber::where('user_id', $user->id)->where('active', true)->first();

            switch ($request->role_id) 
            {
                case 2: //todo

                    $user->cellphone = is_null($cellphone)? null : $cellphone->number;
                    break;
                case 3: //todo
                    $user->restaurant = Restaurant::where('client_id', $user->id)->first();
                    break;
                case 4:
                    $user = DeliveryMan::where('user_id', $user->id)->with('user')->first();
                    $user->address = Address::where('delivery_man_id', $user->id)->first();
                    $user->user->cellphone = is_null($cellphone)? null : $cellphone->number;
                    break;
            }
        }
        else
        {
            $rolesUser = RoleUser::where('user_id', $user->id)->get();

            $roles = [];

            foreach ($rolesUser as $roleUser) 
            {
                $roles[] = $roleUser->role_id;

                if($roleUser->role_id === 3)
                {
                    $user->restaurant = Restaurant::where('client_id', $user->id)->first();
                }
            }
            
            $user->roles = $roles;
        }
        
        return $user;
    }

    public function closeSession(Request $request)
    {
        $value = $request->bearerToken();
        
        $id = (new Parser())->parse($value)->getHeader('jti');
        $token= $request->user()->tokens->find($id);
        $token->revoke();
        $token->success = true;
        return $token;
    }

    public function verifyFacebookLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'facebook_token' => 'required|string',
            'email' => 'required|exists:users,email',
        ]);

        if($validator->fails())
            throw new \Exception(json_encode($validator->errors()), 1);

        $user = User::where('email', $request->email)->first();

        if(!$user->created_from_facebook)
            throw new \Exception('The user was not created with facebook', 2);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://graph.facebook.com/debug_token?input_token='.$request->facebook_token.'&access_token=1129110887209064|f6rrbrMWfY6cfHisdTUu4XjSqxM');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true );
        
        $result = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($result);

        if(property_exists($data, 'error'))
            throw new \Exception('The token is invalid', 3);

        
        $accessToken = $user->createToken('delivery')->accessToken;

        return response()->json(['access_token' => $accessToken]);
    }
}

/*
{
  "data": {
    "app_id": "1129110887209064",
    "application": "com.jefe.a_tu_casa_consumer",
    "expires_at": 1489359989,
    "is_valid": true,
    "issued_at": 1484175989,
    "metadata": {
      "auth_type": "rerequest"
    },
    "scopes": [
      "email",
      "public_profile"
    ],
    "user_id": "115503572286162"
  }
}

{
  "data": {
    "error": {
      "code": 190,
      "message": "Malformed access token AAQC651YGGgBALn91aZBdmCNyduKALH0SwxCXaowrd2wmWqXmMrkyd3A03dsn6VZBNRjjniQXCZCdYdcDgr6fN0PumPYriLNWwEDasodr9Kchp7R65ZB2ATPbM8F9lMNxVa4NfBQF4AkZBis3PXvarUFSomxTQppDltWC6nGUZBpTyi9QQqUUCNGjWnhi4lIrfd8ZBABqGKTAZDZD"
    },
    "is_valid": false,
    "scopes": []
  }
}
*/