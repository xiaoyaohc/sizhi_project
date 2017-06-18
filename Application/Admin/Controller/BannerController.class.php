<?php

namespace Admin\Controller;

class BannerController extends AdminController {
	/**
	 * 首页轮播图
	 */
	public function index(){
		
		$list   = $this->lists('Banner',array(),'sort desc,id desc');
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '首页轮播图';

        $this->display();
	}

	/**
	 * 添加
	 */
	public function add(){
		$Banner = D('Banner');
		$id = I('id',intval);
		if(IS_POST){
            $data = $Banner->create();
            if($data){
            	$method = $id?"save":"add";
                if($Banner->$method()){
                    $this->success('新增成功', U('Banner/index'));
                } else {
                    $this->error('新增失败');
                }
            } else {
                $this->error($Banner->getError());
            }
        } else {
        	if($id){
        		$this->data = $Banner->find($id);
        	}
            $this->meta_title = '轮播图';
            $this->display();
        }
	}


    /**
     * 状态修改
     */
    public function changeStatus($method=null){
        $method = strtolower($method);
        $this->$method();
    }

    /**
     * 删除后台菜单
     */
    protected function del(){
        $id = array_unique((array)I('id',0));

        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(M('Banner')->where($map)->delete()){
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }
    /**
     * 启用
     */
    protected function resume(){
        $id = I('id',0);
        if ( !$id ) {
            $this->error('非法操作!');
        }else{
            $data['status'] = 1;
            $data['id']     = $id;
            $db = D('Banner');
            if($db->save($data)){
                $this->success("已启用",U('Banner/index'));
            }else{
                $this->error("操作失败");
            }
        }
    }

    /**
     * 禁用
     */
    protected function forbid(){
        $id = I('id',0);
        if ( !$id ) {
            $this->error('非法操作!');
        }else{
            $data['status'] = 0;
            $data['id']     = $id;
            $db = D('Banner');
            if($db->save($data)){
                $this->success("已禁用",U('Banner/index'));
            }else{
                $this->error("操作失败");
            }
        }
    }
}