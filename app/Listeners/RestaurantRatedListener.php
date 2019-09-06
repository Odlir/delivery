<?php

namespace App\Listeners;

use App\Events\RestaurantRatedEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\RestaurantRatingSummary;

class RestaurantRatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  RestaurantRatedEvent  $event
     * @return void
     */
    public function handle(RestaurantRatedEvent $event)
    {
        $rating = $event->rating;

        $summary = RestaurantRatingSummary::where('restaurant_id', $rating->restaurant_id)->first();

        if(is_null($summary))
        {
            $summary = new RestaurantRatingSummary();
            $summary->restaurant_id = $rating->restaurant_id;
            $summary->one_stars = 0;
            $summary->two_stars = 0;
            $summary->three_stars = 0;
            $summary->four_stars = 0;
            $summary->five_stars = 0;

            $this->modifyScore($summary, $rating->score, true);

            $summary->save();

            return;
        }

        if($event->previousScore != 0)
            $this->modifyScore($summary, $event->previousScore, false);

        $this->modifyScore($summary, $rating->score, true);

        $summary->save();
    }

    private function modifyScore(&$summary, $score, $isPositive)
    {
        switch ($score) 
        {
            case 1: $summary->one_stars += ($isPositive? 1 : -1);  break;
            case 2: $summary->two_stars += ($isPositive? 1 : -1);  break;
            case 3: $summary->three_stars += ($isPositive? 1 : -1);  break;
            case 4: $summary->four_stars += ($isPositive? 1 : -1);  break;
            case 5: $summary->five_stars += ($isPositive? 1 : -1);  break;
        }
    }

}