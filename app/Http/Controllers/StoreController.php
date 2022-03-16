<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    //1. Send OTP
    //2. Verify OTP
    //3. Complete registration
    //4. Get store profile
    //5. Get store details
    //6. Get store content
    //7. Update store profile
    //8. Create a post
    //9. Delete a post
    //10. Update Store Location

    //1. Send Otp
	public function send_store_otp(Request $request){
		$phone=$request->phone;

		$user=Store::where('store_phone',$phone)->first();

		if(!$user){
			$userData=[];
			$userData['store_phone']=$phone;
			// $otp=$this->getOtp(4);
            $otp="1234";
			$userData['otp']=$otp;

			// $this->sendOtp($otp,$phone);

			$user=Store::create($userData);
		}else{
			// $otp=$this->getOtp(4);
            $otp="1234";
			$user->otp=$otp;
			// $this->sendOtp($otp,$phone);
			$user->save();
		}

		return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => null]);
	}


	public function sendOtp($otp,$number){
		$otpData=[];
		$otpData['variables_values']=$otp;
		$otpData['route']="otp";
		$otpData['numbers']=$number;

		$setting=Setting::first();
		$apiKey=$setting->sms_api;

		$data_string = json_encode($otpData);

			$ch = curl_init("https://www.fast2sms.com/dev/bulkV2");
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt(
				$ch,
				CURLOPT_HTTPHEADER,
				array(
					'authorization:'.$apiKey,
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string)			
					)
			);

			curl_exec($ch) . "\n";
			curl_close($ch);
	}

	//2. Verify Otp
	public function verify_store_otp(Request $request){
		$phone=$request->phone;
		$otp=$request->otp;
		$token=$request->token;

		$user=Store::where('store_phone',$phone)
					->where('otp',$otp)
					->first();

		if($user){
			$user->token=$token;
			$user->save();
			if(empty($user->name))
			return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $user]);
			else
			return response()->json(['status' => 'SUCCESS', 'code' => 'SC_02', 'data' => $user]);
		}

		return response()->json(['status' => 'SUCCESS', 'code' => 'FC_01', 'data' => null]);
					
	}

	function getOtp($n)
	{
		$characters = '0123456789';
		$randomString = '';

		for ($i = 0; $i < $n; $i++) {
			$index = rand(0, strlen($characters) - 1);
			$randomString .= $characters[$index];
		}

		return $randomString;
	}


    //3. Complete registration
    public function complete_store_profile(Request $request){

        $store_id=$request->store_id;
        $phone=$request->phone;
        $storeName=$request->store_name;

        $user=Store::where('store_phone',$phone)
                ->where('id',$store_id)
                ->first();

        if(!empty($user->store_name)){
            return response()->json(['status' => 'SUCCESS', 'code' => 'FC_01', 'data' => null]);
        }

        if($user){
        if ($request->hasFile('profile')) {
            $image = $request->file('profile');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/profile');
            $image->move($destinationPath, $name);

            $path = url('') . '/profile/' . $name;
            $user->store_profile=$path;
        }

            $user->store_name=$storeName;
            $user->store_address=$request->store_address;
            $user->mobile=$request->mobile;
            $user->lat=$request->lat;
            $user->lng=$request->lng;
            $user->store_location=$request->store_location;
            $user->save();
        }

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $user]);
    }


    //4. Get store profile
    public function get_store_profile($storeId){
        $store=Store::where('id',$storeId)
                    ->first();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $store]);        
    }


    //6. Get store content
    public function get_content_store($storeId){
        $posts=Post::where('store_id',$storeId)
                ->orderBy('created_at','DESC')
                ->get();
                
        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $posts]);            
    }

    
    //7. Update store profile
    public function update_store(Request $request){
        $store=Store::where('id',$request->store_id)
                    ->first();

        if($store){
            if ($request->hasFile('profile')) {
                $image = $request->file('profile');
                $name = time() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('/profile');
                $image->move($destinationPath, $name);
    
                $path = url('') . '/profile/' . $name;
                $store->store_profile=$path;
            }
    
                $store->store_name=$request->store_name;
                $store->store_address=$request->store_address;
                $store->mobile=$request->mobile;
                $store->lat=$request->lat;
                $store->lng=$request->lng;
                $store->store_location=$request->store_location;
                $store->save();
        }

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $store]);            
    }

    
    //8. Create a post
    public function create_post(Request $request){
        $postData=[];
        $postData['store_id']=$request->store_id;
        $postData['post_type']='VIDEO';
        $postData['post_path']=$request->post_path;
        $postData['file_name']='-';

        $post=Post::create($postData);

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $post]);            
    }

    //9. Delete a post
    public function delete_post($postId){
        $post=Post::where('id',$postId)
                ->delete();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => null]);                
    }

    //10. Update Store Location
    public function update_store_location(Request $request){
        $storeId=$request->store_id;
        $lat=$request->lat;
        $lng=$request->lng;
        $storeLocation=$request->store_location;

        $store=Store::where('id',$storeId)
                    ->first();

        if($store){
            $store->lat=$lat;
            $store->lng=$lng;
            $store->store_location=$storeLocation;

            $store->save();
        }

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $store]);                
    }

}
