<?php
namespace Admin\Controller;
/**
 * 信息控制器
 */
class GoalController extends AdminController {

    /**
     * 福值兑换
     * @return [type] [description]
     */
    public function index(){
        
        $this->meta_title = '类型兑换福值信息';
        $list       =   D('Jf_goal')->select();
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('info', $voList);
        $this->assign('_page', $p? $p: '');
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();       
    }

    public function add(){
            $db  =D('Jf_goal');
            $this->meta_title = '新增福值';
            $this->display('add');
    }

    public function edit($goal_id){
        $model = D('Jf_goal');
        $where['goal_id'] = $goal_id;
        $data=$model->find($goal_id);
        $this->assign('data',$data);
        $this->meta_title = '编辑福值';
        $this->display();
    }

    public function update(){
        $db  =D('Jf_goal');
        $_POST['goal_id'] = intval($_POST['goal_id']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Goal/index'));
        } else {
            $this->error('更新失败!');
        }
    }

    public function insert(){
        if(IS_POST){
            $db  =D('Jf_goal');
            $data = $db->create();
            if($data){
                $list  = $db->add($_POST);
                if($list){
                    $this->success('添加成功',U('Goal/index'));
                } else {
                    $this->error('添加失败');
                }
            }else {
                $this->error($Menu->getError());
            }
        }
    }
    public function deleteInfos(){
        $goal_id = array_unique((array)I('goal_id',0));       
        $goal_id = is_array($goal_id) ? implode(',',$goal_id) : $goal_id;
        if ( empty($goal_id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['goal_id'] = array('in',$goal_id);
        $model = D('Jf_goal');
        if($model->where($map)->delete()){
            $this->success('删除成功',U('Goal/index'));
        }else{
            $this->error('删除失败');
        }
    }
}
