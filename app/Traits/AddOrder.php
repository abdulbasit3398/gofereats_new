<?php 

/**
 * AddOrder Trait
 *
 * @package     GoferEats
 * @subpackage  AddOrder Trait
 * @category    AddOrder
 * @author      Trioangle Product Team
 * @version     1.6
 * @link        http://trioangle.com
 */

namespace App\Traits;
use App\Models\Wallet;
use App\Models\Payout;
use JWTAuth;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderDelivery;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\OrderItem;
use App\Models\Store;
use App\Models\MenuItemModifier;
use App\Models\MenuItemModifierItem;
use App\Models\OrderItemModifier;
use App\Models\OrderItemModifierItem;

trait AddOrder
{
	/**
	 * Add to cart after Login or Register
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function add_cart_item($request,$status=1) 
	{
		try {
			$user_details = JWTAuth::parseToken()->authenticate();

			if($status == 0) {
				$this->saveLocation($request);
				$request->order =  json_decode(str_replace ('\"','"', $request->order), true); 
				if(!$request->order) {
					
					$request->order = array();
				}
				$store_id = $request->order[0]['store_id'];
			}
			else
			{
				$store_id = $request->store_id;	
			}
			$store_id = $store_id ;
		}
		catch(\Exception $e) {
			return [
				'status_code' => '0',
				'status_message' => $e->getMessage(),
			];
		}
		if(!$request['order'])
		{
			$already_cart = Order::where('user_id', $user_details->id)->status('cart')->first();
			if ($already_cart) {
				if ($already_cart->store_id != $store_id) {
					$new_store 			 = Store::find($store_id);
					$already_store_name  	= $already_cart->store->name;
					$new_store_name 		= $new_store->name;
	                $new_first = '';
	                $already_first = '';

	               	$new_address = UserAddress::where('user_id', $new_store->user_id)->default()->first();
	               	$already_address = UserAddress::where('user_id', $already_cart->store->user_id)->default()->first();
					if(isset($new_address->city)) {
						$new_first = '-'.$new_address->city;
					}

					if(isset($already_address->city)) {
						$already_first =  '-'.$already_address->city;
					}

					return [
						'status_code' => '0',
						'status_message' => trans('store_api_language.store.cart_already').$already_store_name.$already_first.trans('store_api_language.store.clear_the_cart').$new_store_name.$new_first.trans('store_api_language.store.instead'),
					];
				}
			}
		}
		else
		{	
			$order = Order::where('user_id', $user_details->id)->status('cart')->first(); 
			if(isset($order))
			{
				$order_items = OrderItem::where('order_id', $order->id)->get();
				$order_item_modifiers = OrderItemModifier::whereIn('order_item_id',$order_items->pluck('id')->toArray())->get();
				if($order_item_modifiers->count()) {
					$order_item_modifier_items = OrderItemModifierItem::whereIn('order_item_modifier_id',$order_item_modifiers->pluck('id')->toArray())->delete();
				}
				OrderItemModifier::whereIn('order_item_id',$order_items->pluck('id')->toArray())->delete();
		        $order->order_item()->forcedelete();
		        $order->order_delivery()->forcedelete();
		        $order->forcedelete();	
	    	}
		}
		$order = Order::where('user_id', $user_details->id)->where('store_id', $store_id)->status('cart')->first();
		$address_details = $this->address_details($request);
		
		$order_type = $address_details['order_type'];
		$delivery_time = $address_details['delivery_time'];

		if($status == 1) {
			$menu 	= MenuItem::where('id', $request->menu_item_id)->first();
			$modifier_price = 0;
			$menu_addon_items = json_decode($request->menu_addon_items,true);

			//check menu item Available or not
			if (!$menu) {
				return [
					'status_code' => '2',
					'status_message' => 'Menu item not available at the moment',
				];
			}

			if(is_array($menu_addon_items)) {
				foreach($menu_addon_items as $menu_addon) {
					$count = $menu_addon['count'];
					$is_select = $menu_addon['is_select'];
					$id = $menu_addon['id'];
					$modifier_id = $menu_addon['menu_item_modifier_id'];
					$menu_modifier_item = MenuItemModifierItem::where('id',$id)->first();
					if(!$menu_modifier_item) {
						return [
							'status_code' => '2',
							'status_message' => 'Menu item Add-on not available at the moment',
						];
					}
					$modifier_price += ($count * $menu_modifier_item->price);
				}	
			}
	
		}

		/* Start Generate Order */
		$store = Store::find($store_id);

		if (!$order) {
			$order = new Order;
			$order->store_id = $store_id;
			$order->user_id = $user_details->id;			
			$order->schedule_status = $order_type;
			$order->schedule_time = $delivery_time;
			$order->currency_code = $user_details->currency_code->code;
			$order->status = 0;
			$order->save();
		}
        /* End Generate Order */
        
        /* Start Generate Order Item details */
        if($status == 1) {
        	$menu_price = $menu->price;
        	$t_menu_price = $menu->offer_price > 0 ? $menu->offer_price : $menu_price;
			$total_amount = $request->quantity * ($t_menu_price + $modifier_price);
			$tax = ($total_amount * $menu->tax_percentage / 100);
			$orderitem = OrderItem::findOrNew($request->order_item_id);
			$orderitem->order_id = $order->id;
			$orderitem->menu_item_id = $request->menu_item_id;
			$orderitem->menu_name = $menu->name;
			$orderitem->price = $t_menu_price;
			$orderitem->quantity = $request->quantity;
			$orderitem->notes = $request->notes;
			$orderitem->total_amount = $total_amount;
			$orderitem->modifier_price = $modifier_price;
			$orderitem->currency_code = $order->currency_code;
			$orderitem->tax = $tax;
			$orderitem->save();

			if($request->order_item_id != '') {
				$remove_order_item = OrderItemModifier::where('order_item_id',$orderitem->id)->get();

				if($remove_order_item) {
					foreach ($remove_order_item as $key => $value) {
						$remove_order_item_modifer = OrderItemModifierItem::whereIn('order_item_modifier_id',[$value->id])->delete();
					}
					OrderItemModifier::whereIn('order_item_id',[$orderitem->id])->delete();
				}
			}

			if(is_array($menu_addon_items)) {
				foreach($menu_addon_items as $menu_addon) {
				
					$modifier_id = $menu_addon['menu_item_modifier_id'];
					$menu_modifier = MenuItemModifier::find($modifier_id);
					$menu_modifier_item = MenuItemModifierItem::where('id',$menu_addon['id'])->first();

					$orderitem_modifier = OrderItemModifier::firstOrCreate(['order_item_id' => $orderitem->id,'modifier_id' => $modifier_id]);

					$orderitem_modifier->modifier_name = $menu_modifier->name;

					$orderitem_modifier->modifier_price += ($menu_addon['count'] * $menu_modifier_item->price);
					$orderitem_modifier->save();

					
					$orderitem_modifier_item = OrderItemModifierItem::firstOrNew(['order_item_modifier_id' => $orderitem_modifier->id,'menu_item_modifier_item_id' => $menu_modifier_item->id]);
					
					$orderitem_modifier_item->order_item_modifier_id = $orderitem_modifier->id;
					$orderitem_modifier_item->menu_item_modifier_item_id = $menu_modifier_item->id;

					$orderitem_modifier_item->modifier_item_name = $menu_modifier_item->name;
					$orderitem_modifier_item->count = $menu_addon['count'];
					$orderitem_modifier_item->price = $menu_modifier_item->price;
					$orderitem_modifier_item->currency_code = $order->currency_code;
					$orderitem_modifier_item->save();
				}
			}
		}
		else {
			foreach($request->order as $item) {
				$menu = MenuItem::where('id', $item['menu_item_id'])->first();
				$modifier_price = 0;
				$menu_addon_items = $item['menu_addon_items'];
				foreach($menu_addon_items as $menu_addon) {
					$count = $menu_addon['count'];
					$is_select = $menu_addon['is_select'];
					$id = $menu_addon['id'];
					$modifier_id = $menu_addon['menu_item_modifier_id'];
					$menu_modifier_item = MenuItemModifierItem::where('id',$id)->first();
					if(!$menu_modifier_item) {
						return [
							'status_code' => '2',
							'status_message' => 'Menu item Add-on not available at the moment',
						];
					}
					$modifier_price += ($count * $menu_modifier_item->price);
				}
				$menu_price = $menu->price;
				$t_menu_price = $menu->offer_price > 0 ? $menu->offer_price : $menu_price;
				$total_amount = $item['quantity'] * ($t_menu_price + $modifier_price);
				$tax = ($total_amount * $menu->tax_percentage / 100);
				$orderitem = new OrderItem;			
				$orderitem->order_id = $order->id;
				$orderitem->menu_item_id = $item['menu_item_id'];
				$orderitem->price = $t_menu_price;
				$orderitem->menu_name = $menu->name;
				$orderitem->quantity = $item['quantity'];
				$orderitem->notes = $item['notes'];
				$orderitem->total_amount = $total_amount;
				$orderitem->modifier_price = $modifier_price;
				$orderitem->currency_code = $order->currency_code;
				$orderitem->tax = $tax;
				$orderitem->save();
				if(is_array($menu_addon_items)) {
					foreach($menu_addon_items as $menu_addon) {
						$modifier_id = $menu_addon['menu_item_modifier_id'];
						$menu_modifier = MenuItemModifier::find($modifier_id);
						$menu_modifier_item = MenuItemModifierItem::where('id',$menu_addon['id'])->first();

						$orderitem_modifier = OrderItemModifier::firstOrCreate(['order_item_id' => $orderitem->id,'modifier_id' => $modifier_id]);
						$orderitem_modifier->modifier_name = $menu_modifier->name;

						$orderitem_modifier->modifier_price += ($menu_addon['count'] * $menu_modifier_item->price);
						$orderitem_modifier->save();

						$orderitem_modifier_item = OrderItemModifierItem::firstOrNew(['order_item_modifier_id' => $orderitem_modifier->id,'menu_item_modifier_item_id' => $menu_modifier_item->id]);
						
						$orderitem_modifier_item->order_item_modifier_id = $orderitem_modifier->id;
						$orderitem_modifier_item->menu_item_modifier_item_id = $menu_modifier_item->id;
						$orderitem_modifier_item->modifier_item_name = $menu_modifier_item->name;
						$orderitem_modifier_item->count = $menu_addon['count'];
						$orderitem_modifier_item->price = $menu_modifier_item->price;
						$orderitem_modifier_item->currency_code = $order->currency_code;
						$orderitem_modifier_item->save();
						
					}
				}
			}

		}
		/* End Generate Order Item details */

		// update order or cart sum price and tax
		$orderitem = OrderItem::where('order_id', $order->id)->get();
		$order_update = Order::find($order->id);
		$order_delivery = $order_update->order_delivery;

		if (!$order_delivery) {
			$order_delivery = new OrderDelivery;
			$order_delivery->order_id = $order_update->id;
			$order_delivery->currency_code = $order_update->currency_code;
			$order_delivery->status = -1;
			$order_delivery->save();
		}
		
		if (site_setting('delivery_fee_type') == 0) {
			$delivery_fee = site_setting('delivery_fee');
			$order_update->delivery_fee = $delivery_fee;
			$lat1 = $order_update->user_location[0]['latitude'];
			$lat2 = $order_update->user_location[1]['latitude'];
			$long1 = $order_update->user_location[0]['longitude'];
			$long2 = $order_update->user_location[1]['longitude'];

			$result = get_driving_distance($lat1, $lat2, $long1, $long2);
			$km = 0;

			if ($result['distance'] != '')
				$km = round(floor($result['distance'] / 1000) . '.' . floor($result['distance'] % 1000),1);
			

			$order_delivery->fee_type = 0;
			$order_delivery->total_fare = $delivery_fee;
			$order_delivery->drop_distance = $km;
			$order_delivery->save();
		}
		else {

			$pickup_fare = site_setting('pickup_fare');
			$drop_fare = site_setting('drop_fare');
			$distance_fare = site_setting('distance_fare');

			$lat1 = $order_update->user_location[0]['latitude'];
			$lat2 = $order_update->user_location[1]['latitude'];
			$long1 = $order_update->user_location[0]['longitude'];
			$long2 = $order_update->user_location[1]['longitude'];

			$result = get_driving_distance($lat1, $lat2, $long1, $long2);

			$km = round(floor($result['distance'] / 1000) . '.' . floor($result['distance'] % 1000));
			\Log::error('get distance'. json_encode($result));
			\Log::error('Total distance'.$result['distance']);
			\Log::error('Total km  '.$km);
			$delivery_fee = $pickup_fare + $drop_fare + ($km * $distance_fare);

			$order_delivery->fee_type = 1;
			$order_delivery->pickup_fare = $pickup_fare;
			$order_delivery->drop_fare = $drop_fare;
			$order_delivery->distance_fare = $distance_fare;
			$order_delivery->drop_distance = $km;
			$order_delivery->est_distance = $km;
			$order_delivery->total_fare = $delivery_fee;
			$order_delivery->save();
		}
		

		$subtotal = offer_calculation($store_id, $order->id);
		
		$promo_amount = promo_calculation();

		$order_quantity = $orderitem->sum('quantity');
		$booking_percentage = site_setting('booking_fee');
		$booking_fee = ($subtotal * $booking_percentage / 100);
		// $order_update->subtotal = $subtotal;
		$order_update->booking_fee = $booking_fee;
		$order_update->delivery_fee = $delivery_fee;
		$order_update->store_commision_fee = 0;
		$order_update->wallet_amount = 0;
		$order_update->owe_amount = 0;

		$order->schedule_status = $order_type;
		$order->schedule_time = $delivery_time;
		$order_update->save();
		$data = get_user_order_details($store_id, $user_details->id,'delivery');
		return array('subtotal' =>  $data['subtotal'] ,'quantity' => $order_quantity,'status_code' => '1');
	}

	/**
	 * save default location for user
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function saveLocation($request) 
	{
		$user_details = JWTAuth::toUser($request->token);

		UserAddress::where('user_id', $user_details->id)->update(['default' => 0]);

		$address = UserAddress::where('user_id', $user_details->id)->where('type', '2')->first();

		if ($address == '') {
			$address = new UserAddress;
			$address->user_id = $user_details->id;
		}

		$address->street = $request->street;
		$address->city = $request->city;
		$address->state = $request->state;
		$address->first_address = $request->first_address;
		$address->second_address = $request->second_address;
		$address->postal_code = $request->postal_code;
		$address->country = $request->country;
		$address->country_code = $request->country_code;
		$address->type = 2;
		$address->default = 1;
		$address->apartment = $request->apartment;
		$address->delivery_note = $request->delivery_note ? $request->delivery_note : '';
		$address->delivery_options = $request->delivery_options ? $request->delivery_options : '';
		$address->order_type = $request->order_type ? $request->order_type : '';
		$address->delivery_time = $request->delivery_time ? $request->delivery_time : '';
		$address->latitude = $request->latitude;
		$address->longitude = $request->longitude;
		$address->address = $request->address;
		$address->save();
	}


	/**
	 * Default user address
	 */

	public function address_details($request)
	{
		$user_details = JWTAuth::toUser($request->token);
		$user = User::where('id', $user_details->id)->first();
		return list('latitude' => $latitude, 'longitude' => $longitude, 'order_type' => $order_type, 'delivery_time' => $delivery_time) =
		collect($user->user_address)->only(['latitude', 'longitude', 'order_type', 'delivery_time'])->toArray();
    }

}
