<?php

namespace Admin\Controller;

/**
 * OthersController 
 */
class OthersController extends AdminController {

	/**
	 * 联系我们
	 * @param  string $value [description]
	 * @return [type]        [description]
	 */
	public function contact()
	{
		$parent_id = 1;
		$list   = $this->lists('Contact',array('parent_id' => $parent_id),'sort desc,id desc');
        int_to_string($list);
        $this->assign('_list', $list);
        $this->meta_title = '联系我们';

		$this->display();
	}

	/**
	 * 添加
	 */
	public function add(){
		$model = D('Contact');
		$id = I('id',intval);
		$parent_id = 1;
		if(IS_POST){
            $data = $model->create();
            if($data){
            	$method = $id?"save":"add";
                if($model->$method()){
                    $this->success('操作成功', U('Others/contact'));
                } else {
                    $this->error('操作失败');
                }
            } else {
                $this->error($model->getError());
            }
        } else {
        	if($id){
        		$this->data = $model->find($id);
        	}
            $this->parent_id  = $parent_id;
            $this->meta_title = '编辑';
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
        if(M('Contact')->where($map)->delete()){
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
            $db = D('Contact');
            if($db->save($data)){
                $this->success("已启用",U('Others/contact'));
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
            $db = D('Contact');
            if($db->save($data)){
                $this->success("已禁用",U('Others/contact'));
            }else{
                $this->error("操作失败");
            }
        }
    }

}