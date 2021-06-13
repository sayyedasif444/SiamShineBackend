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
        if($user->userType != 'ADMIN' || $user == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'User not authorized.',
            ];
            return response($reponse, 200);
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
        if($user->userType != 'ADMIN' || $user == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'User not authorized.',
            ];
            return response($reponse, 200);
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
        if($user->userType != 'ADMIN' || $user == ''){

            $reponse = [
                "statuscode" => 400,
                "message" => 'User not authorized.',
            ];
            return response($reponse, 200);
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
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
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
                        'image_path'=>$filetostore,
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
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
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
            if($user->userType != 'ADMIN' || $user == ''){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
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
        if($user->userType != 'ADMIN' || $user == ''){

            $reponse = [
                "statuscode" => 400,
                "message" => 'User not authorized.',
            ];
            return response($reponse, 200);
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
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
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
            if($request['category_id'] != ''){
                $product->category_id = $request['category_id'];
                $product->save();
            }

            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Product Added Successfully!',
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
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
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
                        'image_path'=>$filetostore,
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
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
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
                    ProductAttributes::create([
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
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
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
            if($request['category_id'] != ''){
                $product->category_id = $request['category_id'];
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
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
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
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'User not authorized.',
                ];
                return response($reponse, 200);
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
            $attributeList = ProductAttributes::where('product_id', $item->id)->get();
            $imageList = ProductImages::where('product_id', $item->id)->get();
            $item->setAttribute('attribute',$attributeList);
            $item->setAttribute('image',$imageList);
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
        $attributeList = ProductAttributes::where('product_id', $productList->id)->get();
        $imageList = ProductImages::where('product_id', $productList->id)->get();
        $productList->setAttribute('attribute',$attributeList);
        $productList->setAttribute('image',$imageList);
        $reponse = [
            "statuscode" => 200,
            "message" => 'Specific Product Listed Successfully!',
            'data' => $productList,
        ];
        return response($reponse, 200);

    }
}
