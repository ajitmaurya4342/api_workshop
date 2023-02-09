<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Order;

//Libraries
use App\Libraries\CommonFunctions;
class OrderController extends Controller
{

    private $CommonFunctions;

    public function __construct()
	{
        $this->CommonFunctions = new CommonFunctions();

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders = Order::all();

        $return = array("status"=>"success","description"=>"Order List","orders"=>$orders);

        return response()->json($return);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id'=>'required|numeric',
            'payed'=>'required'
        ]);

        $order = new Order([
            'customer_id' => $request->get('customer_id'),
            'payed' => $request->get('payed'),
            'created_at' => date("Y-m-d H:i:s"),
            'updated_at' => date("Y-m-d H:i:s")
        ]);
        if($order->save()){
            $return = array("status"=>"success","description"=>"Order placed successfully");
        }else{
            $return = array("status"=>"failure","description"=>"Unable to place order");
        }

        return response()->json($return);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_id'=>'required|numeric',
            'payed'=>'required|boolean'
        ]);

        $order = Order::find($id);
        $order->customer_id =  $request->get('customer_id');
        if(!empty($request->get('product_id'))){
            $order->product_id = $request->get('product_id');
        }
        $order->payed = $request->get('payed');
        if($order->save()){
            $return = array("status"=>"success","description"=>"Order updated successfully");
        }else{
            $return = array("status"=>"failure","description"=>"Unable to update order");
        }

        return response()->json($return);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order = Order::find($id);

        if($order->delete()){
            $return = array("status"=>"success","description"=>"Order deleted successfully");
        }else{
            $return = array("status"=>"failure","description"=>"Unable to delete order");
        }
        return response()->json($return);
    }

    function addProductToOrder(Request $request, $id){

        $request->validate([
            'product_id'=>'required|numeric'
        ]);

        $order = Order::find($id);

        if($order->payed == 0){
            $order->product_id = $request->get('product_id');
            if($order->save()){
                $return = array("status"=>"success","description"=>"Product updated successfully");
            }else{
                $return = array("status"=>"failure","description"=>"Unable to update product");
            }
        }else{
            $return = array("status"=>"failure","description"=>"Order is already payed");
        }
        
        return response()->json($return);
    }

    function payOrder($id){
        $logFileName = "payOrder";
        $url = "https://superpay.view.agentur-loop.com/pay";

        $getOrderData = DB::select("SELECT c.email, p.price FROM orders o JOIN customers c ON c.id = o.customer_id JOIN products p ON p.id = o.product_id WHERE o.id = '$id'");
        $getOrderData = json_decode(json_encode($getOrderData),true);
        $this->CommonFunctions->logData($logFileName,"Order Detail".json_encode($getOrderData));
        if(!empty($getOrderData)){
            $post_data = array(
                "order_id" => $id,
                "customer_email" => $getOrderData->email,
                "value" => $getOrderData->price
            );
            $this->CommonFunctions->logData($logFileName,"Order Payment Request $url :: ".json_encode($post_data));
            $callback_res = $this->CommonFunctions->sendRequest($url,json_encode($post_data));
            $this->CommonFunctions->logData($logFileName,"Order Payment Response :: ".json_encode($callback_res));
            $status = "failure";
            if($callback_res['message'] == "Payment Successful"){
                $status = "success";
                $updateOrderPayment = DB::update("UPDATE orders SET payed = '1' WHERE id = '$id'");
            }

            $return = array("status"=>$status,"description"=>$callback_res['message']);
            return response()->json($return);
        }
        
    }
}
