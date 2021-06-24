<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ProductEnquiry;
use App\Models\ProductEnquiryList;
use App\Models\Product;
use App\Jobs\EnquiryMail;


use DB;


class OrderManagement extends Controller
{
    public function send_enquiry(Request $request){
        $fields = $request->validate([
            "product_id_list" => 'required|string',
            "name" => 'required|string',
            "message" => 'required|string',
            "email" => 'required|string'
        ]);
        DB::beginTransaction();
        try {
            $enquiry = ProductEnquiry::create([
                "name" => $fields['name'],
                "message" => $fields['message'],
                "email" => $fields['email']
            ]);
            if($request['phone'] != ''){
                $enquiry->phone = $request['phone'];
                $enquiry->save();
            }
            if($request['userId'] != ''){
                $user = User::find($request['userId']);
                if($user == null ||  $user == ''){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
                $enquiry->userId = $request['userId'];
                $enquiry->save();
            }
            DB::commit();
            $explode = explode(',', $fields['product_id_list']);
            $unique = array_unique($explode);
            foreach($unique as $it){
                if($it != ''){
                    $cont = 0;
                    for($i=0; $i<count($explode); $i++){
                        if(trim($explode[$i], ' ') == $it){
                            $cont++;
                        }
                    }
                    $checkPro = Product::find($it);
                    if($checkPro == ''){
                        DB::rollback();
                        ProductEnquiry::find($enquiry->id)->delete();
                        DB::commit();
                        $reponse = [
                            "statuscode" => 400,
                            "message" => 'Invalid Product ID!'. $it,
                        ];
                        return response($reponse, 200);
                    }
                    ProductEnquiryList::create([
                        'enquiry_id' => $enquiry->id,
                        'product_id' => $it,
                        'quantity' => $cont,
                    ]);
                }

            }
            $products = DB::SELECT(DB::raw('SELECT * FROM (SELECT products.id, products.userId, products.product_name, products.product_desc, products.product_price, products.product_price_range, productenquire_list.quantity FROM products, productenquiry, productenquire_list WHERE productenquiry.id=productenquire_list.enquiry_id AND productenquire_list.product_id = products.id AND products.id IN ('.implode(',',$explode).') AND productenquiry.id = '.$enquiry->id.') AS prods LEFT JOIN (SELECT image_deck.image_path, product_images.id AS imgid, product_images.product_id FROM image_deck, product_images WHERE product_images.image_id = image_deck.id) AS image ON prods.id = image.product_id'));
            $userList = array();
            DB::commit();

            foreach($products as $prod){
                array_push($userList, $prod->userId);
            }
            $userList = array_unique($userList);
            $details = [
                'clientEmail' => $request["email"],
                'adminEmail' => "sayyedasif444@gmail.com",
                'data' => $products,
                'usersList' => $userList,
            ];
            EnquiryMail::dispatch($details)->delay(now()->addSeconds(2));

            $reponse = [
                "statuscode" => 200,
                "message" => 'Enquiry Submitted Successfully!',
                "enquiry_id" => $enquiry->id,
            ];
            return response($reponse, 200);
        }catch (\Throwable $th) {
            DB::rollback();
            // return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }

    public function update_enquiry(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
            "enquiry_id" => 'required|int',
        ]);

        $user = User::find($fields['userId']);
        if($user == null ||  $user == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'Invalid user!',
            ];
            return response($reponse, 200);
        }
        if($user->userType == 'ADMIN')
    }
}
