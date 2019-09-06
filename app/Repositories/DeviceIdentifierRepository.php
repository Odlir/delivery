<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\DeviceIdentifier;

class DeviceIdentifierRepository
{
	public function create(Request $request)
	{
		$errors = DeviceIdentifier::validate($request, [
			'identifier' => 'required|string',
			'user_id' => 'required|exists:users,id'
		]);

		if(!is_null($errors))
			throw new \Exception(json_encode($errors), 1);

		$existentIdentifier = DeviceIdentifier::where('user_id', $request->user_id)
		->where('identifier', $request->identifier)->first();

		if(!is_null($existentIdentifier))
			return $existentIdentifier;

		$deviceIdentifier = new DeviceIdentifier();
		$deviceIdentifier->user_id = $request->user_id;
		$deviceIdentifier->identifier = $request->identifier;
		$deviceIdentifier->save();

		return $deviceIdentifier;			
	}
}