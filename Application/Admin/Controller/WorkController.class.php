<?php

namespace Admin\Controller;

/**
 * Work 
 */
class WorkController extends AdminController {
	/**
	 * 列表
	 * @return [type] [description]
	 */
	protected function infolist($parent_id = 0){
        $map['parent_id']  =   $parent_id;

        $list   = $this->lists('Work', $map ,'sort desc, id desc',array());

        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        
        int_to_string($list);
        $this->assign('_list', $list);
        $this->assign('parent_id', $parent_id);
        $this->display('infolist');
	}

	/**
	 * brand
	 */
	public function brand(){
		$parent_id = I('parent_id',0);

		if($parent_id){
			$meta_title = '图片列表';
		}else{
			$parent_id = 6;
			$meta_title = '品牌-案例列表';
		}

		$this->meta_title = $meta_title;
		$this->infolist($parent_id);
	}

	/**
	 * space
	 */
	public function space(){
		$parent_id = I('parent_id',0);
		if($parent_id){
			$meta_title = '图片列表';
		}else{
			$parent_id = 7;
			$meta_title = '空间-案例列表';
		}

		$this->meta_title = $meta_title;
		$this->infolist($parent_id);
	}

	/**
	 * vision
	 */
	public function vision(){
		$parent_id = I('parent_id',0);
		if($parent_id){
			$meta_title = '图片列表';
		}else{
			$parent_id = 8;
			$meta_title = '视觉-案例列表';
		}

		$this->meta_title = $meta_title;
		$this->infolist($parent_id);
	}

	/**
	 * 编辑、添加记录
	 */
	public function edit($parent_id = 0,$id = 0){
		$db = D('Work');
		$cateName = array('6' => 'brand','7' => 'space','8' => 'vision');

		if(IS_POST){
			$method = $id?'save':'add';
			if($db->create()){
				if($db->$method()){
					$this->success('操作成功',Cookie('__forward__'));
					// $this->success('操作成功',U('Work/'.$cateName[$parent_id]));
				}else{
					$this->error('操作失败');
				}
			}else{
				$this->error($db->getError());
			}
		}else{
			if($id){
				$this->data = $db->find($id);
			}
			$this->flags = C('WORK_FLAGS');
			$this->cateName = $cateName;
			$this->parent_id = $parent_id;
			$this->meta_title = '编辑';
			$this->display();
		}
	}

	/**
	 * 删除记录
	 */
	public function deleteInfos(){
        $id = array_unique((array)I('id',0));
        
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(D('Work')->where($map)->delete()){
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

}