<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wishlist;
use DB;


class WishlistController extends Controller
{
    public function add_to_wishlist(Request $request){
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
            $category = Wishlist::create([
                "product_id" => $fields['product_id'],
                "userId" => $fields['userId'],
            ]);
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Item Added to wishlist!',
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

    public function remove_from_wishlisth(Request $request){
        $fields = $request->validate([
            "wishlist_item_id" => 'required|int',
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
        $item = Wishlist::find($fields['wishlist_item_id']);
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
            "message" => 'Wishlist Item Removed successfully!',
        ];
        return response($reponse, 200);
    }

    public function remove_all_from_wishlisth(Request $request){
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
        $item = Wishlist::where('userId', $fields['userId'])->delete();
        if($item == 0){
            $reponse = [
                "statuscode" => 400,
                "message" => 'No records to delete!',
            ];
            return response($reponse, 200);
        }
        $reponse = [
            "statuscode" => 200,
            "message" => 'Wishlist Items Removed successfully!',
        ];
        return response($reponse, 200);
    }

    public function list_wishlist_items(Request $request){
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
        $listItem = Wishlist::leftJoin('products', function($join) {
            $join->on('wishlist.product_id', '=', 'products.id');
          })->where('wishlist.userId', $fields['userId'])->get();
        $list = [];
        //return $listItem;
        if(count($listItem) > 0){
            foreach($listItem as $item){
                $bol = false;
                if(count($list) == 0){
                    $item['count'] = 1;
                    array_push($list, $item);
                }else{
                    foreach($list as $it){
                        if($item->product_id == $it->product_id){
                            $it->count = $it->count + 1;
                            $bol = false;
                            break;
                        }else{
                            $bol = true;
                        }
                    }
                    if($bol){

                        $item['count'] = 1;
                        array_push($list, $item);
                    }
                }

            }
        }
        foreach($list as $li){
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
            $attributeList =  DB::SELECT(DB::raw('SELECT * FROM product_attritubes, attributes WHERE product_attritubes.attribute_id = attributes.id AND product_attritubes.product_id='.$li->id));
            $imageList = DB::SELECT(DB::raw('SELECT * FROM product_images, image_deck WHERE product_images.image_id = image_deck.id AND product_images.product_id='.$li->id));
            $li->setAttribute('attributes',$attributeList);
            $li->setAttribute('images',$imageList);
        }
        $reponse = [
            "statuscode" => 200,
            "message" => "Items listed successfully",
            "data" => $list,
        ];
        return response($reponse, 200);
    }
}
