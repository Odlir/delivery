<?php

namespace App\Repositories;

use App\Models\RestaurantRating;
use Illuminate\Http\Request;
use App\Models\Restaurant;
use App\Events\RestaurantRatedEvent;

class RestaurantRatingRepository
{
    public function create(Request $request)
    {
        $errors = RestaurantRating::validate($request, [
            'consumer_id' => 'required|exists:roles_users,user_id,role_id,2',
            'score' => 'required|integer|between:1,5',
            'commentary' => 'string',
            'restaurant_id' => 'required|exists:restaurants,id'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        if(!is_null(RestaurantRating::where('restaurant_id', $request->restaurant_id)->where('consumer_id', $request->consumer_id)->first()))
            throw new \Exception('The user needs edit the rating, not create', 2);            

        $rating = new RestaurantRating();
        $rating->restaurant_id = $request->restaurant_id;
        $rating->consumer_id = $request->consumer_id;
        $rating->score = $request->score;

        if($request->has('commentary'))
            $rating->commentary = $request->commentary;

        $rating->save();

        event(new RestaurantRatedEvent($rating));

        return $rating;
    }

    public function get(Request $request)
    {
        $errors = RestaurantRating::validate($request, [
            'owner' => 'in:consumer,restaurant',
            'consumer_id' => 'required_if:owner,consumer|exists:roles_users,user_id,role_id,2',
            'restaurant_id' => 'required_if:owner,restaurant,consumer|exists:restaurants,id'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        if(!is_null($request->id))
        {
            return RestaurantRating::where('id', $request->id)
                ->join('users', 'users.id', '=', 'user_restaurant_ratings')
                ->select('user_restaurant_ratings.*', 'users.email')
                ->first();
        }

        if($request->has('owner'))
        {
            if($request->owner == 'consumer')
            {
                return RestaurantRating::where('consumer_id', $request->consumer_id)->where('restaurant_id', $request->restaurant_id)->first();
            }
            elseif($request->owner == 'restaurant')
            {
                return RestaurantRating::where('restaurant_id', $request->restaurant_id)
                    ->join('users', 'users.id', '=', 'user_restaurant_ratings.consumer_id')
                    ->select('user_restaurant_ratings.*', 'users.email as consumer_email')
                    ->get();
            }
        }

        return RestaurantRating::all();
    }

    public function edit(Request $request)
    {
        $errors = RestaurantRating::validate($request, [
            'score' => 'integer|between:1,5',
            'commentary' => 'string'
        ]);

        if(!is_null($errors))
            throw new \Exception(json_encode($errors), 1);

        $rating = RestaurantRating::find($request->id);

        if(is_null($rating))
            throw new \Exception('Non existent rating', 2);
        
        $lastScore = $rating->score;

        if($request->has('score'))
            $rating->score = $request->score;

        if($request->has('commentary'))
            $rating->commentary = $request->commentary;

        if($lastScore != $rating->score)
            event(new RestaurantRatedEvent($rating, $lastScore));

        $rating->save();

        return $rating;
    }

}