<?php

namespace App\Repositories;

use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeliveryZoneRepository
{
    public function create(Request $request)
    {
        $errors = DeliveryZone::validate($request, [
            'restaurant_id' => 'required|exists:restaurants,id',
            'coordinates' => 'required|array',
            'coordinates.*.latitude' => 'required|numeric',
            'coordinates.*.longitude' => 'required|numeric'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $coordinatesAsText = '';

        foreach ($request->coordinates as $coordinate)
            $coordinatesAsText .= $coordinate['latitude'].' '.$coordinate['longitude'].',';

        $lastCoordinate = $request->coordinates[0];
        $coordinatesAsText .= $lastCoordinate['latitude'].' '.$lastCoordinate['longitude'];

        $deliveryZone = new DeliveryZone();
        $deliveryZone->restaurant_id = $request->restaurant_id;
        $deliveryZone->zone_polygon_as_text = $coordinatesAsText;
        $deliveryZone->save();

        DB::update("update delivery_zones set zone_polygon = ST_POLYFROMTEXT('POLYGON((".$coordinatesAsText."))') where id = ". $deliveryZone->id);

        return $deliveryZone;
    }

    public function update(Request $request)
    {
        $errors = DeliveryZone::validate($request, [
            'coordinates' => 'required|array',
            'coordinates.*.latitude' => 'required|numeric',
            'coordinates.*.longitude' => 'required|numeric'
        ]);

        if($errors != null)
            throw new \Exception(json_encode($errors), 1);

        $deliveryZone = DeliveryZone::find($request->id);

        if($deliveryZone == null)
            throw new \Exception('Non existent delivery zone', 2);

        $coordinatesAsText = '';

        foreach ($request->coordinates as $coordinate)
            $coordinatesAsText .= $coordinate['latitude'].' '.$coordinate['longitude'].',';

        $lastCoordinate = $request->coordinates[0];
        $coordinatesAsText .= $lastCoordinate['latitude'].' '.$lastCoordinate['longitude'];

        $deliveryZone->zone_polygon_as_text = $coordinatesAsText;
        $deliveryZone->save();

        DB::update("update delivery_zones set zone_polygon = ST_POLYFROMTEXT('POLYGON((".$coordinatesAsText."))') where id = ". $deliveryZone->id);

        unset($deliveryZone->zone_polygon);

        return $deliveryZone;
    }
}