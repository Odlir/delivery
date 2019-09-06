<?php

namespace App\Repositories;

use Illuminate\Http\Request;
use App\Models\Restaurant;
use Illuminate\Support\Facades\DB;

class ReportRepository
{
	public function getDaySales(Request $request)
	{
		$errors = Restaurant::validate($request, [
			'restaurant_id' => 'required|exists:restaurants,id',
			'date' => 'required|date'
		]);

		if(!is_null($errors))
			throw new \Exception(json_encode($errors), 1);			

		$report = [
			'total_sales' => 0,
			'orders_count' => 0,
			'total_delivery_charge' => 0,
			'sales_by_product' => null,
			'sales_by_category' => null,
			'lower_sale' => 0,
			'greater_sale' => 0,
			'average_sale_by_order' => 0
		];
		/*
		$orders = DB::select('select sum(total) as total_sales,
			 count(id) as orders_count,
			 sum(delivery_charge) as total_delivery_charge 
			 from orders where restaurant_id = '.$request->restaurant_id. ' and delivery_status = "delivered" and date(created_at) = "'.$request->date.'"')[0];

		$report['total_sales'] = $orders->total_sales;
		$report['orders_count'] = $orders->orders_count;
		$report['total_delivery_charge'] = $orders->total_delivery_charge;

		$report['lower_sale'] = DB::select('select min(total) lower from orders where restaurant_id = '.$request->restaurant_id. ' and delivery_status = "delivered" and date(created_at) = "'.$request->date.'"')[0]->lower;

		$report['greater_sale'] = DB::select('select max(total) greater from orders where restaurant_id = '.$request->restaurant_id. ' and delivery_status = "delivered" and date(created_at) = "'.$request->date.'"')[0]->greater;

		$report['sales_by_product'] = DB::select('select sum(od.subtotal) as total, p.name as product_name, p.id from orders o, order_details od, products p 
			where o.restaurant_id = '.$request->restaurant_id.' and date(o.created_at) = "'.
			$request->date.'" and o.delivery_status = "delivered" and o.id = od.order_id and od.product_id = p.id group by p.id');

		$report['sales_by_category'] = DB::select('select sum(od.subtotal) as total, pc.name as category, pc.id from orders o, order_details od, products p, product_categories pc  
			where o.restaurant_id = '.$request->restaurant_id.' and date(o.created_at) = "'.
			$request->date.'" and o.delivery_status = "delivered" and o.id = od.order_id and od.product_id = p.id and p.product_category_id = pc.id group by pc.id');

		$report['average_sale_by_order'] = DB::select('select avg(total) as average from orders o 
			where o.restaurant_id = '.$request->restaurant_id.' and date(o.created_at) = "'.
			$request->date.'" and o.delivery_status = "delivered"')[0]->average;
		*/


		$orders = DB::select('select sum(total) as total_sales,
			 count(id) as orders_count,
			 sum(delivery_charge) as total_delivery_charge 
			 from orders where restaurant_id = '.$request->restaurant_id. '  and date(created_at) = "'.$request->date.'"')[0];

		$report['total_sales'] = $orders->total_sales;
		$report['orders_count'] = $orders->orders_count;
		$report['total_delivery_charge'] = $orders->total_delivery_charge;

		$report['lower_sale'] = DB::select('select min(total) lower from orders where restaurant_id = '.$request->restaurant_id. '  and date(created_at) = "'.$request->date.'"')[0]->lower;

		$report['greater_sale'] = DB::select('select max(total) greater from orders where restaurant_id = '.$request->restaurant_id. '  and date(created_at) = "'.$request->date.'"')[0]->greater;

		$report['sales_by_product'] = DB::select('select sum(od.subtotal) as total, p.name as product_name, p.id, count(p.id) as amount from orders o, order_details od, products p 
			where o.restaurant_id = '.$request->restaurant_id.' and date(o.created_at) = "'.
			$request->date.'"  and o.id = od.order_id and od.product_id = p.id group by p.id');

		$report['sales_by_category'] = DB::select('select sum(od.subtotal) as total, pc.name as category, pc.id from orders o, order_details od, products p, product_categories pc  
			where o.restaurant_id = '.$request->restaurant_id.' and date(o.created_at) = "'.
			$request->date.'"  and o.id = od.order_id and od.product_id = p.id and p.product_category_id = pc.id group by pc.id');

		$report['average_sale_by_order'] = DB::select('select avg(total) as average from orders o 
			where o.restaurant_id = '.$request->restaurant_id.' and date(o.created_at) = "'.
			$request->date.'"')[0]->average;
		return $report;
	}

}