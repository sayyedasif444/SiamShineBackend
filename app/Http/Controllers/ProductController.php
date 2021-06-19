<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use App\Models\Category;
use App\Models\Attributes;
use App\Models\Product;
use App\Models\ImageDeck;
use App\Models\ProductAttributes;
use App\Models\ProductImages;
use App\Models\ProductSupportingFile;
use App\Models\ProductCategory;
use App\Models\StockItem;
use DB;
use Config;


function randString($n) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}
class ProductController extends Controller
{
    //category management
    public function add_category(Request $request){
        $fields = $request->validate([
            "category_name" => 'required|string',
            "userId" => 'required|int',
        ]);

        $user = User::find($fields['userId']);
        if($user == null){
            if($user == null){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Invalid user!',
                ];
                return response($reponse, 200);
            }
        }
        if($user->userType != 'ADMIN' ||  $user == ''){
            if($user->userType != 'COMPANY-ADMIN'){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
            }
        }
        DB::beginTransaction();
        try {
            $category = Category::create([
                "categoryName" => $fields['category_name'],
            ]);
            if($request['category_desc'] != ''){
                $category->categoryDesc = $request['category_desc'];
                $category->save();
            }
            if($request['parent_id'] != '' || $request['parent_id'] != 'NONE'){
                $category->parent_id = $request['parent_id'];
                $category->save();
            }
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Category Added!',
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

    public function edit_category(Request $request){
        $fields = $request->validate([
            "category_id" => 'required|int',
            "userId" => 'required|int',
        ]);
        $user = User::find($request['userId']);
        if($user == null){
            if($user == null){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Invalid user!',
                ];
                return response($reponse, 200);
            }
        }
        if($user->userType != 'ADMIN' ||  $user == ''){
            if($user->userType != 'COMPANY-ADMIN'){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
            }
        }
        DB::beginTransaction();
        try {
            $category = Category::find($request['category_id']);
            if($request['category_desc'] != ''){
                $category->categoryDesc = $request['category_desc'];
                $category->save();
            }
            if($request['parent_id'] != ''){
                $category->parent_id = $request['parent_id'];
                if($request['parent_id'] == $category->id){
                    DB::rollback();
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'parent_id cannot be same as Category Id!',
                    ];
                    return response($reponse, 200);
                }
                $catlist = Category::where('parent_id', $request['category_id'])->get();

                if(count($catlist) > 0 ){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Parent Category cannot be assigned to children!',
                    ];
                    return response($reponse, 200);
                }
                $category->save();
            }
            if($request['category_name'] != ''){
                $category->categoryName = $request['category_name'];
                $category->save();
            }
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Category Updated Successfully!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }

    }

    public function list_category(Request $request){
        if($request['category_id'] == ''){
            $categoryList = Category::where('parent_id', null)->get();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Categories listed successfully!',
                "data" => $categoryList
            ];
            return response($reponse, 200);
        }else{
            $categoryList = Category::where('parent_id', $request['category_id'])->get();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Sub-categories listed successfully!',
                "data" => $categoryList
            ];
            return response($reponse, 200);
        }
    }

    public function list_category_by_id(Request $request){
        $fields = $request->validate([
            "category_id" => 'required|int',
        ]);
        $categoryList = Attributes::find($request['category_id']);
        $reponse = [
            "statuscode" => 200,
            "message" => 'Sub-categories listed successfully!',
            "data" => $categoryList
        ];
        return response($reponse, 200);
    }

    public function delete_category(Request $request){
        $request->validate([
            'userId'=>'required|int',
            'category_id'=> 'required|int'
        ]);
        $user = User::find($request['userId']);
        if($user == null){
            if($user == null){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Invalid user!',
                ];
                return response($reponse, 200);
            }
        }
        if($user->userType != 'ADMIN' ||  $user == ''){
            if($user->userType != 'COMPANY-ADMIN'){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
            }
        }
        $category = Category::find($request['category_id']);
        if($category == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'Category not found!',
            ];
            return response($reponse, 200);
        }
        Category::find($request['category_id'])->delete();
        $reponse = [
            "statuscode" => 200,
            "message" => 'Category deleted successfully!',
        ];
        return response($reponse, 200);
    }

    //add image to deck
    public function add_image(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
        ]);
        DB::beginTransaction();
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            if(count($request->all()) > 1){
                //return $request->all();
                foreach($request->file() as $item => $val){
                    $file = $val;
                    $fileext =  $val->extension();
                    $allowed = array('jpeg', 'png', 'jpg', 'pdf');
                    if(!in_array(strtolower($fileext), $allowed)){
                        $reponse = [
                            "statuscode" => 400,
                            "message" => $fileext. ' is an Invalid format, please provide PNG|JPG|JPEG|pdf only.',
                        ];
                        return response($reponse, 200);
                    }
                    $filetostore = time() + rand(10,100).time().'.'.$fileext;
                    $path = $val->move('storage/uploads/', $filetostore);
                    $name = $val->getClientOriginalName();
                    $imgDeck = ImageDeck::create([
                        'image_path'=>'/storage/uploads/'.$filetostore,
                        'image_name'=>$name,
                    ]);
                }
                DB::commit();
                $reponse = [
                    "statuscode" => 200,
                    "message" => 'Files Added Successfully!',
                ];
                return response($reponse, 200);
            }else{
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'No file(s) found!',
                ];
                return response($reponse, 200);
            }

        } catch (\Throwable $th) {
            DB::rollback();
            //return $th;
             $reponse = [
                 "statuscode" => 500,
                 "message" => 'Server Error!',
             ];
             return response($reponse, 200);
        }

    }

    //attribute management
    public function add_attribute(Request $request){
        $fields = $request->validate([
            "attribute_name" => 'required|string',
            "userId" => 'required|int',
        ]);
        DB::beginTransaction();
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $attribute = Attributes::create([
                "attribute_name" => $fields['attribute_name'],
            ]);
            if($request['attribute_desc'] != ''){
                $attribute->attribute_desc = $request['attribute_desc'];
                $attribute->save();
            }
            if($request['parent_id'] != '' || $request['parent_id'] != 'NONE'){
                $attribute->parent_id = $request['parent_id'];
                $attribute->save();
            }
            if($request['image_path'] != ''){
                //
                $file = $request['image_path'];
                $fileext =  $request['image_path']->extension();
                $allowed = array('jpeg', 'png', 'jpg');
                if(!in_array(strtolower($fileext), $allowed)){
                    DB::rollback();
                     $reponse = [
                         "statuscode" => 400,
                         "message" => $fileext. ' is an Invalid image format, please provide PNG|JPG|JPEG only.',
                     ];
                     return response($reponse, 200);
                }
                $filetostore = time() + rand(10,100).time().'.'.$fileext;
                $path = $request['image_path']->move('storage/uploads/', $filetostore);
                $attribute->image_path = '/storage/uploads/' . $filetostore;
                $attribute->save();
            }

            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Attribute Added!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
           //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }

    public function edit_attribute(Request $request){
        $fields = $request->validate([
            "attribute_id" => 'required|int',
            "userId" => 'required|int',
        ]);

        DB::beginTransaction();
        try {
            $user = User::find($request['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $attribute = Attributes::find($request['attribute_id']);
            if($request['attribute_desc'] != ''){
                $attribute->attribute_desc = $request['attribute_desc'];
                $attribute->save();
            }
            if($request['parent_id'] != ''){
                $attribute->parent_id = $request['parent_id'];
                if($request['parent_id'] == $attribute->id){
                    DB::rollback();
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'parent_id cannot be same as Category Id!',
                    ];
                    return response($reponse, 200);
                }
                $attlist = Category::where('parent_id', $request['category_id'])->get();

                if(count($attlist) > 0 ){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Parent Category cannot be assigned to children!',
                    ];
                    return response($reponse, 200);
                }
                $attribute->save();
            }
            if($request['attribute_name'] != ''){
                $attribute->attribute_name = $request['attribute_name'];
                $attribute->save();
            }
            if($request['image_path'] != ''){
                $file = $request['image_path'];
                $fileext =  $request['image_path']->extension();
                $allowed = array('jpeg', 'png', 'jpg');
                if(!in_array(strtolower($fileext), $allowed)){
                    DB::rollback();
                     $reponse = [
                         "statuscode" => 400,
                         "message" => $fileext. ' is an Invalid image format, please provide PNG|JPG|JPEG only.',
                     ];
                     return response($reponse, 200);
                }
                $filetostore = time() + rand(10,100).time().'.'.$fileext;
                $path = $request['image_path']->move('storage/uploads/', $filetostore);
                $attribute->image_path = '/storage/uploads/' . $filetostore;
                $attribute->save();
            }
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Attribute Updated Successfully!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }

    }

    public function list_attribute(Request $request){
        if($request['attribute_id'] == ''){
            $attributeList = Attributes::where('parent_id', null)->get();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Attributes listed successfully!',
                "data" => $attributeList
            ];
            return response($reponse, 200);
        }else{
            $attributeList = Attributes::where('parent_id', $request['attribute_id'])->get();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Sub-categories listed successfully!',
                "data" => $attributeList
            ];
            return response($reponse, 200);
        }
    }
    public function list_attribute_by_id(Request $request){
        $fields = $request->validate([
            "attribute_id" => 'required|int',
        ]);
        $attributeList = Attributes::find($request['attribute_id']);
        $reponse = [
            "statuscode" => 200,
            "message" => 'Sub-categories listed successfully!',
            "data" => $attributeList
        ];
        return response($reponse, 200);
    }

    public function delete_attribute(Request $request){
        $request->validate([
            'userId'=>'required|int',
            'attribute_id'=> 'required|int'
        ]);
        $user = User::find($request['userId']);
        if($user == null){
            if($user == null){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Invalid user!',
                ];
                return response($reponse, 200);
            }
        }
        if($user->userType != 'ADMIN' ||  $user == ''){
            if($user->userType != 'COMPANY-ADMIN'){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
            }
        }
        $attribute = Attributes::find($request['attribute_id']);
        if($attribute == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'Attribute not found!',
            ];
            return response($reponse, 200);
        }
        Attributes::find($request['attribute_id'])->delete();
        $reponse = [
            "statuscode" => 200,
            "message" => 'Attribute deleted successfully!',
        ];
        return response($reponse, 200);
    }

    public function add_product(Request $request){
        $fields = $request->validate([
            "product_name" => 'required|string',
            "userId" => 'required|int',
        ]);
        DB::beginTransaction();
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $product = Product::create([
                "product_name" => $fields['product_name'],
                "userId" => $fields['userId'],
                "product_u_id" => randString(12) . time(),
            ]);
            if($request['product_desc'] != ''){
                $product->product_desc = $request['product_desc'];
                $product->save();
            }
            if($request['product_price'] != ''){
                $product->product_price = $request['product_price'];
                $product->save();
            }
            if($request['product_price_range'] != ''){
                $product->product_price_range = $request['product_price_range'];
                $product->save();
            }
            if($request['isStockable'] == 1){
                $product->isStockable = $request['isStockable'];
                $product->save();
            }
            if($request['isAvailable'] == 0){
                $product->isAvailable = $request['isAvailable'];
                $product->save();
            }
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Product Added Successfully!',
                "product_id" => $product->id,
                "productId" => $product->product_u_id,
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
           //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }

    public function add_stock(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
            "product_id" => 'required|int',
            "number_of_items" => 'required|int',
        ]);
        DB::beginTransaction();
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $prod = Product::find($fields['product_id']);
            if($prod == ''){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Invalid Product Id!',
                ];
                return response($reponse, 200);
            }
            if($prod->isStockable == 0){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Stock entry is marked as non-stockable, please change that and try again!',
                ];
                return response($reponse, 200);
            }
            StockItem::create([
                'product_id' => $fields['product_id'],
                'number_of_items' => $fields['number_of_items'],
            ]);
            $reponse = [
                "statuscode" => 200,
                "message" => 'Stock Added Successfully!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
           //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }

    }

    public function edit_stock(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
            "stock_id" => 'required|int',
            "number_of_items" => 'required|int',
        ]);
        $user = User::find($fields['userId']);
        if($user == null){
            if($user == null){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Invalid user!',
                ];
                return response($reponse, 200);
            }
        }
        if($user->userType != 'ADMIN' ||  $user == ''){
            if($user->userType != 'COMPANY-ADMIN'){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
            }
        }
        $stock = StockItem::find($fields['stock_id']);
        if($stock == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'Invalid Stock Id!',
            ];
            return response($reponse, 200);
        }
        $stock->number_of_items = $fields['number_of_items'];
        $stock->save();
        $reponse = [
            "statuscode" => 200,
            "message" => 'Stock updated Successfully!',
        ];
        return response($reponse, 200);
    }

    public function delete_stock(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
            "stock_id" => 'required|int',
        ]);
        $user = User::find($fields['userId']);
        if($user == null){
            if($user == null){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Invalid user!',
                ];
                return response($reponse, 200);
            }
        }
        if($user->userType != 'ADMIN' ||  $user == ''){
            if($user->userType != 'COMPANY-ADMIN'){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
            }
        }
        $stock = StockItem::find($fields['stock_id']);
        if($stock != ''){
            $stock->delete();
        }else{
            $reponse = [
                "statuscode" => 400,
                "message" => 'Invalid Stock Id!',
            ];
            return response($reponse, 200);
        }
        $reponse = [
            "statuscode" => 200,
            "message" => 'Stock deleted Successfully!',
        ];
        return response($reponse, 200);
    }


    public function add_product_image(Request $request){
        $fields = $request->validate([
            "upload_type" => 'required|string',
            "userId" => 'required|int',
            "product_id" => 'required|int',
        ]);
        DB::beginTransaction();
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            if($fields['upload_type'] == "UPLOADED-FILE"){
                if(count($request->all()) > 3){
                    //return $request->all();
                    foreach($request->all() as $item => $val){
                        if($item != 'upload_type' && $item != 'userId' &&  $item != 'product_id'){
                            ProductImages::create([
                                "image_id"=>$val,
                                "product_id"=>$request['product_id'],
                            ]);
                        }
                    }
                    DB::commit();
                    $reponse = [
                        "statuscode" => 200,
                        "message" => 'Files Added to product Successfully!',
                    ];
                }
                return response($reponse, 200);
            }elseif($fields['upload_type'] == "NEW-FILE"){
                foreach($request->file() as $item => $val){
                    $file = $val;
                    $fileext =  $val->extension();
                    $allowed = array('jpeg', 'png', 'jpg');
                    if(!in_array(strtolower($fileext), $allowed)){
                        $reponse = [
                            "statuscode" => 400,
                            "message" => $fileext. ' is an Invalid image format, please provide PNG|JPG|JPEG only.',
                        ];
                        return response($reponse, 200);
                    }
                    $filetostore = time() + rand(10,100).time().'.'.$fileext;
                    $path = $val->move('storage/uploads/', $filetostore);
                    $name = $val->getClientOriginalName();
                    $imgDeck = ImageDeck::create([
                        'image_path'=>'/storage/uploads/'.$filetostore,
                        'image_name'=>$name,
                    ]);
                    DB::commit();
                    ProductImages::create([
                        "image_id"=>$imgDeck->id,
                        "product_id"=>$request['product_id'],
                    ]);
                    DB::commit();
                }
                $reponse = [
                    "statuscode" => 200,
                    "message" => 'Files Added to product Successfully!',
                ];
                return response($reponse, 200);
            }else{
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Invalid Upload!',
                ];
                return response($reponse, 200);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            //return $th;
             $reponse = [
                 "statuscode" => 500,
                 "message" => 'Server Error!',
             ];
             return response($reponse, 200);
        }

    }

    public function add_product_attribute(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
            "product_id" => 'required|int',
        ]);
        DB::beginTransaction();
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $arr = [];
            foreach($request->all() as $item => $val){
                if($item != 'userId' &&  $item != 'product_id'){
                    if(in_array($val, $arr)){
                        DB::rollback();
                        $reponse = [
                            "statuscode" => 400,
                            "message" => 'Duplicate Attribute Id found!',
                        ];
                        return response($reponse, 200);
                    }
                    $attribute = ProductAttributes::where('product_id', $fields['product_id'])->get();
                    $attrList = Attributes::where('id', $val)->get();
                    if(count($attrList) == 0){
                        DB::rollback();
                        $reponse = [
                            "statuscode" => 400,
                            "message" => 'Attribute with given attribute id does not exists!',
                        ];
                        return response($reponse, 200);
                    }
                    foreach($attribute as $it){
                        if($it->attribute_id == $val){
                            DB::rollback();
                            $reponse = [
                                "statuscode" => 200,
                                "message" => 'Product Attribute Already Exists!',
                            ];
                            return response($reponse, 200);
                        }
                    }
                    array_push($arr,$val);
                    $patt = ProductAttributes::create([
                        "attribute_id"=>$val,
                        "product_id"=>$request['product_id'],
                    ]);

                }
            }
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Product Attribute Added Successfully!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }

    public function edit_product(Request $request){
        $fields = $request->validate([
            "product_id" => 'required|int',
            "userId" => 'required|int',
        ]);
        DB::beginTransaction();
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $product = Product::find($fields['product_id']);
            if($request['product_name'] != ''){
                $product->product_name = $request['product_name'];
                $product->save();
            }
            if($request['product_desc'] != ''){
                $product->product_desc = $request['product_desc'];
                $product->save();
            }
            if($request['product_price'] != ''){
                $product->product_price = $request['product_price'];
                $product->save();
            }
            if($request['product_price_range'] != ''){
                $product->product_price_range = $request['product_price_range'];
                $product->save();
            }
            if($request['isStockable'] != ''){
                $product->isStockable = $request['isStockable'];
                $product->save();
            }
            if($request['isAvailable'] != ''){
                $product->isAvailable = $request['isAvailable'];
                $product->save();
            }
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Product Updated Successfully!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
           //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }

    public function delete_product_image(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
            "product_image_id" => 'required|int',
        ]);
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $prodImage = ProductImages::find($fields['product_image_id']);
            if($prodImage == null){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Image with given id not found!',
                ];
                return response($reponse, 200);
            }
            $prodImage->delete();
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Image Deleted Successfully!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }

    public function delete_product_attribute(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
            "product_attribute_id" => 'required|int',
        ]);
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $prodAttr = ProductAttributes::find($fields['product_attribute_id']);
            if($prodAttr == null){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Attribute with given id not found!',
                ];
                return response($reponse, 200);
            }
            $prodAttr->delete();
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Attribute Deleted Successfully!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }

    public function list_product(Request $request){
        $productList = Product::all();
        $list = [];
        foreach($productList as $item){
            $attributeList =  DB::SELECT(DB::raw('SELECT * FROM product_attritubes, attributes WHERE product_attritubes.attribute_id = attributes.id AND product_attritubes.product_id='.$item->id));
            $imageList = DB::SELECT(DB::raw('SELECT * FROM product_images, image_deck WHERE product_images.image_id = image_deck.id AND product_images.product_id='.$item->id));
            $catList = DB::SELECT(DB::raw('SELECT * FROM product_category, category WHERE product_category.category_id = category.id AND product_category.product_id='.$item->id));
            if($item->isStockable == 0){
                if($item->isAvailable == 1){
                    $item->setAttribute('available_in_stock',true);
                    $item->setAttribute('is_stockable',false);
                    $item->setAttribute('availabe_stock',null);
                    $item->setAttribute('stock_history',[]);
                }
                else{
                    $item->setAttribute('available_in_stock',false);
                    $item->setAttribute('is_stockable',false);
                    $item->setAttribute('availabe_stock',null);
                    $item->setAttribute('stock_history',[]);
                }
            }else{
                $item->setAttribute('available_in_stock',true);
                $item->setAttribute('is_stockable',true);
                $stockcount = DB::SELECT(DB::raw('SELECT SUM(number_of_items) AS total FROM stockitem WHERE product_id = '.$item->id));
                $item->setAttribute('availabe_stock',$stockcount[0]->total);
                $stock = DB::SELECT(DB::raw('SELECT * From stockitem WHERE  product_id = '.$item->id));
                $item->setAttribute('stock_history',$stock);
            }
            $item->setAttribute('attribute',$attributeList);
            $item->setAttribute('image',$imageList);
            $item->setAttribute('category',$catList);
            array_push($list,$item);
        }

        $reponse = [
            "statuscode" => 200,
            "message" => 'Product Listed Successfully!',
            'data' => $list,
        ];
        return response($reponse, 200);

    }

    public function list_product_by_user(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
        ]);
        $productList = Product::where('userId', $request['userId'])->get();
        $list = [];
        foreach($productList as $item){
            $attributeList =  DB::SELECT(DB::raw('SELECT * FROM product_attritubes, attributes WHERE product_attritubes.attribute_id = attributes.id AND product_attritubes.product_id='.$item->id));
            $imageList = DB::SELECT(DB::raw('SELECT * FROM product_images, image_deck WHERE product_images.image_id = image_deck.id AND product_images.product_id='.$item->id));
            $catList = DB::SELECT(DB::raw('SELECT * FROM product_category, category WHERE product_category.category_id = category.id AND product_category.product_id='.$item->id));
            if($item->isStockable == 0){
                if($item->isAvailable == 1){
                    $item->setAttribute('available_in_stock',true);
                    $item->setAttribute('is_stockable',false);
                    $item->setAttribute('availabe_stock',null);
                    $item->setAttribute('stock_history',[]);
                }
                else{
                    $item->setAttribute('available_in_stock',false);
                    $item->setAttribute('is_stockable',false);
                    $item->setAttribute('availabe_stock',null);
                    $item->setAttribute('stock_history',[]);
                }
            }else{
                $item->setAttribute('available_in_stock',true);
                $item->setAttribute('is_stockable',true);
                $stockcount = DB::SELECT(DB::raw('SELECT SUM(number_of_items) AS total FROM stockitem WHERE product_id = '.$item->id));
                $item->setAttribute('availabe_stock',$stockcount[0]->total);
                $stock = DB::SELECT(DB::raw('SELECT * From stockitem WHERE  product_id = '.$item->id));
                $item->setAttribute('stock_history',$stock);
            }
            $item->setAttribute('attribute',$attributeList);
            $item->setAttribute('image',$imageList);
            $item->setAttribute('category',$catList);
            array_push($list,$item);
        }

        $reponse = [
            "statuscode" => 200,
            "message" => 'Product Listed Successfully!',
            'data' => $list,
        ];
        return response($reponse, 200);

    }

    public function list_product_by_id(Request $request){
        $fields = $request->validate([
            "product_id" => 'required|int',
        ]);
        $productList = Product::find($fields['product_id']);
        $attributeList =  DB::SELECT(DB::raw('SELECT * FROM product_attritubes, attributes WHERE product_attritubes.attribute_id = attributes.id AND product_attritubes.product_id='.$productList->id));
        $imageList = DB::SELECT(DB::raw('SELECT * FROM product_images, image_deck WHERE product_images.image_id = image_deck.id AND product_images.product_id='.$productList->id));
        $catList = DB::SELECT(DB::raw('SELECT * FROM product_category, category WHERE product_category.category_id = category.id AND product_category.product_id='.$productList->id));
        if($productList->isStockable == 0){
            if($productList->isAvailable == 1){
                $productList->setAttribute('available_in_stock',true);
                $productList->setAttribute('is_stockable',false);
                $productList->setAttribute('availabe_stock',null);
                $productList->setAttribute('stock_history',[]);
            }
            else{
                $productList->setAttribute('available_in_stock',false);
                $productList->setAttribute('is_stockable',false);
                $productList->setAttribute('availabe_stock',null);
                $productList->setAttribute('stock_history',[]);
            }
        }else{
            $productList->setAttribute('available_in_stock',true);
            $productList->setAttribute('is_stockable',true);
            $stockcount = DB::SELECT(DB::raw('SELECT SUM(number_of_items) AS total FROM stockitem WHERE product_id = '.$productList->id));
            $productList->setAttribute('availabe_stock',$stockcount[0]->total);
            $stock = DB::SELECT(DB::raw('SELECT * From stockitem WHERE  product_id = '.$productList->id));
            $productList->setAttribute('stock_history',$stock);
        }
        $number_of_variant = DB::SELECT(DB::raw('SELECT COUNT(*) as cnt FROM products WHERE product_u_id = "'.$productList->product_u_id.'" AND id != '. $productList->id));
        $productList->setAttribute('number_of_variants',$number_of_variant[0]->cnt);
        $productVariants = array();
        if($number_of_variant[0]->cnt == 0){
            $productVariants = new \stdClass();
        }else{
            $no_var = DB::SELECT(DB::raw('SELECT DISTINCT(products.id) as id, products.product_price_range, products.product_price FROM products WHERE product_u_id = "'.$productList->product_u_id.'" AND id != '.$productList->id));
            $cntVar = 1;
            foreach($no_var as $item){
                $prodVariants = DB::SELECT(DB::raw('SELECT products.id,  attributes.id as attribute_id, attributes.attribute_name, attributes.attribute_desc, attributes.image_path, attributes.parent_id, attributes.created_at, attributes.updated_at, product_attritubes.product_id FROM attributes, product_attritubes, products WHERE product_attritubes.attribute_id = attributes.id AND products.id = product_attritubes.product_id AND products.id = '. $item->id));
                if($prodVariants != ''){
                    $prodVarCont = array();
                    $prodVarCont['product_id'] = $item->id;
                    $prodVarCont['product_price'] = $item->product_price;
                    $prodVarCont['product_price_range'] = $item->product_price_range;
                    $prodVarCont['data'] = $prodVariants;
                    $productVariants['variant' . $cntVar] = $prodVarCont;
                    $cntVar++;
                }
            }
        }
        $productList->setAttribute('attributes',$attributeList);
        $productList->setAttribute('variants',$productVariants);
        $productList->setAttribute('images',$imageList);
        $productList->setAttribute('category',$catList);
        $reponse = [
            "statuscode" => 200,
            "message" => 'Specific Product Listed Successfully!',
            'data' => $productList,
        ];
        return response($reponse, 200);

    }

    public function delete_product(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
            "product_id" => 'required|int',
        ]);
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $product = Product::find($fields['product_id']);
            if($product == null){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Product with given id not found!',
                ];
                return response($reponse, 200);
            }
            $product->delete();
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Product Deleted Successfully!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }

    public function add_product_category(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
            "product_id" => 'required|int',
        ]);
        DB::beginTransaction();
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $arr = [];
            foreach($request->all() as $item => $val){
                if($item != 'userId' &&  $item != 'product_id'){
                    if(in_array($val, $arr)){
                        DB::rollback();
                        $reponse = [
                            "statuscode" => 400,
                            "message" => 'Duplicate Attribute Id found!',
                        ];
                        return response($reponse, 200);
                    }
                    $category = ProductCategory::where('product_id', $fields['product_id'])->get();
                    $catList = Category::where('id', $val)->get();
                    if(count($catList) == 0){
                        DB::rollback();
                        $reponse = [
                            "statuscode" => 400,
                            "message" => 'Category with given Category id does not exists!',
                        ];
                        return response($reponse, 200);
                    }
                    foreach($category as $it){
                        if($it->category_id == $val){
                            DB::rollback();
                            $reponse = [
                                "statuscode" => 200,
                                "message" => 'Product Category Already Exists!',
                            ];
                            return response($reponse, 200);
                        }
                    }
                    array_push($arr,$val);
                    ProductCategory::create([
                        "category_id"=>$val,
                        "product_id"=>$request['product_id'],
                    ]);
                }
            }
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Product Category Added Successfully!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }

    public function delete_product_category(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
            "product_category_id" => 'required|int',
        ]);
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $prodCat = ProductCategory::find($fields['product_category_id']);
            if($prodCat == null){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Category with given id not found!',
                ];
                return response($reponse, 200);
            }
            $prodCat->delete();
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Category Deleted Successfully!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }

    //add supporting file
    public function add_product_supporting_file(Request $request){
        $fields = $request->validate([
            "upload_type" => 'required|string',
            "userId" => 'required|int',
            "product_id" => 'required|int',
        ]);
        DB::beginTransaction();
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            if($fields['upload_type'] == "UPLOADED-FILE"){
                if(count($request->all()) > 3){
                    //return $request->all();
                    foreach($request->all() as $item => $val){
                        if($item != 'upload_type' && $item != 'userId' &&  $item != 'product_id'){
                            ProductSupportingFile::create([
                                "image_id"=>$val,
                                "product_id"=>$request['product_id'],
                            ]);
                        }
                    }
                    DB::commit();
                    $reponse = [
                        "statuscode" => 200,
                        "message" => 'Files Added to product Successfully!',
                    ];
                }
                return response($reponse, 200);
            }elseif($fields['upload_type'] == "NEW-FILE"){
                foreach($request->file() as $item => $val){
                    $file = $val;
                    $fileext =  $val->extension();
                    $allowed = array('jpeg', 'png', 'jpg', 'pdf');
                    if(!in_array(strtolower($fileext), $allowed)){
                        $reponse = [
                            "statuscode" => 400,
                            "message" => $fileext. ' is an Invalid image format, please provide PNG|JPG|JPEG only.',
                        ];
                        return response($reponse, 200);
                    }
                    $filetostore = time() + rand(10,100).time().'.'.$fileext;
                    $path = $val->move('storage/uploads/', $filetostore);
                    $name = $val->getClientOriginalName();
                    $imgDeck = ImageDeck::create([
                        'image_path'=>'/storage/uploads/'.$filetostore,
                        'image_name'=>$name,
                    ]);
                    DB::commit();
                    ProductSupportingFile::create([
                        "image_id"=>$imgDeck->id,
                        "product_id"=>$request['product_id'],
                    ]);
                    DB::commit();
                }
                $reponse = [
                    "statuscode" => 200,
                    "message" => 'Files Added to product Successfully!',
                ];
                return response($reponse, 200);
            }else{
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Invalid Upload!',
                ];
                return response($reponse, 200);
            }
            if(count($request->file()) > 0){
                foreach($request->file() as $item => $val){
                    $file = $val;
                    $fileext =  $val->extension();
                    $allowed = array('jpeg', 'png', 'jpg', 'pdf');
                    if(!in_array(strtolower($fileext), $allowed)){
                        $reponse = [
                            "statuscode" => 400,
                            "message" => $fileext. ' is an Invalid image format, please provide PNG|JPG|JPEG|PDF only.',
                        ];
                        return response($reponse, 200);
                    }
                    $filetostore = time() + rand(10,100).time().'.'.$fileext;
                    $path = $val->move('storage/uploads/', $filetostore);
                    $name = $val->getClientOriginalName();
                    $imgDeck = ImageDeck::create([
                        'image_path'=>'/storage/uploads/'.$filetostore,
                        'image_name'=>$name,
                    ]);
                    DB::commit();
                    ProductSupportingFile::create([
                        "image_id"=>$imgDeck->id,
                        "product_id"=>$request['product_id'],
                    ]);
                    DB::commit();
                }
                $reponse = [
                    "statuscode" => 200,
                    "message" => 'Files Added to product Successfully!',
                ];
                return response($reponse, 200);
            }else{
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'No files to Upload!',
                ];
                return response($reponse, 200);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            return $th;
             $reponse = [
                 "statuscode" => 500,
                 "message" => 'Server Error!',
             ];
             return response($reponse, 200);
        }
    }

    public function delete_product_supporting_file(Request $request){
        $fields = $request->validate([
            "userId" => 'required|int',
            "product_supporting_file_id" => 'required|int',
        ]);
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $prodImage = ProductSupportingFile::find($fields['product_supporting_file_id']);
            if($prodImage == null){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'File with given id not found!',
                ];
                return response($reponse, 200);
            }
            $prodImage->delete();
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'File Deleted Successfully!',
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }

    //add product variant
    public function add_product_variant(Request $request){
        $fields = $request->validate([
            "productId" => 'required|string',
            "product_name" => 'required|string',
            "userId" => 'required|int',
        ]);
        DB::beginTransaction();
        try {
            $user = User::find($fields['userId']);
            if($user == null){
                if($user == null){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'Invalid user!',
                    ];
                    return response($reponse, 200);
                }
            }
            if($user->userType != 'ADMIN' ||  $user == ''){
                if($user->userType != 'COMPANY-ADMIN'){
                    $reponse = [
                        "statuscode" => 400,
                        "message" => 'User not authorized.',
                    ];
                    return response($reponse, 200);
                }
            }
            $product = Product::create([
                "product_name" => $fields['product_name'],
                "userId" => $fields['userId'],
                "product_u_id" => $fields['productId'],
            ]);
            if($request['product_desc'] != ''){
                $product->product_desc = $request['product_desc'];
                $product->save();
            }
            if($request['product_price'] != ''){
                $product->product_price = $request['product_price'];
                $product->save();
            }
            if($request['product_price_range'] != ''){
                $product->product_price_range = $request['product_price_range'];
                $product->save();
            }
            if($request['isStockable'] == 1){
                $product->isStockable = $request['isStockable'];
                $product->save();
            }
            if($request['isAvailable'] == 0){
                $product->isAvailable = $request['isAvailable'];
                $product->save();
            }
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Product Added Successfully!',
                "product_id" => $product->id,
            ];
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
           //return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 200);
        }
    }


}
