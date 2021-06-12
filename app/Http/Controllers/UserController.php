<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use App\Models\UserDetail;
use App\Jobs\OtpDelete;
use App\Jobs\ResetPassword;
use App\Models\Useraddress;
use DB;
use Config;

//string generater
function randString($n) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}

class UserController extends Controller
{

    //
    public function register(Request $request){

        DB::beginTransaction();
        try{
            $fields = $request->validate([
                "email" => 'required|string|unique:users,email',
                "password" => 'required|string|confirmed',
                "mobile" => "",
                "userType" => "required|string"
            ]);
            $fields1 = $request->validate([
                "first_name" => 'required|string',
                "last_name" => 'required|string'
            ]);
            $user = User::create([
                "email" => $fields['email'],
                "password" => bcrypt($fields['password']),
                "mobile" => $fields['mobile'],
                "userType" => $fields['userType']
            ]);
            $user_detail = UserDetail::create([
                "first_name" =>  $fields1['first_name'],
                "last_name" =>  $fields1['last_name'],
                "user_id" => $user->id
            ]);

            $token = $user->createToken('myapptoken')->plainTextToken;
            $reponse = [
                'user' => $user,
                "token" => $token,
            ];
            DB::commit();
            return response($reponse, 200);

        }catch(\Exception $e){
            DB::rollback();
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 500);
        }

    }

    public function logout(Request $request){
        auth()->user()->tokens()->delete();
        return response([
            'msg'=> "logged out",
        ],200);
    }

    public function login(Request $request){
        $fields = $request->validate([
            "email" => 'required|string',
            "password" => 'required|string'
        ]);

        $user = User::where('email',$fields['email'])->first();
        if(!$user || !Hash::check($fields['password'], $user->password)){
            return response([
                'message'=>"Invalid user details",
            ], 400);
        }
        $token = $user->createToken('myapptoken')->plainTextToken;
        $reponse = [
            'user' => $user,
            "token" => $token,
        ];
        return response($reponse, 200);
    }

    public function edit_user(Request $request){

        try {
            $user = User::find($request['user_id']);
            $userDetail = UserDetail::where('userId', $request['user_id'])->first();
            if($request['first_name'] != ''){
                $userDetail->first_name = $request['first_name'];
                $userDetail->save();
            }
            if($request['last_name'] != ''){
                $userDetail->last_name = $request['last_name'];
                $userDetail->save();
            }
            if($request['userType'] != ''){
                $user->userType = $request['userType'];
                $user->save();
            }
            if($request['mobile'] != ''){
                $user->mobile = $request['mobile'];
                $user->save();
            }
            if($request['additonal_email'] != ''){
                $userDetail->additonal_email = $request['additonal_email'];
                $userDetail->save();
            }
            if($request['additional_mobile'] != ''){
                $userDetail->additional_mobile = $request['additional_mobile'];
                $userDetail->save();
            }
            if($request['gender'] != ''){
                $userDetail->gender = $request['gender'];
                $userDetail->save();
            }


        } catch (\Throwable $th) {
            DB::rollback();
            // return $th;
            echo $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 500);
        }
    }

    public function update_password(Request $request){
        $fields = $request->validate([
            "oldPassword" => 'required|string',
            "newPassword" => 'required|string|confirmed',
        ]);
        $user = User::find($request['user_id']);

        if (Hash::check($user->password, $fields['newPassword'])){
            $reponse = [
                "statuscode" => 400,
                "message" => 'Password cannot be same as previous!',
            ];
            return response($reponse, 400);
        }
        $user->password = bcrypt($fields['newPassword']);
        $user->save();
        $reponse = [
            "statuscode" => 200,
            "message" => 'Password updated successfully!',
        ];
        return response($reponse, 200);
    }

    public function forgot_password(Request $request){
        $user = User::where('email', $request['email'])->first();
        //return $user;
        if($user == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'No user is registered with the email: ' . $request["email"],
            ];
            return response($reponse, 400);
        }
        $otp = randString(8);
        $user->otp = $otp;
        $user->save();
        $details = [
            'email' => $request["email"],
            'body' => 'Email: '.$request["email"].'<br> OTP: '.$otp.' <br>click on this link to reset password : '.Config::get('globeVar.frontEndUrl').'/forgot-password?useremail=asif.sayyed@momenttext.com'
        ];
        ResetPassword::dispatch($details)->delay(now()->addSeconds(2));
        $email = ([
            "email" => $request['email'],
        ]);
        OtpDelete::dispatch($email)->delay(now()->addMinutes(5));
        $reponse = [
            "statuscode" => 200,
            "message" => 'Email with otp is sent to your email id.',
        ];
        return response($reponse, 200);
    }

    public function reset_password(Request $request){
        $fields = $request->validate([
            'otp' => 'required|string',
            "newPassword" => 'required|string|confirmed',
            "email" => 'required|string',
        ]);
        $user = User::where('email', $fields['email'])->first();
        if($user->otp == ''){
            $reponse = [
                "statuscode" => 400,
                "message" => 'OTP Expired!',
            ];
            return response($reponse, 400);
        }
        if($user->otp != $fields['otp']){
            $reponse = [
                "statuscode" => 400,
                "message" => 'OTP Mismatched!',
            ];
            return response($reponse, 400);
        }
        $user->password = bcrypt($fields['newPassword']);
        $user->otp = '';
        $user->save();
        $reponse = [
            "statuscode" => 200,
            "message" => 'Password resetted successfully!',
        ];
        return response($reponse, 200);
    }

    public function add_address(Request $request){
        DB::beginTransaction();
        try {
            $fields = $request->validate([
                "contact_person" => 'required|string',
                "contact_number" => 'required|string',
                "pincode" => "required|string",
                "address" => "required|string",
                "city" => "required|string",
                "state" => "required|string",
                "userId" => "required|int"
            ]);

            $user = Useraddress::create([
                "contact_person" => $fields['contact_person'],
                "contact_phone" => $fields['contact_number'],
                "pincode" => $fields['pincode'],
                "address" => $fields['address'],
                "city" => $fields['city'],
                "state" => $fields['state'],
                "userId" => $fields['userId'],
            ]);
            if($request['landmark'] != ''){
                $user->landmark = $request['landmark'];
                $user->save();
            }
            if($request['alternate_number'] != ''){
                $user->alternate_number = $request['alternate_number'];
                $user->save();
            }
            if($request['address_type'] != ''){
                $user->address_type = $request['address_type'];
                $user->save();
            }
            if($request['isPrimary'] != ''){
                $user->isPrimary = $request['isPrimary'];
                $user->save();
            }

            $reponse = [
                'message' => 'Address Added Successfully',
                'Address' => $user,

            ];
            DB::commit();
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 500);
        }

    }

    public function edit_address(Request $request){
        DB::beginTransaction();
        $Address = $request->validate([
            'addressId'=>'required|int'
        ]);
        try {
            $address = Useraddress::find($request['addressId']);
            if($request['contact_person']){
                $address->contact_person = $request['contact_person'];
                $address->save();
            }
            if($request['contact_phone']){
                $address->contact_phone = $request['contact_phone'];
                $address->save();
            }
            if($request['pincode']){
                $address->pincode = $request['pincode'];
                $address->save();
            }
            if($request['address']){
                $address->address = $request['address'];
                $address->save();
            }
            if($request['city']){
                $address->city = $request['city'];
                $address->save();
            }
            if($request['state']){
                $address->state = $request['state'];
                $address->save();
            }
            if($request['landmark']){
                $address->landmark = $request['landmark'];
                $address->save();
            }
            if($request['alternate_number']){
                $address->alternate_number = $request['alternate_number'];
                $address->save();
            }
            if($request['address_type']){
                $address->address_type = $request['address_type'];
                $address->save();
            }
            if($request['isPrimary']){
                $address->isPrimary = $request['isPrimary'];
                $address->save();
            }
            $reponse = [
                'message' => 'Address Updated Successfully',
                'Address' => $address,

            ];
            DB::commit();
            return response($reponse, 200);
        } catch (\Throwable $th) {
            DB::rollback();
            return $th;
            $reponse = [
                "statuscode" => 500,
                "message" => 'Server Error!',
            ];
            return response($reponse, 500);
        }
    }

    public function list_address(Request $request){
        $Address = $request->validate([
            'userId'=>'required|int'
        ]);
        $address = Useraddress::where('userId', $request['userId'])->get();
        $reponse = [
            'message' => 'Addresses listed successfully',
            'AddressList' => $address,
        ];
        return response($reponse, 200);
    }
}
