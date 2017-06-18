<?php

namespace Admin\Controller;

/**
 * News 新闻单独一个表
 */
class NewsController extends AdminController {
	/**
     * 新闻是单独一个表的
	 * 列表
	 * @return [type] [description]
	 */
	protected function infolist($parent_id = 0){
        $map['parent_id']  =   $parent_id;

        $list   = $this->lists('News', $map ,'sort desc, id desc',array());

        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        
        int_to_string($list);
        $this->assign('_list', $list);
        $this->assign('parent_id', $parent_id);
        $this->display('infolist');
	}

	/**
	 * 新闻/图片 列表
	 */
	public function newslist(){
		$parent_id = I('parent_id',0);
		if($parent_id){
			$meta_title = '图片列表';
		}else{
			$parent_id = 0;
			$meta_title = '新闻列表';
		}

		$this->meta_title = $meta_title;
		$this->infolist($parent_id);
	}

	/**
	 * 编辑、添加记录
	 */
	public function edit($parent_id = 0,$id = 0){
		$db = D('News');
		if(IS_POST){
			$method = $id?'save':'add';
			if($db->create()){
				if($db->$method()){
					$this->success('操作成功',Cookie('__forward__'));
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
        if(D('News')->where($map)->delete()){
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
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
     * 启用
     */
    protected function resume(){
        $id = I('id',0);
        if ( !$id ) {
            $this->error('非法操作!');
        }else{
            $data['status'] = 1;
            $data['id']     = $id;
            $db = D('News');
            if($db->save($data)){
                $this->success("已启用");
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
            $db = D('News');
            if($db->save($data)){
                $this->success("已禁用");
            }else{
                $this->error("操作失败");
            }
        }
    }


    /**
     * 行业
     */
    public function industry()
    {
    	$parent_id = 15;
    	$map['parent_id']  =   $parent_id;

        $list   = $this->lists('Information', $map ,'sort desc, id asc',array());

        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        
        int_to_string($list);
        $this->assign('_list', $list);
        $this->assign('parent_id', $parent_id);
        $this->display();

    }

    /**
     * 行业信息编辑
     */
    public function industryEdit($id = 0)
    {
    	$parent_id = 15;
    	$db = D('Information');
		if(IS_POST){
            $_POST['desc'] = substr(strip_tags(I('content')),0,200);
			$method = $id?'save':'add';
			if($db->create()){
				if($db->$method()){
					$this->success('操作成功',Cookie('__forward__'));
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
			$this->parent_id = $parent_id;
			$this->display();
		}
    }


    public function changeIndustryStatus($method=null){
        $method = strtolower($method) . "Industry";
        $this->$method();
    }

    /**
     * 启用
     */
    protected function resumeIndustry(){
        $id = I('id',0);
        if ( !$id ) {
            $this->error('非法操作!');
        }else{
            $data['status'] = 1;
            $data['id']     = $id;
            $db = D('Information');
            if($db->save($data)){
                $this->success("已启用");
            }else{
                $this->error("操作失败");
            }
        }
    }

    /**
     * 禁用
     */
    protected function forbidIndustry(){
        $id = I('id',0);
        if ( !$id ) {
            $this->error('非法操作!');
        }else{
            $data['status'] = 0;
            $data['id']     = $id;
            $db = D('Information');
            if($db->save($data)){
                $this->success("已禁用");
            }else{
                $this->error("操作失败");
            }
        }
    }

    /**
     * 删除记录
     */
    public function deleteIndustryInfos(){
        $id = array_unique((array)I('id',0));
        
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }

        $map = array('id' => array('in', $id) );
        if(D('Information')->where($map)->delete()){
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

}