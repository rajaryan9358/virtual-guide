<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    //1. Send OTP
    //2. Verify OTP
    //3. Complete registration
    //4. Get user profile
    //5. Get Nearby store videos
    //6. Get Nearby stores
    //7. Get video comments
    //8. Get store details
    //9. Get store content
    //10. Like video
    //11. Add comment

    
    //1. Send Otp
	public function send_otp(Request $request){
		$phone=$request->phone;

		$user=User::where('phone',$phone)->first();

		if(!$user){
			$userData=[];
			$userData['phone']=$phone;
			// $otp=$this->getOtp(4);
			$otp="1234";
			$userData['otp']=$otp;

			// $this->sendOtp($otp,$phone);

			$user=User::create($userData);
		}else{
			$otp=$this->getOtp(4);
			// $user->otp=$otp;
			$otp="1234";
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
	public function verify_otp(Request $request){
		$phone=$request->phone;
		$otp=$request->otp;

		$user=User::where('phone',$phone)
					->where('otp',$otp)
					->first();

		if($user){
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
    public function complete_profile(Request $request){

        $phone=$request->phone;
        $otp=$request->otp;
        $name=$request->name;

        $user=User::where('phone',$phone)
                ->where('otp',$otp)
                ->first();

        if($user){
            $user->name=$name;
            $user->save();
        }

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $user]);
    }

    
    //4. Get user profile
    public function get_user_profile($userId){
        $user=User::where('id',$userId)
                ->first();

        return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $user]);
    }


    //5. Get Nearby store videos
    public function get_nearby_store_videos(Request $request){
		$latitude=$request->lat;
		$longitude=$request->lng;
		$radius=$request->radius;
		$userId=$request->user_id;

        $sql="SELECT stores.id,stores.store_name,stores.mobile,stores.store_profile,stores.store_address,stores.lat,stores.lng,posts.post_path, posts.id as post_id,
		round(( 6371 * 
			ACOS( 
				COS( RADIANS( stores.lat ) ) * 
				COS( RADIANS( $latitude ) ) * 
				COS( RADIANS( $longitude ) - 
				RADIANS( stores.lng ) ) + 
				SIN( RADIANS( stores.lat ) ) * 
				SIN( RADIANS( $latitude) ) 
			) 
		),2)
		AS distance, (SELECT count(user_id) FROM post_likes WHERE post_likes.post_id=posts.id) AS like_count,(SELECT count(user_id) FROM post_comments WHERE post_comments.post_id=posts.id) AS comment_count,COALESCE((SELECT post_likes.post_id FROM post_likes WHERE post_likes.post_id=posts.id AND post_likes.user_id=".$userId."),'-1') AS liked_video FROM stores INNER JOIN posts ON stores.id=posts.store_id HAVING distance <= $radius";
		
		$stores=DB::select($sql);

		return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $stores]);
    }

	
	//6. Get Nearby stores
	public function get_nearby_stores(Request $request){
		$latitude=$request->lat;
		$longitude=$request->lng;
		$radius=$request->radius;

        $sql="SELECT id,store_name,store_address,store_profile,mobile,lat,lng, 
		round(( 6371 * 
			ACOS( 
				COS( RADIANS( lat ) ) * 
				COS( RADIANS( $latitude ) ) * 
				COS( RADIANS( $longitude ) - 
				RADIANS( lng ) ) + 
				SIN( RADIANS( lat ) ) * 
				SIN( RADIANS( $latitude) ) 
			) 
		),2)
		AS distance FROM stores HAVING distance <= $radius";

		$stores=DB::select($sql);

		return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $stores]);
	}


	//7. Get Video comments
	public function get_video_comments($postId){
		$postComments=PostComment::leftJoin('users','post_comments.user_id','users.id')
								->select('post_comments.*','users.name')
								->where('post_comments.post_id',$postId)
								->orderBy('post_comments.created_at','ASC')
								->get();

		return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $postComments]);					
	}

	//8. Get store details
	public function get_store_details($storeId){
		$store=Store::where('id',$storeId)
					->first();

		return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $store]);								
	}

	
	//9. Get store content
	public function get_store_content($storeId){
		$posts=Post::where('store_id',$storeId)
					->orderBy('created_at','DESC')
					->get();

		return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $posts]);								
	}

	
	//10. Like video
	public function like_video(Request $request){
		$postId=$request->post_id;
		$userId=$request->user_id;

		$postLike=PostLike::where('post_id',$postId)
						->where('user_id',$userId)
						->first();
		
		if($postLike){
			$postLike->delete();
			return response()->json(['status' => 'SUCCESS', 'code' => 'SC_02', 'data' => $postLike]);								
		}else{
			$postLikeData=[];
			$postLikeData['post_id']=$postId;
			$postLikeData['user_id']=$userId;

			$postLike=PostLike::create($postLikeData);
			return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $postLike]);								
		}

	}


	//11. Add comment
	public function add_comment(Request $request){
		$postId=$request->post_id;
		$userId=$request->user_id;
		$comment=$request->comment;

		$postCommentData=[];
		$postCommentData['post_id']=$postId;
		$postCommentData['user_id']=$userId;
		$postCommentData['comment']=$comment;

		$postComment=PostComment::create($postCommentData);

		return response()->json(['status' => 'SUCCESS', 'code' => 'SC_01', 'data' => $postComment]);								
	}

}
