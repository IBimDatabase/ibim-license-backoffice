<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Customer;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Package;
use App\Models\LicenseType;
use App\Models\OrderEmailLog;
use App\Services\LicenseKeyService;
use App\Mail\GeneratedLicenseEmail;
use App\Jobs\GeneratedLicenseEmailJob;
//use Webpatser\Uuid\Uuid;
//use GoldSpecDigital\LaravelUuid\Uuid; // Added_by_Abdul_Rehman_for_Upgrade Laravel
use Illuminate\Support\Facades\DB;
use App\Resources\Orders\OrderResource;
use App\Services\WPOrderService;
use Illuminate\Support\Str;
use App\Helpers\LicenseKeyHelper;
DB::enableQueryLog();

class OrderService
{
    public static function getOrdersData($data, $perPage)
    {
        $result = [];

        $query = new Order;

        if (!empty($data['product_name']))
        {
            $query = $query->whereHas('orderItems', function($sub) use ($data) {
                $sub->whereHas('product', function($q) use ($data) {
                    $q->where('product_name', 'like', '%'. $data['product_name']. '%');
                });
            })->orwhereHas('license', function($sub) use ($data) {
                $sub->whereHas('product', function($q) use ($data) {
                    $q->where('product_name', 'like', '%'. $data['product_name']. '%');
                });
            });
        }

        if (!empty($data['license_type']))
        {
            $query = $query->whereHas('orderItems', function($sub) use ($data) {
                $sub->whereHas('licenseType', function($q) use ($data) {
                    $q->where('name', 'like', '%'. $data['license_type']. '%');
                });
            })->orwhereHas('license', function($sub) use ($data) {
                $sub->whereHas('licenseProduct', function($subQuery) use ($data) {
                    $subQuery->whereHas('licenseType', function($q) use ($data) {
                        $q->where('name', 'like', '%'. $data['license_type']. '%');
                    });
                });
            });
        }

        if (!empty($data['customer_email']))
        {
            $query = $query->whereHas('customer', function($q) use ($data){
                $q->where('email', 'like', '%'. $data['customer_email']. '%');
            })->orwhereHas('license', function($sub) use ($data) {
                $sub->whereHas('customer', function($q) use ($data) {
                    $q->where('email', 'like', '%'. $data['customer_email']. '%');
                });
            });
        }

        if (!empty($data['order_id']))
            $query = $query->where('id', 'like', $data['order_id']);

        if (!empty($data['order_date']))
            $query = $query->whereDate('order_placed_at', '>=', date('Y-m-d', strtotime($data['order_date'])));

        if (!empty($data['order_status']))
        {
            if ($data['order_status'] == 'SUCCESS'){
                $query = $query->where(function($q) {
                    $q->where('order_status', 'PROCESSING');
                    $q->orWhere('order_status', 'COMPLETED');
                });
            }
            else
            {
                $query = $query->where('order_status', 'like', $data['order_status']);
            }
        }

        $query = $query->orderBy((@$data['sort_by']) ? @$data['sort_by'] : 'order_placed_at', (@$data['sort_order']) ? @$data['sort_order'] : 'DESC');

        if (!key_exists('page', $data) || $data['page'] == 'all')
            $orders = $query->paginate($perPage);
        else
            $orders = $query->paginate($perPage);

        $orders->map( function($order) {
            if (!empty($order->customer))
            {
                return $order->customer->makeHidden(['id']);
            }
            else
            {
                return $order->customer;
            }
        });

        $orders->map( function($order) {
            if (!empty($order->orderItems))
            {
                $order->orderItems->map( function($orderItem) {
                    $orderItem->product;
                    $orderItem->licenseType;
                });
            }
        });

        $orders->map( function($order) {
            if (!empty($order->license))
            {
                @$order->license->product;
                @$order->license->customer;
                @$order->license->licenseProduct->licenseType;
                $order->license->makeHidden(['id']);
            }
            else
            {
                @$order->license->product;
                @$order->license->customer;
                @$order->license->licenseProduct->licenseType;
                $order->license;
            }
        });

        foreach ($orders as $order)
        {
            if (!empty($order->orderItems) && count($order->orderItems) > 0)
            {
                foreach($order->orderItems as $orderItem)
                {
                    $result['data'][] = OrderResource::order_details($order, $orderItem);
                }
            }
            else
            {
                $result['data'][] = OrderResource::order_details($order);
            }
        }

        if (!empty($result['data']))
        {
            $result['from'] = $orders->firstItem();
            $result['to'] = $orders->lastItem();
            $result['total'] = $orders->total();
            $result['current_page'] = $orders->currentPage();
            $result['last_page'] = $orders->lastPage();
            $result['per_page'] = $orders->perPage();
        }

        //dd (DB::getQueryLog());
        return json_encode(["status" => true, "code" => 200, "message" => 'Orders Retrieved Successfully', "data" => $result, "status_code" => 200]);
    }

    public static function syncWooCommerceOrder($data)
    {
        $order = Order::where('order_uuid', @$data['id'])->first();
        $orderSync = WPOrderService::sendOrderInfoData(['id' => @$order->wp_order_id]);

        if (!empty($orderSync['status']) && $orderSync['status'] == true)
        {
            return json_encode(["status" => true, "code" => 200, "message" => "Product Updated Successfully", "data" => @$orderSync['data'], "status_code" => 200]);
        }
        else
        {
            return json_encode(["status" => false, "code" => 422, "message" => "There Is No Recent Updates", "data" => "", "status_code" => 422]);
        }
    }
    public static function order_info_payload($request_data)
    {
        $payload['order_info'] = [];
        if(!empty($request_data['order_info'])){
            $payload= [
                'order_uuid' => Str::uuid(),
                'wp_order_id' => @$request_data['order_info']['order_id'],
                'order_type' => @$request_data['order_info']['type'],
                'order_status' => 'COMPLETED',
                'total_price' => @$request_data['order_info']['order_amount'],
                'order_reference_no' => @$request_data['order_info']['order_number'],
                'order_placed_at' => @$request_data['order_info']['date'],
                'order_source' => @$request_data['order_info']['source'],
                'source' => "API"
            ];
        }
        return $payload;
    }


    public static function customer_info_payload($request_data)
    {
        $payload['customer_info'] = [];
        if(!empty($request_data['customer_info'])){
        $payload= [
            'customer_uuid' => Str::uuid(),
            'wp_customer_id' => @$request_data['customer_info']['id'],
            'user_name' => @$request_data['customer_info']['name'],
            'first_name' => @$request_data['customer_info']['name'],
            'email' => @$request_data['customer_info']['email'],
            'phone' => @$request_data['customer_info']['phone']
        ];
        }
        return $payload;
    }

    public static function order_items_payload($request_data)
    {
        $payload= [];
        foreach($request_data['order_items'] as $item) {
            $order_item = [];

            $license_type = LicenseType::where('code', $item['license_type'])->first();
            $order_item['license_type_id'] = @$license_type->id;
            $order_item['license_code'] = @$license_type->code;
            $entity_type = '';
            $entity_ref_id = '';

            if($item['type'] == 'PRODUCT'){
                $order_item['entity_type'] = 'PRODUCT';
                $product_info = Product::where('product_uuid',$item['product_id'])->first();
                $order_item['entity_ref_id'] = @$product_info->id;
            }

            if($item['type'] == 'PACKAGE'){
                $order_item['entity_type'] = 'PACKAGE';
                $product_info = Package::where('package_uuid',$item['package_id'])->first();
                $order_item['entity_ref_id'] = @$product_info->id;
            }
            $order_item['order_id'] = null;
            $order_item['order_item_uuid'] = Str::uuid();
            $order_item['quantity'] = @$item['quantity'];
            $order_item['unit_price'] = @$item['unit_price'];
            $order_item['total_price'] = @$item['total_price'];
            $order_item['download_url'] = @$item['download_url'];
            $order_item['additional_info'] =@$item['additional_info'];
            $order_item['status'] = 'ACTIVE';

            $payload[] = $order_item;
        }
        return $payload;
    }

    public static function create_order_data($request_data){
        $result = [];
        $order_payload = self::order_info_payload($request_data);
        $customer_payload = self::customer_info_payload($request_data);
        $order_items_payload = self::order_items_payload($request_data);
        // dd($order_items_payload);
        $order_id = $order_payload['wp_order_id'];
        // $order_record = Order::find($order_id);
        $customer_record = Customer::where('email', $customer_payload['email'])->first();
        // DB::beginTransaction();
        $generatedLicensesArray=[];
        try {
            if ($customer_record) {
                $customer_info = Customer::updateRecord($customer_payload, $customer_record->id);
            } else {
                $customer_info = Customer::insertRecord($customer_payload);
            }
            $order_payload['customer_id']=@$customer_info->id;
            $order_info = Order::insertRecord($order_payload);
            $result['message'] = "Order Data added successfully!";
            
            foreach ($order_items_payload as $payload) {
                $payload['order_id'] = $order_info->id;
                $order_item_info = OrderItem::insertRecord($payload);
                $licenseData = [
                    "entity_type" => $payload['entity_type'],
                    "entity_ref_id" => $payload['entity_ref_id'],
                    "order_id" => $order_info->id,
                    "customer_id" => (isset($customer_info)) ? $customer_info->id : NULL,
                    "license_code" => $payload['license_code'],
                    "quantity" => $payload['quantity'],
                    "wp_order_item_id" => $order_item_info->id
                ];
                $generatedLicensesData = LicenseKeyService::generateLicense($licenseData);
                $generatedLicenses = json_decode($generatedLicensesData, true);
                if ($generatedLicenses['status'] === true) {
                    if (isset($generatedLicenses['data'])) {
                        foreach ($generatedLicenses['data'] as $key => $generatedLicense) {
                            $generatedLicenses['data'][$key]['download_url'] = @$payload['download_url'];
                        }
                        $generatedLicensesArray[] = @$generatedLicenses['data'];
                    }
                }

                }
            // DB::commit();
            $result['status'] = true;
        }catch (\Exception $e) {
            // DB::rollBack();
            $result['status'] = false;
            $result['data'] = [
                'getCode' => $e->getCode(),
                'getFile' => $e->getFile(),
                'getLine' => $e->getLine(),
                'message' => $e->getMessage(),
            ];
        }
        if (!empty($result['status'])) {
            if(!empty($generatedLicensesArray)){
                foreach($generatedLicensesArray as $license){
                    $mailData = [
                        'order_id' => @$license[0]['details']['order']['order_reference_no'],
                        'generatedLicenses' => $license,
                        'customer_uname' => @$customer_info->user_name,
                        'customer_fname' => @$customer_info->first_name,
                        'customer_lname' => @$customer_info->last_name,
                        'customer_email' => @$customer_info->email,
                        'subject' => 'IBIM - Order Placed',
                    ];
                    GeneratedLicenseEmailJob::dispatch($mailData);

                    $mailLogData = [
                        'email_uuid' => (string) Str::uuid(),
                        'customer_id' => (isset($customer_info)) ? $customer_info->id : NULL,
                        'entity_type' => $order_item_info->entity_type,
                        'email_to' => @$customer_info->email,
                        'subject' => 'IBIM - Order Placed',
                    ];
                    OrderEmailLog::insertRecord($mailLogData);
                }
            }
            // $data = OrderResource::create_order($order_info,$customer_info);
            $view_payload=[];
            $view_payload['id']=$order_info->order_uuid;
            $data = self::view_order_data($view_payload);
            $result['data'] = $data;
            $result['message'] = "Order Data added successfully!";
        }
        return $result;
    }
    public static function view_order_data($request_data){
        $result = [];
        
        $order_info = Order::where("order_uuid", $request_data['id'])->first();
        if(!empty($order_info->id)){

            $result["order_info"]=[
                "id"=>$order_info->order_uuid,
                "order_id"=>$order_info->wp_order_id,
                "order_source"=>$order_info->order_source,
                "order_reference_no"=>$order_info->order_reference_no,
                "order_type"=>$order_info->order_type,
                "order_status"=>$order_info->order_status,
                "total_price"=>$order_info->total_price,
                "source"=>$order_info->source,
                "status"=>$order_info->status,
                "order_placed_at"=>$order_info->order_placed_at,
            ];
            if(!empty($order_info->customer_id)){
                $customer_info = Customer::where('id', $order_info->customer_id)->first();
                $result["customer_info"]=[
                    "id"=>$customer_info->customer_uuid,
                    "name"=>$customer_info->user_name,
                    "email"=>$customer_info->email,
                    "phone"=>$customer_info->phone,
                    "status"=>$customer_info->status,
                ];
            }
            $order_item_info=DB::table('order_items as oi')
            ->leftJoin('product_license_keys as plk', function($join){
                return $join->on('oi.id', 'plk.wp_order_item_id')->on('oi.order_id', 'plk.order_id');
            })->leftJoin('products as p', function($join){
                return $join->on('plk.product_id', 'p.id');
            })->leftJoin('packages as pk', function($join){
                return $join->on('plk.package_id', 'pk.id');
            })->leftJoin('license_types as lt', function($join){
                return $join->on('oi.license_type_id', 'lt.id');
            })->select('oi.id', 'oi.order_item_uuid', 'oi.order_id', 'oi.quantity', 'oi.unit_price', 'oi.total_price'
            ,'p.id as p_id', 'p.product_uuid', 'p.product_name', 'p.product_id', 'p.product_code'
            , 'pk.id as pk_id', 'pk.package_uuid', 'pk.package_name', 'pk.package_code'
            , 'lt.id as license_type_id', 'lt.name as license_type_name', 'lt.code as  license_type_code'
            , 'plk.id as license_id', 'plk.license_uuid', 'plk.license_key', 'plk.mac_address', 'plk.expiry_date', 'plk.purchased_date')
            ->where('oi.order_id', $order_info->id)->get();
            $processed_item_group=[];
            if(!empty($order_item_info)){
                foreach ($order_item_info as $key => $value) {
                    if(empty($processed_item_group[$value->id])){
                        $temp_processed=[];
                        $temp_processed=[
                            "id"=>$value->order_item_uuid,
                            "quantity"=>$value->quantity,
                            "unit_price"=>$value->unit_price,
                            "total_price"=>$value->total_price,
                            "product"=>[],
                            "package"=>[],
                            "licence_type"=>[],
                            "licence_keys"=>[],
                        ];
                        $temp_processed["licence_type"]=[
                            "license_name"=>$value->license_type_name,
                            "license_code"=>$value->license_type_code,
                        ];
                        if(!empty($value->pk_id)){
                            $temp_processed["type"]="PACKAGE";
                            $temp_processed["package"]=[
                                "id"=>$value->package_uuid,
                                "package_name"=>$value->package_name,
                                "package_code"=>$value->package_code,
                            ];
                        } else {
                            $temp_processed["type"]="PRODUCT";
                            $temp_processed["product"]=[
                                "id"=>$value->product_uuid,
                                "product_name"=>$value->product_name,
                                "product_id"=>$value->product_id,
                                "product_code"=>$value->product_code,
                            ];
                        }
                        $processed_item_group[$value->id]=$temp_processed;
                    }
                    if(empty($processed_item_group[$value->id]["licence_keys"][$value->license_key])){
                        $processed_item_group[$value->id]["licence_keys"][$value->license_key]=[];
                        $processed_item_group[$value->id]["licence_keys"][$value->license_key]['license_key']=LicenseKeyHelper::licenseKeyHash($value->license_key);
                        $processed_item_group[$value->id]["licence_keys"][$value->license_key]['license_info']=[];
                        $processed_item_group[$value->id]["licence_keys"][$value->license_key]["license_type"]=[
                            "license_name"=>$value->license_type_name,
                            "license_code"=>$value->license_type_code,
                        ];
                    }
                    $license_key_info=[];
                    $license_key_info['license_id']=$value->license_uuid;
                    $license_key_info['mac_address']=$value->mac_address;
                    $license_key_info['expiry_date']=$value->expiry_date;
                    $license_key_info['activated_on']=$value->purchased_date;
                    $license_key_info["product"]=[
                        "id"=>$value->product_uuid,
                        "product_name"=>$value->product_name,
                        "product_id"=>$value->product_id,
                        "product_code"=>$value->product_code,
                    ];
                    
                    $processed_item_group[$value->id]["licence_keys"][$value->license_key]['license_info'][]=$license_key_info;
                }
            }
            $result["order_items"]=[];
            if(!empty($processed_item_group)){
                foreach($processed_item_group as $key => $value) {
                    $temp=$value;
                    $temp['licence_keys']=array_values($value['licence_keys']);
                    $result["order_items"][]=$temp;
                }
            }
            
        }
        return $result;
    }
    public static function view_order_items($item_ids){
        $result = [];
        if(!empty($item_ids)){
            $order_item_info=DB::table('order_items as oi')
            ->leftJoin('product_license_keys as plk', function($join){
                return $join->on('oi.id', 'plk.wp_order_item_id')->on('oi.order_id', 'plk.order_id');
            })->leftJoin('products as p', function($join){
                return $join->on('plk.product_id', 'p.id');
            })->leftJoin('packages as pk', function($join){
                return $join->on('plk.package_id', 'pk.id');
            })->leftJoin('license_types as lt', function($join){
                return $join->on('oi.license_type_id', 'lt.id');
            })->select('oi.id', 'oi.order_item_uuid', 'oi.order_id', 'oi.quantity', 'oi.unit_price', 'oi.total_price'
            ,'p.id as p_id', 'p.product_uuid', 'p.product_name', 'p.product_id', 'p.product_code'
            , 'pk.id as pk_id', 'pk.package_uuid', 'pk.package_name', 'pk.package_code'
            , 'lt.id as license_type_id', 'lt.name as license_type_name', 'lt.code as  license_type_code'
            , 'plk.id as license_id', 'plk.license_uuid', 'plk.license_key', 'plk.mac_address', 'plk.expiry_date', 'plk.purchased_date')
            ->whereIn('oi.order_item_uuid', $item_ids)->get();
            $processed_item_group=[];
            if(!empty($order_item_info)){
                foreach ($order_item_info as $key => $value) {
                    if(empty($processed_item_group[$value->id])){
                        $temp_processed=[];
                        $temp_processed=[
                            "id"=>$value->order_item_uuid,
                            "quantity"=>$value->quantity,
                            "unit_price"=>$value->unit_price,
                            "total_price"=>$value->total_price,
                            "product"=>[],
                            "package"=>[],
                            "licence_type"=>[],
                            "licence_keys"=>[],
                        ];
                        $temp_processed["licence_type"]=[
                            "license_name"=>$value->license_type_name,
                            "license_code"=>$value->license_type_code,
                        ];
                        if(!empty($value->pk_id)){
                            $temp_processed["type"]="PACKAGE";
                            $temp_processed["package"]=[
                                "id"=>$value->package_uuid,
                                "package_name"=>$value->package_name,
                                "package_code"=>$value->package_code,
                            ];
                        } else {
                            $temp_processed["type"]="PRODUCT";
                            $temp_processed["product"]=[
                                "id"=>$value->product_uuid,
                                "product_name"=>$value->product_name,
                                "product_id"=>$value->product_id,
                                "product_code"=>$value->product_code,
                            ];
                        }
                        $processed_item_group[$value->id]=$temp_processed;
                    }
                    if(empty($processed_item_group[$value->id]["licence_keys"][$value->license_key])){
                        $processed_item_group[$value->id]["licence_keys"][$value->license_key]=[];
                        $processed_item_group[$value->id]["licence_keys"][$value->license_key]['license_key']=LicenseKeyHelper::licenseKeyHash($value->license_key);
                        $processed_item_group[$value->id]["licence_keys"][$value->license_key]['license_info']=[];
                        $processed_item_group[$value->id]["licence_keys"][$value->license_key]["license_type"]=[
                            "license_name"=>$value->license_type_name,
                            "license_code"=>$value->license_type_code,
                        ];
                    }
                    $license_key_info=[];
                    $license_key_info['license_id']=$value->license_uuid;
                    $license_key_info['mac_address']=$value->mac_address;
                    $license_key_info['expiry_date']=$value->expiry_date;
                    $license_key_info['activated_on']=$value->purchased_date;
                    $license_key_info["product"]=[
                        "id"=>$value->product_uuid,
                        "product_name"=>$value->product_name,
                        "product_id"=>$value->product_id,
                        "product_code"=>$value->product_code,
                    ];
                    
                    $processed_item_group[$value->id]["licence_keys"][$value->license_key]['license_info'][]=$license_key_info;
                }
            }
            $result["order_items"]=[];
            if(!empty($processed_item_group)){
                foreach($processed_item_group as $key => $value) {
                    $temp=$value;
                    $temp['licence_keys']=array_values($value['licence_keys']);
                    $result["order_items"][]=$temp;
                }
            }
        }
        
        return $result;
    }



}