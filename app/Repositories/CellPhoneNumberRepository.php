<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\CellPhoneNumber;

class CellPhoneNumberRepository
{
	public function create(Request $request)
	{
		$errors = CellPhoneNumber::validate($request, [			
			'user_id' => 'required|exists:users,id',
			'number' => 'required|digits:9',
			'telephony_company' => 'string'
		]);

		if(!is_null($errors))
		{
			throw new \Exception($errors, 1);
		}
		
		$activeCellphoneNumber = CellPhoneNumber::where('user_id', $request->user_id)->where('active', true)->first();

		//si el nuevo numero es el actual activo
		if(!is_null($activeCellphoneNumber) && $activeCellphoneNumber->number == $request->number)
		{
			throw new \Exception("The user is already using the given cellphone", 2);			
		}

		//si el nuevo numero ya fue registrado y por consiguiente no activo
		$existentCellPhoneNumber = CellPhoneNumber::where('user_id', $request->user_id)->where('number', $request->number)->first();

		if(!is_null($existentCellPhoneNumber))
		{
			$existentCellPhoneNumber->active = true;
			
			if($request->has('telephony_company'))
			{
				$existentCellPhoneNumber->telephony_company = $request->telephony_company;
			}

			$existentCellPhoneNumber->save();

			if(!is_null($activeCellphoneNumber))
			{
				$activeCellphoneNumber->active = false;
				$activeCellphoneNumber->save();
			}

			return $existentCellPhoneNumber;
		}
		else  
		{
			//crear nuevo
			$cellPhoneNumber = new CellPhoneNumber();
			$cellPhoneNumber->number = $request->number;
			$cellPhoneNumber->user_id = $request->user_id;
			
			if($request->has('telephony_company'))
			{
				$cellPhoneNumber->telephony_company = $request->telephony_company;
			}

			$cellPhoneNumber->save();

			if(!is_null($activeCellphoneNumber))
			{
				$activeCellphoneNumber->active = false;
				$activeCellphoneNumber->save();
			}

			return $cellPhoneNumber;
		}
	}

}