<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use DB;

class CartController extends Controller
{
    public function add_to_cart(Request $request){
        $fields = $request->validate([
            "product_id" => 'required|int',
            "userId" => 'required|int',
        ]);
        $user = User::find($fields['userId']);
        if($user == null ||  $user == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'Invalid user!',
            ];
            return response($reponse, 200);
        }
        DB::beginTransaction();
        try {
            $category = Cart::create([
                "product_id" => $fields['product_id'],
                "userId" => $fields['userId'],
            ]);
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Item Added to cart!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            // return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }

    public function remove_from_cart(Request $request){
        $fields = $request->validate([
            "cart_item_id" => 'required|int',
            "userId" => 'required|int',
        ]);
        $user = User::find($fields['userId']);
        if($user == null ||  $user == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'Invalid user!',
            ];
            return response($reponse, 200);
        }
        $item = Cart::find($fields['cart_item_id']);
        if($item == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'Invalid item id!',
            ];
            return response($reponse, 200);
        }
        $item->delete();
        $reponse = [
            "statuscode" => 200,
            "message" => 'Cart Item Removed successfully!',
        ];
        return response($reponse, 200);
    }

    public function remove_all_from_cart(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
        ]);
        $user = User::find($fields['userId']);
        if($user == null ||  $user == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'Invalid user!',
            ];
            return response($reponse, 200);
        }
        $item = Cart::where('userId', $fields['userId'])->delete();
        if($item == 0){
            $reponse = [
                "statuscode" => 400,
                "message" => 'No records to delete!',
            ];
            return response($reponse, 200);
        }
        $reponse = [
            "statuscode" => 200,
            "message" => 'Cart Items Removed successfully!',
        ];
        return response($reponse, 200);
    }

    public function list_cart_items(Request $request){

        $fields = $request->validate([
            "userId" => 'required|int',
            "product_id_list" => 'required|string',
        ]);
       // return array($fields['product_id_list']);
        $user = User::find($fields['userId']);
        if($user == null ||  $user == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'Invalid user!',
            ];
            return response($reponse, 200);
        }
        $listItem = Product::whereIn('id', explode(',', $fields['product_id_list']))->get();
        $list = [];
        $explode = explode(',', $fields['product_id_list']);

        foreach($listItem as $li){
           $cont = 0;
            for($i=0; $i<count($explode); $i++){
                if(trim($explode[$i], ' ') == $li->id){
                    //echo trim($explode[$i], ' ') . ' ' . $cont;
                   $cont++;
                }
            }

            if($li->isAvailable == 1 && $li->isStockable == 0){
                $li['stock_availabe'] = true;
            }else{
                $stockcount = DB::SELECT(DB::raw('SELECT SUM(number_of_items) AS total FROM stockitem WHERE product_id = '.$li->id));
                if($stockcount[0]->total > $li->count){
                    $li['stock_availabe'] = true;
                }else{
                    $li['stock_availabe'] = false;
                }
            }
            $li['count'] = $cont;
        }
        $reponse = [
            "statuscode" => 200,
            "message" => "Items listed successfully",
            "data" => $listItem,
        ];
        return response($reponse, 200);
    }
}
