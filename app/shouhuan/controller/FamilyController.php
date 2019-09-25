<?php
namespace app\shouhuan\controller;

use think\Db;
use cmf\controller\AdminBaseController;

class FamilyController extends AdminBaseController
{


	public function index(){

		$jtq_user= Db::name('jtq_user');
		$jtq_family= Db::name('jtq_family');
		$params=$this->request->param();

		$where=array();
		$where_ands=array();
		if(isset($params['name'])&&!empty($params['name'])){
			$name=$params['name'];
			$name="%$name%";
			array_push($where_ands, "name like '$name'");
			$where= join(" and ", $where_ands);

		}
		$count=$jtq_family->where($where)->count();

		$lists=$jtq_family->where($where)->order('id desc')->paginate(20)->each(function($item, $key){

			$token=$item['token'];
			$jtq_user= Db::name('jtq_user');
			$f_user=$jtq_user->where(array('token'=>$token))->find();

			$alias=$f_user['alias'];
			$item['alias']=$alias;

			return $item;

    	});


    	$this->assign('lists', $lists);
    	$this->assign('count', $count);
		$lists->appends($params);
		$this->assign('page', $lists->render());
		return $this->fetch();


	}


// 家庭圈成员
	public function fam_users(){


		$jtq_user= Db::name('jtq_user');
		$jtq_family= Db::name('jtq_family');
		$jtq_family_member= Db::name('jtq_family_member');
		$params=$this->request->param();
		$f_id=$params['f_id'];

		$where=array();
		$where_ands=array();
		array_push($where_ands, "f_id like '$f_id'");
		if(isset($params['u_name'])&&!empty($params['u_name'])){
			$u_name=$params['u_name'];
			$u_name="%$u_name%";
			array_push($where_ands, "u_name like '$u_name'");
			$where= join(" and ", $where_ands);

		}else{
			$where= join(" and ", $where_ands);
		}


		$count = $jtq_family_member->where($where)->count();

		$lists = $jtq_family_member->where($where)->order('create_time desc')
		->paginate(20)->each(function($item, $key){

			$jtq_user= Db::name('jtq_user');
			$token=$item['token'];
			$f_f=$jtq_user->where(array('token'=>$token))->find();
			$item['avatar']=$f_f['avatar'];
			$item['sex']=$f_f['sex'];
			
			return $item;


		});


		// print_r(json_encode($lists ));
		// exit();


		$this->assign('lists', $lists);
    	$this->assign('count', $count);
    	$this->assign('f_id', $f_id);
		$lists->appends($params);
		$this->assign('page', $lists->render());
		return $this->fetch();



	}








}

