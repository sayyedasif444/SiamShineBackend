<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ImageDeck;

use Config;
use DB;

class Operational extends Controller
{
     //add deck images
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

            if(count($request->file()) > 0){
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
    public function delete_image(Request $request){
       // return Config::get('globeVar.backEndUrl');
        $fields = $request->validate([
            "userId" => 'required|int',
            "image_id" => 'required|int'
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
            $ImageDeck = ImageDeck::find($fields['image_id']);
            if($ImageDeck == null){
                $reponse = [
                    "statuscode" => 400,
                    "message" => 'Image with given id not found!',
                ];
                return response($reponse, 200);
            }

            $file_path = Config::get('globeVar.backEndUrl').$ImageDeck->image_path;
            if(\File::exists(public_path($ImageDeck->image_path))){
                \File::delete(public_path($ImageDeck->image_path));
            }
            $ImageDeck->delete();
            DB::commit();
            $reponse = [
                "statuscode" => 200,
                "message" => 'Image Deleted Successfully!',
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
}
