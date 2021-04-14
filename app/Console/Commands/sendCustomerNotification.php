<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use DB;

class SendCustomerNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:customer_notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $customerNotify = DB::table('notifications')->where('is_sent', 0)->skip(0)->take(100)->get();
        if($customerNotify)
        {
            foreach($customerNotify as $row)
            {
                $title = $row->notification_title;
                $message = $row->notification_content;
                $customer_id = $row->customer_id;

                $optionBuilder = new OptionsBuilder();
                $optionBuilder->setTimeToLive(60*20);

                $image = "http://staging.ezeerides.com/uploads/logo/honda-activa.jpg";
                        
                $notificationBuilder = new PayloadNotificationBuilder($title);
                $notificationBuilder->setBody($message)->setIcon("xxxhdpi")->setImage($image)->setSound('default');
                
                $dataBuilder = new PayloadDataBuilder();
                $dataBuilder->addData(['title' => $title, 'content' => $message]);
                
                $option = $optionBuilder->build();

                $notification = $notificationBuilder->build();
                $data = $dataBuilder->build();

                $userDeviceRow = DB::table('customers')->where('id','=', $customer_id)->first();

                $tokenData = array($userDeviceRow->fcmToken);
                                    
                $downstreamResponse = FCM::sendTo($tokenData, $option, $notification, $data);
                                    
                $success = $downstreamResponse->numberSuccess();
                $fail = $downstreamResponse->numberFailure();
                $total = $downstreamResponse->numberModification();

                $date   = date('Y-m-d H:i:s');
                DB::table('notifications')->where('id', '=', $row->id)->update(['is_sent' => '1', 'updated_at' => $date]);
            }
        }
    }
}
