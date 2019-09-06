<?php 

namespace App\Repositories;

use App\Models\TermsConditions;

class TermsConditionsRepository
{
	public function get()
	{
		return TermsConditions::find(1);
	}
}