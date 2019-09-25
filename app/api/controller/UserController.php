<?php
/*
用户接口

*/ 


namespace app\api\controller;

use think\Db; // /thinkphp/library/think/Loader.php
use cmf\controller\HomeBaseController;
class UserController extends HomeBaseController
{
	public function _initialize()
    {

        parent::_initialize();
    }


/*
新建家庭圈
http://localhost/shouhuan/public/api/user/group_new?token=44&name=家庭圈111
{"errno":0,"error":"Success.","id":"6"}
https://shouhuan.taoyt.cn/api/user/group_new?token=oRXGB4kGkb6x_Mppt64_BnI9l9wI&name=家庭圈222

*/ 
	public function group_new(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$f_name=isset($_REQUEST['name'])?$_REQUEST['name']:'';
		$jtq_family= Db::name('jtq_family');
		$jtq_family_member= Db::name('jtq_family_member');
		$jtq_user= Db::name('jtq_user');

		if($token==''||$f_name==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();

		}

		$arr=array();
		$arr['name']=$f_name;
		$arr['token']=$token;
		$arr['create_time']=time();
		$f_id=$jtq_family->insertGetId($arr);
		$arr_m=array();
		$arr_m['f_id']=$f_id;
		$arr_m['f_name']=$f_name;
		$arr_m['token']=$token;
		$arr_m['u_id']=$f_info['id'];
		$arr_m['u_name']=$f_info['alias'];
		$arr_m['master']=1;
		$arr_m['create_time']=time();
		$jtq_family_member->insert($arr_m);
		$up_d=array();
		$up_d['errno']=0;
		$up_d['error']="Success.";
		$up_d['id']=$f_id;
		print_r(json_encode($up_d));
		exit();


	}

/*
获得家庭圈列表
http://localhost/shouhuan/public/api/user/group_get_list?token=44
{"errno":0, "error":"Success.", "list":[{"name":"\u5bb6\u5ead\u5708111","id":6}]}
https://shouhuan.taoyt.cn/api/user/group_get_list?token=oRXGB4kGkb6x_Mppt64_BnI9l9wI

*/ 
	public function group_get_list(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$jtq_family= Db::name('jtq_family');
		$jtq_family_member= Db::name('jtq_family_member');
		$jtq_user= Db::name('jtq_user');
		if($token==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();

		}

		// 查询用户所在的家庭圈  多个 所以用 select
		$get_f_m=$jtq_family_member->where(array('token'=>$token))->order("f_id desc")->select()->toArray();

		
		if(!$get_f_m){
			$err=array();
			$err['errno']=105;
			$err['error']="该用户不属于任何家庭圈！";
			$err=json_encode($err);
			echo($err);
			exit();
		}else{
			$lists=array();
			$lists['errno']=0;
			$lists['error']="Success.";
			foreach ($get_f_m as $key => $val) {
				$info=array();
				$info['name']=$val['f_name'];
				$info['id']=$val['f_id'];
				$lists['list'][]=$info;
			}
			print_r(json_encode($lists));
			exit();
		}

	}

/*
添加家庭圈成员 （与邀请为同一协议）
http://localhost/shouhuan/public/api/user/group_add_member?token=44&id=6&uid=5
https://shouhuan.taoyt.cn/api/user/group_add_member?token=44&id=1001&uid=5

*/ 

	public function group_add_member(){

		//圈主的 token   其他人没有权限
		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//家庭圈编号
		$uid=isset($_REQUEST['uid'])?$_REQUEST['uid']:''; //被邀请的用户 id
		$jtq_family= Db::name('jtq_family');
		$jtq_family_member= Db::name('jtq_family_member');
		$jtq_user= Db::name('jtq_user');
		if($token==''||$id==''||$uid==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}

		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();

		}
		$f_f=$jtq_family->where(array('id'=>$id))->find();
		if(!$f_f){
			$err=array();
			$err['errno']=105;
			$err['error']="该家庭圈不存在！";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		if($f_f['token']!=$token){
			$err=array();
			$err['errno']=105;
			$err['error']="只有家庭圈主才可添加或邀请！";
			$err=json_encode($err);
			echo($err);
			exit();
		}

		// 被邀请的用户
		$u_info=$jtq_user->where(array('id'=>$uid))->find();
		if(!$u_info){
			$err=array();
			$err['errno']=105;
			$err['error']="被邀请的用户不存在！";
			$err=json_encode($err);
			echo($err);
			exit();
		}else{
			$find_m=$jtq_family_member->where(array('f_id'=>$id,'u_id'=>$uid))->find();
			if(!$find_m){
				$arr_m=array();
				$arr_m['f_id']=$f_f['id'];
				$arr_m['f_name']=$f_f['name'];
				$arr_m['token']=$u_info['token'];
				$arr_m['u_id']=$u_info['id'];
				$arr_m['u_name']=$u_info['alias'];
				$arr_m['master']=0;
				$arr_m['create_time']=time();
				$jtq_family_member->insert($arr_m);
			}

			$up_d=array();
			$up_d['errno']=0;
			$up_d['error']="Success.";
			print_r(json_encode($up_d));

		}

	}


/*
 删除家庭圈成员
http://localhost/shouhuan/public/api/user/group_del_member?token=44&id=6&uid=5

1、根据 家庭圈编号 找圈主，与传过来的token对比，不是圈主则 根据 被邀请的用户uid 搜索 ，对比token，还不一样则不能删除，因为他人没有权限删除他人，只能删除自己
2、若是圈主，则根据 被邀请的用户uid 搜索 ，对比token， 若还一样 则 不能删除，因为圈主不能删除自己（只能解散或删除他人）
3、 若2中 对比不一样，则是圈主删除她人 可以删除

https://shouhuan.taoyt.cn/api/user/group_del_member?token=44&id=6&uid=5

*/ 

 	public function group_del_member(){

		//圈主的 token   其他人没有权限
		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';//家庭圈编号
		$uid=isset($_REQUEST['uid'])?$_REQUEST['uid']:''; //被邀请的用户 id
		$jtq_family= Db::name('jtq_family');
		$jtq_family_member= Db::name('jtq_family_member');
		$jtq_user= Db::name('jtq_user');

		if($token==''||$id==''||$uid==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}

		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();

		}

		// 找圈主
		$flag=0;
		$error=0;
		$f_f=$jtq_family->where(array('id'=>$id))->find();
		$f_user=$jtq_user->where(array('id'=>$uid))->find();
		if($f_f['token']!=$token){// 不是圈主，
			// 是自己
			if($f_user['token']==$token){
				$flag=1;
				$error="不是圈主,是自己";
			}else{
				$flag=0;
				$error="您不是圈主,不能删除其他人！";
			}

		}else{// 是圈主，

			if($f_user['token']==$token){
				$flag=0;
				$error="圈主不能删除自己！";
			}else{
				$flag=1;
				$error="是圈主,不是自己";
			}

		}


		if($flag){
			// 用户不存在 即根据uid 找不到 则也当做已删除
			$del=$jtq_family_member->where(array('u_id'=>$uid,'f_id'=>$id))->delete();
			$up_d=array();
			$up_d['errno']=0;
			$up_d['error']="Success.";
			print_r(json_encode($up_d));
			exit();
		}else{

			$err=array();
			$err['errno']=105;
			$err['error']=$error;
			$err=json_encode($err);
			echo($err);
			exit();

		}


 	}

/*
成员 上报位置信息

http://localhost/shouhuan/public/api/user/group_location?token=44&uid=7&location={"longitude":114.0728118956,"latitude": 22.5239589827 }

{"errno":0, "error":"Success." }
https://shouhuan.taoyt.cn/api/user/group_location?token=44&uid=7&location={"longitude":114.0728118956,"latitude": 22.5239589827 }


*/ 
	public function group_location(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$uid=isset($_REQUEST['uid'])?$_REQUEST['uid']:''; // 用户 uid
		$location=isset($_REQUEST['location'])?$_REQUEST['location']:'';

		$jtq_user= Db::name('jtq_user');
		$jtq_user_location= Db::name('jtq_user_location');

		if($token==''||$location==''||$uid==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}

		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info||$f_info['id']!=$uid){
			$err=array();
			$err['errno']=102;
			$err['error']="token 错误 或 用户不存在！";
			$err=json_encode($err);
			echo($err);
			exit();

		} 


		$arr_location=array();
		$arr_location['token']=$token;
		$arr_location['u_id']=$uid;
		$arr_location['location']=$location;
		$arr_location['add_time']=time();

		$add=$jtq_user_location->insert($arr_location);

		$up_d=array();
		$up_d['errno']=0;
		$up_d['error']="Success.";
		print_r(json_encode($up_d));
		exit();


	}

/*
解散家庭圈
http://localhost/shouhuan/public/api/user/group_cancel?token=44&id=7
{"errno":0, "error":"Success." }

https://shouhuan.taoyt.cn/api/user/group_cancel?token=44&id=7

*/

	public function group_cancel(){

		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:''; // 家庭圈编号
		$jtq_user= Db::name('jtq_user');
		$jtq_family= Db::name('jtq_family');
		$jtq_family_member= Db::name('jtq_family_member');

		if($token==''||$id==''){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();
		}
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();

		} 

		$f_f=$jtq_family->where(array("id"=>$id))->find();
		// 圈子不存在 因为已经删除,所以直接返回成功
		if(!$f_f){
			$up_d=array();
			$up_d['errno']=0;
			$up_d['error']="Success.";
			print_r(json_encode($up_d));
			exit();
		}else if($f_f['token']!=$token){
			// 圈子存在但不是 圈主
			$err=array();
			$err['errno']=105;
			$err['error']="您不是圈主,不能解散该圈";
			$err=json_encode($err);
			echo($err);
			exit();

		}


		$del=$jtq_family->where(array("id"=>$id))->delete();
		$del_m=$jtq_family_member->where(array("f_id"=>$id))->delete();

		$up_d=array();
		$up_d['errno']=0;
		$up_d['error']="Success.";
		print_r(json_encode($up_d));
		exit();


	}


/*
获得家庭圈个人数据
http://localhost/shouhuan/public/api/user/group_get_member?token=44&id=7
{"errno":0, "error":"Success.", "id":10001,"name":"我的家","creator_uid":5,"creator_name":"小明","list":[{"id":6,"name":"张三","location":{"longitude":114.0728118956,"latitude":22.5239589827 }}]}
*/ 

	public function group_get_member(){

		//  用户的token  不一定是圈主的token,所以 不能用token进行搜索
		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$id=isset($_REQUEST['id'])?$_REQUEST['id']:''; // 家庭圈编号
		$jtq_user= Db::name('jtq_user');
		$jtq_family= Db::name('jtq_family');
		$jtq_family_member= Db::name('jtq_family_member');
		$jtq_user_location= Db::name('jtq_user_location');

		if($token==''||$id==''){
			$err=array();
			$err['errno']=105;
			$err['error']="Lack of parameters!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		}
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);
			exit();
		} 

		
		$f_fam=$jtq_family->where(array("id"=>$id))->find();
		if(!$f_fam){
			$err=array();
			$err['errno']=105;
			$err['error']="圈子不存在";
			$err=json_encode($err);
			echo($err);
			exit();
		}

		$check=$jtq_family_member->where(array("f_id"=>$id,'token'=>$token))->find();
		if(!$check){
			$err=array();
			$err['errno']=105;
			$err['error']="您不在该圈子内！";
			$err=json_encode($err);
			echo($err);
			exit();
		}


		// 获取圈主信息
		$f_user=$jtq_user->where(array("token"=>$f_fam['token']))->find();

		$data=array();
		$data['errno']=0;
		$data['error']="Success.";
		$data['id']=$f_fam['id'];
		$data['name']=$f_fam['name'];
		$data['creator_uid']=$f_user['id'];
		$data['creator_name']=$f_user['alias'];
		$data['list']=[];

		$f_f_m=$jtq_family_member->where(array("f_id"=>$id))->select();

		

		foreach ($f_f_m as $key => $val) {
			# code...
			$info=array();
			$u_id=$val['u_id'];
			$u_info=$jtq_user_location->where(array('u_id'=>$u_id))->order("add_time desc")->find();
			$info['id']=intval($val['u_id']);
			$info['name']=$val['u_name'];
			$info['location']=stripslashes($u_info['location']);
			$info['location']=json_decode($info['location']);
			$data['list'][]=$info;

		}

		print_r(json_encode($data));
		exit();

	}








/***********************************************************************/ 
// 微信小程序 测试
//  http://localhost/shouhuan/public/api/user/getOpenId

    public function getOpenId(){
    	echo phpinfo();
    	exit();

		$code=isset($_REQUEST['code'])?$_REQUEST:'';

		// 自己小程序的 原来的
		// $APPID='wx3531a61bddbb5b85';
		// $AppSecret='6f21a8ff514cc572ee251e7b8d1d09d3';

		// 自己小程序的 最新 张弓给的 权限在张弓那
		$APPID='wx3531a61bddbb5b85';
		$AppSecret='960e9d0e218a834fd62e6de3258f0e68';

		/*  张弓给的 测试
AppID(小程序ID) wxc4496a8566d3b5f6
原始ID
gh_3adbfed534bf
密钥 AppSecret: 0c97d560c2b1ef941a963bdd51ea45bc
		*/ 


		$code='aaa';
		$url="https://api.weixin.qq.com/sns/jscode2session?appid=".$APPID."&secret=".$AppSecret."&js_code=".$code."&grant_type=authorization_code";
		$arr=$this->vget($url);
		$arr=json_decode($arr,true); //openid
			print_r($arr);

    }




// qweewq321  faa0c791b4902e7dc69a19dec36f0b2d  
// 123456 e10adc3949ba59abbe56e057f20f883e  892245718@qq.com
// http://localhost/shouhuan/public/api/user/register.php?email=892245718@qq.com&password=e10adc3949ba59abbe56e057f20f883e
// https://shouhuan.taoyt.cn/api/user/register.php?email=892245718@qq.com&password=e10adc3949ba59abbe56e057f20f883e


/*
1.3.1注册
http://39.108.219.58:8808/api/user/register.php?email=892245718@qq.com&password=4297F44B13955235245B2497399D7A93


{"errno":0,"error":"Success.","id":100109,"token":"MTAwMTA5fHx8ZjIzMWE2MTJiNTkxMzRlM2E4OWE4ZWFhYTI5NWIwNTVkZWQ4NDcxY3x8fDIwMTktMDgtMjAgMDA6Mjc6Mjc="}
{"errno":100,"error":"User already exists!!!"}
{"errno":105,"error":"Lack of parameters!!!"}


http://39.108.219.58:8808/api/user/register.php?email=text1@qq.com&password=4297F44B13955235245B2497399D7A93
MTAwMTExfHx8NzYxN2M1OWQ1MmUwMDJjYzQxMzdhMWIwNWYxZmVlODAzZDAxZDFiYXx8fDIwMTktMDgtMjIgMDY6Mzg6MDM
*/ 
// 暂时不用
	public function register(){

		$jtq_user= Db::name('jtq_user');
		$arr=array();

		$data=(isset($_REQUEST['email'])&&isset($_REQUEST['password']))?$_REQUEST:'';
		if(!$data){
			$err=array();
			$err['errno']=105;
			$err['error']="Lack of parameters!!!";
			$err=json_encode($err);
			echo($err);
		}else{
			$user=$data['email'];
			if(!filter_var($user,FILTER_VALIDATE_EMAIL)){
				$err=array();
				$err['errno']=101;
				$err['error']="Error in mailbox format!!!";
				$err=json_encode($err);
				echo($err);
				exit();

			}
			$g_info=$jtq_user->where(array('user'=>$user))->find();
			if($g_info){
				$err=array();
				$err['errno']=100;
				$err['error']="User already exists!!!";
				$err=json_encode($err);
				echo($err);

			}else{

				$arr['user']=$user;
				// 密码传过来就是md5的值
				$arr['password']=$data['password'];
				$arr['createtime']=time();
				// $arr['token']="qweewq321";

				$arr['token']=$this->get_token($user,$data['password']);

				$info=$jtq_user->insertGetId($arr);
				if($info){
					$err=array();
					$err['errno']=0;
					$err['error']="Success.";
					$err['id']=$info;
					$err=json_encode($err);
					echo($err);
				}else{
					$err=array();
					$err['errno']=105;
					$err['error']="Lack of parameters!!! 2";
					$err=json_encode($err);
					echo($err);

				}
			}
			
		}
		

	}


// 微信小程序 登录
// http://localhost/shouhuan/public/api/user/login
	public function login(){

		// 张弓测试的小程序
		// $APPID='wxc4496a8566d3b5f6';
		// $AppSecret='0c97d560c2b1ef941a963bdd51ea45bc';

		// // 自己小程序的 最新 张弓给的
		// $APPID='wx3531a61bddbb5b85';
		// $AppSecret='960e9d0e218a834fd62e6de3258f0e68';


		// $code=isset($_REQUEST['code'])?$_REQUEST['code']:'';
		// $url="https://api.weixin.qq.com/sns/jscode2session?appid=".$APPID."&secret=".$AppSecret."&js_code=".$code."&grant_type=authorization_code";

		// $arr=$this->vget($url);
		// $arr=json_decode($arr,true); //openid

		$arr['openid']="yuyi";//测试

		$jtq_user= Db::name('jtq_user');
		$jtq_device=Db::name('jtq_device');

		if(!isset($arr['openid'])){
			$err=array();
			$err['errno']=105;
			$err['error']="openid 获取失败";
			$err=json_encode($err);
			echo($err);
			exit();
			

		}else{
			$openid=$arr['openid'];
			$where=array();
			$where['token']=$openid;
			$g_info=$jtq_user->where($where)->find();
			if(!$g_info){
				$add=array();
				$add['token']=$openid;				
				$add['createtime']=time();		
				$info=$jtq_user->insertGetId($add);
				$g_info=$jtq_user->where($where)->find();

			}
			$err=array();
			$err['errno']=0;
			$err['error']="Success.";
			$err['id']=$g_info['id'];
			$err['token']=$g_info['token'];
			$err['phone']=$g_info['phone'];
			$err['email']=$g_info['email'];
			$err['sex']=$g_info['sex'];
			$err['avatar']=$g_info['avatar'];
			$err['birthday']=$g_info['birthday'];
			$err['alias']=$g_info['alias'];
			$err['height']=$g_info['height'];
			$err['weight']=$g_info['weight'];
			// $err['active_id']=$g_info['active_id'];
			$active_id=$g_info['active_id'];
			if($g_info['active_id']){
				$info_def=$jtq_device->where(array('id'=>$active_id))->find();
				$err['active_id']=$info_def['id'];
				$err['active_name']=$info_def['name'];
				$err['active_device']=$info_def['device'];
				$err['active_alias']=$info_def['alias'];
			}else{
				$err['active_id']=null;
				$err['active_name']=null;
				$err['active_device']=null;
				$err['active_alias']=null;
			}
			$device_id = explode("__", $g_info['device_id']);
			$err['device_id']=$device_id;
			$err=json_encode($err);
			echo($err);
			exit();

		}

	}


	public function vget($url){
	  $info=curl_init();
	  curl_setopt($info,CURLOPT_RETURNTRANSFER,true);
	  curl_setopt($info,CURLOPT_HEADER,0);
	  curl_setopt($info,CURLOPT_NOBODY,0);
	  curl_setopt($info,CURLOPT_SSL_VERIFYPEER, false);
	  curl_setopt($info,CURLOPT_SSL_VERIFYHOST, false);
	  curl_setopt($info,CURLOPT_URL,$url);
	  $output= curl_exec($info);
	  curl_close($info);
	  return $output;
	}





// 1.3.2登陆
// http://localhost/shouhuan/public/api/user/login.php?user=892245718@qq.com&password=e10adc3949ba59abbe56e057f20f883e
// d4e3a98e5ed367ece367ec80dee11023


// http://39.108.219.58:8808/api/user/login.php?user=892245718@qq.com&password=4297F44B13955235245B2497399D7A93

// 每次登录token都变化
// MTAwMTEwfHx8YWI5ZTdkNjdkZmYxNzkxNjY0MDBlZmYyMGI4MjRmZjA0MWE0ZmVkM3x8fDIwMTktMDgtMjYgMDU6MzU6MTA=

// https://shouhuan.taoyt.cn/api/user/login.php?user=892245718@qq.com&password=e10adc3949ba59abbe56e057f20f883e
// d4e3a98e5ed367ece367ec80dee11023

	public function login2(){

		$jtq_user= Db::name('jtq_user');
		$arr=array();
		$data=(isset($_REQUEST['user'])&&isset($_REQUEST['password']))?$_REQUEST:'';
		if(!$data){
			$err=array();
			$err['errno']=105;
			$err['error']="Lack of parameters!!!";
			$err=json_encode($err);
			echo($err);
		}else{

			$where=array();
			$where['user']=$data['user'];
			$where['password']=$data['password'];
			$g_info=$jtq_user->where($where)->find();
			if(!$g_info){
				$err=array();
				$err['errno']=111;
				$err['error']="Username or password error!!!";
				$err=json_encode($err);
				echo($err);

			}else{
				$err=array();
				$err['errno']=0;
				$err['error']="Success.";
				$err['id']=$g_info['id'];
				$err['token']=$g_info['token'];
				$err=json_encode($err);
				echo($err);

			}
		}

	}
	

// 1.3.3文件上传
// http://localhost/shouhuan/public/api/file/upload.php?token=d4e3a98e5ed367ece367ec80dee11023
// d4e3a98e5ed367ece367ec80dee11023

// http://39.108.219.58:8808/api/file/upload.html
// http://39.108.219.58:8808/api/file/upload.php?token=MTAwMTEwfHx8YWI5ZTdkNjdkZmYxNzkxNjY0MDBlZmYyMGI4MjRmZjA0MWE0ZmVkM3x8fDIwMTktMDgtMjYgMDU6MzU6MTA=
// {"errno":102,"error":"Invalid token!!!"}
// {"errno":105,"error":"Lack of parameters!!!"}
// {"url":"http:\/\/39.108.219.58:8808\/upload\/2019\/08\/26\/100110_20190826054131423.png","errno":0,"error":"Success."}

// {"errno":0,"error":"Success.","url":"http:\/\/localhost\/shouhuan\/public\/upload\/jtq_avatar\/4_1566798215.jpg"}

	public function upload(){

		$jtq_user= Db::name('jtq_user');
		$arr=array();
		$data=isset($_REQUEST['token'])?$_REQUEST:'';

		if(!$data){
			$err=array();
			$err['errno']=105;
			$err['error']="Lack of parameters!!!";
			$err=json_encode($err);
			echo($err);
		}else{

			$where=array();
			$where['token']=$data['token'];
			$g_info=$jtq_user->where($where)->find();
			if(!$g_info){
				$err=array();
				$err['errno']=102;
				$err['error']="Invalid token!!!";
				$err=json_encode($err);
				echo($err);
			}else{

				if(!isset($_FILES['file'])){
					$err=array();
					$err['errno']=103;
					$err['error']="Invalid parameter!!!";
					$err=json_encode($err);
					echo($err);
					exit();

				}
				$imgname = $_FILES['file']['name'];
			    $tmp = $_FILES['file']['tmp_name'];
			    $ss = explode('.',$imgname);
			    $ss =array_reverse($ss);

			    $avatar='upload/jtq_avatar/'.$g_info['id'].'_'.time().'.'.$ss[0];
				$filepath = CMF_ROOT.'public/'. $avatar;

				// // 同名的覆盖，不用写删除,因为加了time所以需要手动删除
				// $g_avatar=substr($g_info['avatar'],strripos($g_info['avatar'],"public/"));
				// $g_avatar=CMF_ROOT. $g_avatar;
				// if(is_file($g_avatar))
				// {
				// 	$u1=unlink($g_avatar);
				// }

			    if(move_uploaded_file($tmp,$filepath)){
			        $arr_up=array();

			        $arr_up['avatar']= 'http://localhost/shouhuan/public/'.$avatar;
			        // 不更新
			        // $up_info= $jtq_user->where(array('id'=>$g_info['id']))->update($arr_up);
			        $err=array();
					$err['errno']=0;
					$err['error']="Success.";
					$err['url']=  $arr_up['avatar'];
					$err=json_encode($err);
					echo($err);

			    }else{
			    	echo("上传失败！");
			    }

			}

		}

	}



/*
1.3.4个人信息
http://localhost/shouhuan/public/api/user/info.php?token=d4e3a98e5ed367ece367ec80dee11023&avatar=http://xxx.xxx.com/123.png&alias=jack&sex=1&birthday=1991-01-01&height=170&weight=62

https://localhost/shouhuan/public/upload/jtq_avatar/5_1568167430.jpg

http://39.108.219.58:8808/api/user/info.php?token=MTAwMTEwfHx8YWI5ZTdkNjdkZmYxNzkxNjY0MDBlZmYyMGI4MjRmZjA0MWE0ZmVkM3x8fDIwMTktMDgtMjYgMDU6MzU6MTA=&avatar=http://xxx.xxx.com/123.png&alias=jack&sex=1&birthday=1991-01-01&height=170&weight=62
https://shouhuan.taoyt.cn/api/user/info.php?token=d4e3a98e5ed367ece367ec80dee11023&avatar=https:\/\/shouhuan.taoyt.cn\/upload\/jtq_avatar\/1_1566872629.jpg&alias=jack&sex=1&birthday=1991-01-01&height=170&weight=62
*/ 
	public function info(){
		
		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		//性别；0：保密/未设置；1：男性；2：女性；可选；
		$sex=isset($_REQUEST['sex'])?$_REQUEST['sex']:'0';
		//1991-01-01
		$birthday=isset($_REQUEST['birthday'])?$_REQUEST['birthday']:'';
		$avatar=isset($_REQUEST['avatar'])?$_REQUEST['avatar']:'';
		// 呢称；可选；
		$alias=isset($_REQUEST['alias'])?$_REQUEST['alias']:'';
		$height=isset($_REQUEST['height'])?$_REQUEST['height']:'';
		$weight=isset($_REQUEST['weight'])?$_REQUEST['weight']:'';

		$email=isset($_REQUEST['email'])?$_REQUEST['email']:'';
		$phone=isset($_REQUEST['phone'])?$_REQUEST['phone']:'';


		$jtq_user= Db::name('jtq_user');
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);

		}else{

			$arr_up=array();
			if($sex){ $arr_up['sex']=$sex; }
			if($birthday){ $arr_up['birthday']=$birthday; }
			if($avatar){ 
				$arr_up['avatar']=$avatar; 
			}
			if($alias){ $arr_up['alias']=$alias; }
			if($height){ $arr_up['height']=$height; }
			if($weight){ $arr_up['weight']=$weight; }

			if($email){ $arr_up['email']=$email; }
			if($phone){ $arr_up['phone']=$phone; }

			$up_info= $jtq_user->where(array('token'=>$token))->update($arr_up);
			$new_info=$jtq_user->where(array('token'=>$token))->find();
			if($new_info){
				$err=array();
				$err['errno']=0;
				$err['error']="Success.";
				$err['avatar']=  $new_info['avatar'];
				$err['alias']=  $new_info['alias'];
				$err['sex']=  $new_info['sex'];
				$err['birthday']=  $new_info['birthday'];
				$err['height']=  $new_info['height'];
				$err['weight']=  $new_info['weight'];
				$err['email']=  $new_info['email'];
				$err['phone']=  $new_info['phone'];
				$err=json_encode($err);
				echo($err);

			}

		}

	}


// 1.3.5密码修改
// http://39.108.219.58:8808/api/user/password_modify.php?token=MTAwMTEwfHx8MWE1MmU0ZjQ4ZTUzMGIzYzMxZWI2ZmRiYzMzY2MzYWQ2YzFiZmJiN3x8fDIwMTktMDgtMjIgMDY6MDg6Mzk=&old_password=qweewq321&new_password=123

// d4e3a98e5ed367ece367ec80dee11023
// http://localhost/shouhuan/public/api/user/password_modify.php?token=d4e3a98e5ed367ece367ec80dee11023&old_password=e10adc3949ba59abbe56e057f20f883e&new_password=123

// https://shouhuan.taoyt.cn/api/user/password_modify.php?token=d4e3a98e5ed367ece367ec80dee11023&old_password=e10adc3949ba59abbe56e057f20f883e&new_password=123
// 8d5f9401e0220a7177658eec5e7d671b

	public function password_modify(){
		$token=isset($_REQUEST['token'])?$_REQUEST['token']:'';
		$old_password=isset($_REQUEST['old_password'])?$_REQUEST['old_password']:'';
		$new_password=isset($_REQUEST['new_password'])?$_REQUEST['new_password']:'';
		
		$jtq_user= Db::name('jtq_user');
		$f_info=$jtq_user->where(array('token'=>$token))->find();
		if(!$f_info){
			$err=array();
			$err['errno']=102;
			$err['error']="Invalid token!!!";
			$err=json_encode($err);
			echo($err);

		}else{
			if(!$old_password||!$new_password){
				$err=array();
				$err['errno']=105;
				$err['error']="Lack of parameters!!!";
				$err=json_encode($err);
				echo($err);
				exit();

			}

			$user=$f_info['user'];
			$id=$f_info['id'];
			$new_token=$this->get_token($user,$new_password);
			$arr_up=array();
			$arr_up['token']=$new_token;
			$arr_up['password']=$new_password;
			$up_info= $jtq_user->where(array('id'=>$id))->update($arr_up);
			if($up_info){
				$err=array();
				$err['errno']=0;
				$err['error']="Success.";
				$err['token']=  $new_token;
				$err=json_encode($err);
				echo($err);

			}else{
				$err=array();
				$err['errno']=106;
				$err['error']="Invalid password!!!";
				$err=json_encode($err);
				echo($err);
				exit();

			}

		}


	}


// 1.3.6密码重置

// http://39.108.219.58:8808/api/user/password_reset.php?email=892245718@qq.com
	

// http://localhost/shouhuan/public/api/user/password_reset.php?email=892245718@qq.com
// https://shouhuan.taoyt.cn/api/user/password_reset.php?email=892245718@qq.com

	public function password_reset(){


		$email=isset($_REQUEST['email'])?$_REQUEST['email']:'';
		$mima_reset=isset($_REQUEST['mima_reset'])?$_REQUEST['mima_reset']:'';

		$jtq_user= Db::name('jtq_user');

		// 前台传输
		if($mima_reset){
			$id=isset($_REQUEST['id'])?$_REQUEST['id']:'';
			$key=isset($_REQUEST['key'])?$_REQUEST['key']:'';
			$password=isset($_REQUEST['password'])?$_REQUEST['password']:'';
			$password=md5($password);
			$f_info=$jtq_user->where(array('id'=>$id,'token'=>$key))->find();
			if(!$f_info){
				$err=array();
				$err['errno']=102;
				$err['error']="Invalid token!!!";
				$err=json_encode($err);
				echo($err);
				exit();
			}

			$new_token=$this->get_token($f_info['user'],$password);
			$arr_up=array();
			$arr_up['token']=$new_token;
			$arr_up['password']=$password;
			$up_info= $jtq_user->where(array('id'=>$id))->update($arr_up);
			
			$err=array();
			$err['errno']=0;
			$err['error']="Success.";
			$err['token']=  $new_token;
			$err=json_encode($err);
			echo($err);

		}else{
				if(!$email){
					$err=array();
					$err['errno']=105;
					$err['error']="Lack of parameters!!!";
					$err=json_encode($err);
					echo($err);
					exit();
				}

			$f_info=$jtq_user->where(array('user'=>$email))->find();
			if(!$f_info){
				$err=array();
				$err['errno']=107;
				$err['error']="User not exist!!!";
				$err=json_encode($err);
				echo($err);
				exit();

			}


			require_once "Smtp.php";
			//******************** 配置信息 ********************************
			// $smtpserver = "smtp.daixiaorui.com";//SMTP服务器
			$smtpserver = "smtp.qq.com";//SMTP服务器
			$smtpserverport =25;//SMTP服务器端口
			$smtpusermail = "892245718@qq.com";//SMTP服务器的用户邮箱
			$smtpemailto = $email;//发送给谁
			$smtpuser = "892245718@qq.com";//SMTP服务器的用户帐号，注：部分邮箱只需@前面的用户名
			$smtppass = "sdctvgnatdmibccj";//SMTP服务器的用户密码
			// $mailtitle = $_POST['title'];//邮件主题
			// $mailcontent = "<h1>".$_POST['content']."</h1>";//邮件内容
			$mailtitle = "重置密码";//邮件主题
			$mailcontent = "<h1>您在band使用了密码重置功能，请通过下面的地址重置密码：</h1><br>";//邮件内容
			// $aa="http://39.108.219.58:8808/api/auth/password_reset_ui.php?id=100110&key=999";
			// yuyi 这里 key 就是 token
			$aa="https://shouhuan.taoyt.cn/api/user/password_reset_ui?id=".$f_info['id']."&key=".$f_info['token'];
			$mailcontent.="<a href='".$aa." '>".$aa." </a>";
			$mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件

			//************************ 配置信息 ****************************

			$smtp = new Smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);//这里面的一个true是表示使用身份验证,否则不使用身份验证.
			
			// print_r($mailcontent);
			// exit();

			$smtp->debug = false;//是否显示发送的调试信息
			$state = $smtp->sendmail($smtpemailto, $smtpusermail, $mailtitle, $mailcontent, $mailtype);
			if($state){

				$err=array();
				$err['errno']=0;
				$err['error']="Success.";
				$err=json_encode($err);
				echo($err);
				exit();
			}else{

				$err=array();
				$err['errno']=999;
				$err['error']="Unknown error!!!";
				$err=json_encode($err);
				echo($err);
				exit();
				
			}

			// echo "<div style='width:300px; margin:36px auto;'>";
			// if($state==""){
			// 	echo "对不起，邮件发送失败！请检查邮箱填写是否有误。";
			// 	echo "<a href='index.html'>点此返回</a>";
			// 	exit();
			// }
			// echo "恭喜！邮件发送成功！！";
			// echo "<a href='index.html'>点此返回</a>";
			// echo "</div>";


		}

		
	}


	public function password_reset_ui(){

		return $this->fetch('file/password_reset_ui');


	}



//  http://localhost/shouhuan/public/api/register/curl_text
	public function curl_text(){
		// http://39.108.219.58:8808/api/user/login.php?user=892245718@qq.com&password=4297F44B13955235245B2497399D7A93


		// $url="http://39.108.219.58:8808/api/user/login.php";
		// $msg_data=array('user'=>"892245718@qq.com","password"=>"4297F44B13955235245B2497399D7A93");

		$url="http://localhost/shouhuan/public/api/register/login.html";
		$msg_data=array('user'=>"test1@163.com","password"=>"qweewq3211");



		$ch = curl_init ($url);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		
		// // 当 strlen($data) > 1024 时，curl_exec函数将返回空字符串
		// // 解决：增加一个HTTP header
		// curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $msg_data);
		$response = curl_exec($ch);
		curl_close($ch);
		// print_r($response);
		return $response;



	}





	function get_token($user,$password){
		$str=$user.$password;
		$token=md5($str);
		return $token;

	}



}








