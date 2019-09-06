<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 26/08/2016
 * Time: 12:48 PM
 */

namespace App\Repositories;
use App\Models\ClientRegisterApplication;
use App\Models\RoleUser;
use App\User;
use Illuminate\Http\Request;

class ClientRegisterApplicationRepository
{
    public function create(Request $request)
    {
        $errors = ClientRegisterApplication::validate($request, [
            'name' => 'required|string',
            'surname' => 'required|string',
            'email' => 'required|email',
            'dni' => 'required|digits:8',
            'cellphone_number' => 'digits:9',
            'landline_phone' => 'digits:7',
            'message' => 'string'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $clientRegister = new ClientRegisterApplication();
        $clientRegister->name = $request->name;
        $clientRegister->surname = $request->surname;
        $clientRegister->dni = $request->dni;
        $clientRegister->email = $request->email;

        if($request->has('cellphone_number'))
            $clientRegister->cellphone_number = $request->cellphone_number;

        if($request->has('landline_phone'))
            $clientRegister->landline_phone = $request->landline_phone;

        if($request->has('message'))
            $clientRegister->message = $request->message;

        $clientRegister->save();

        return $clientRegister;
    }

    public function get(Request $request)
    {
        $errors = RoleUser::validate($request, [
            'user_id' => 'required|exists:roles_users,user_id,role_id,1'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        return  ClientRegisterApplication::all();
    }

    public function delete(Request $request)
    {
        $errors = RoleUser::validate($request, [
            'user_id' => 'required|exists:roles_users,user_id,role_id,1'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $clientRegister = ClientRegisterApplication::find($request->id);

        if($clientRegister == null)
            throw new \Exception('Non existent client_register_application', 2);

        $clientRegister->delete();

        return  $clientRegister;
    }

}