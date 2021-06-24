<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class EnquiryMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $details;
    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            \Mail::to($this->details['adminEmail'])->send(new \App\Mail\EnquiryMailAdmin($this->details['data']));
            \Mail::to($this->details['clientEmail'])->send(new \App\Mail\EnquiryMailClient($this->details['data']));
            for($i = 0; $i<count($this->details['usersList']); $i++){
                $products = [];
                foreach($this->details['data'] as $prod){
                    if($prod->userId == $this->details['usersList'][$i]){
                        array_push($products, $prod);
                    }
                }
                $userz = User::find($this->details['usersList'][$i]);
                \Mail::to($userz->email)->send(new \App\Mail\EnquiryMailCompany($products));
            }
        } catch (\Throwable $th) {
            echo $th;
        }

    }
}
