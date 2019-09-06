<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\FavoriteRestaurant;
use Illuminate\Support\Facades\DB;
use App\Models\RestaurantRatingSummary;

class ConsumerRepository
{
	public function addFavoriteRestaurant(Request $request)
	{
		$request['user_id'] = $request->id;
		$errors = FavoriteRestaurant::validate($request, [
			'user_id' => 'required|exists:roles_users,user_id,role_id,2',
			'restaurant_id' => 'required|exists:restaurants,id'
		]);

		if($errors != null){
			throw new \Exception(json_encode($errors), 1);
		}

		$favorite = new FavoriteRestaurant();
		$favorite->user_id = $request->user_id;
		$favorite->restaurant_id = $request->restaurant_id;

		$favorite->save();

		return $favorite;
	}

	public function deleteFavoriteRestaurant(Request $request)
	{
		$favorite = FavoriteRestaurant::where('user_id', $request->user_id)
			->where('restaurant_id', $request->restaurant_id)->first();

		if($favorite == null){
			throw new \Exception('Non existent favorite_restaurant', 1);
		}
		
		DB::delete('delete from favorite_restaurants where user_id ='.$favorite->user_id.' and restaurant_id ='. $favorite->restaurant_id);

		return $favorite;
	}

	public function getFavoriteRestaurants(Request $request)
	{
		$favorites = DB::select("select r.*, res.path from favorite_restaurants fr, restaurants r left join resources res on res.restaurant_id = r.id where fr.restaurant_id = r.id and fr.user_id = ".$request->id);

		foreach ($favorites as $favorite)
		{
			$favorite->summary = RestaurantRatingSummary::where('restaurant_id', $favorite->id)->first();
		}

		return $favorites;
	}

	public function getFavoriteRestaurant(Request $request)
	{
		$favorite = FavoriteRestaurant::where('user_id', $request->user_id)
			->where('restaurant_id', $request->restaurant_id)
			->first();

		if(is_null($favorite))
			throw new \Exception('The restaurant is not in favorite list ', 1);			

		return $favorite;
	}
}