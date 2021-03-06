<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use App\Models\VehicleRegister;
use App\OtpVerify;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller as BaseController;
use PaytmWallet;

class OrderController  extends BaseController
{
    
     public function initiate()
    {
        return view('paytm');
    }
    
    public function payOld(Request $request)
    {
        $amount = 1500; //Amount to be paid

        $userData = [
            'name' => $request->name, // Name of user
            'mobile' => $request->mobile, //Mobile number of user
            'email' => $request->email, //Email of user
            'fee' => $amount,
            'order_id' => $request->mobile."_".rand(1,1000) //Order id
        ];

        //$paytmuser = Paytm::create($userData); // creates a new database record

        $payment = PaytmWallet::with('receive');

        $payment->prepare([
            'order' => $userData['order_id'], 
            'user' => $paytmuser->id,
            'mobile_number' => $userData['mobile'],
            'email' => $userData['email'], // your user email address
            'amount' => $amount, // amount will be paid in INR.
            'callback_url' => route('status') // callback URL
        ]);
        return $payment->receive();  // initiate a new payment
    }
    public function pay(Request $request)
    {
        $booking_id = $request->order_id;
        $booking = DB::table('vehicle_registers')->select('id','booking_no','total_amount', 'payment_status', 'customer_id','phone', 'created_at')->where('id', $booking_id)->orderBy('id', 'DESC')->first();
        
        if($booking){
            $payment = PaytmWallet::with('receive');
            $payment->prepare([
              'order' => $booking->id,
              'user' => $booking->customer_id,
              'mobile_number' => $booking->phone,
              'email' => $request->email,
              'amount' => $booking->total_amount,
              'callback_url' => route('status')
            ]);
            return $payment->receive();
        }else{
            return redirect(route('initiate.payment'))->with('message', "Your Order Id is wrong.");
        }
    }

    /**
     * Obtain the payment information.
     *
     * @return Object
     */
    public function paymentCallback()
    {
        $transaction = PaytmWallet::with('receive');
        
        $response = $transaction->response(); // To get raw response as array
        //Check out response parameters sent by paytm here -> http://paywithpaytm.com/developer/paytm_api_doc?target=interpreting-response-sent-by-paytm

        $order_id = $transaction->getOrderId(); // Get order id
        $transactionId = $transaction->getTransactionId(); // Get transaction id
        $responseMessage = $transaction->getResponseMessage(); //Get Response Message If Available
        $date   = date('Y-m-d H:i:s');
        if($transaction->isSuccessful()){
          //Transaction Successful
            $payment_status = 'success';
            DB::table('vehicle_registers')->where('id', '=', $order_id)->update(['responseMessage' => "".$responseMessage, 'transactionId' => $transactionId, 'payment_status' => $payment_status, 'updated_at' => $date]);
            return redirect(route('initiate.payment'))->with('message', "Your payment is successful. for #".$order_id);
        }else if($transaction->isFailed()){
          //Transaction Failed
            return redirect(route('initiate.payment'))->with('message', "Your payment is failed.");
        }else if($transaction->isOpen()){
          //Transaction Open/Processing
            return redirect(route('initiate.payment'))->with('message', "Your payment is processing.");
        }
        
        //get important parameters via public methods
        
    } 
}