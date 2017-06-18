<?php
namespace Admin\Controller;
/**
 * 微信用户信息
 */
class WxuserController extends AdminController {

    
    public function index(){
        $nick_name  = I('nick_name');
        $agent_name  = I('agent_name');
        $where = '1';
        if(!empty($nick_name)){
          // $where .= " And w.nick_name='".$nick_name."'"; 
            $where  .=  " AND w.nick_name like '%$nick_name%'";
        }
        if(!empty($agent_name)){
            //$where .= " And a.name='".$agent_name."'";
            $where  .=  " AND a.name like '%$agent_name%'";
        }
        $rs  = get_site_cate();
        $list= D('Wx_user')->query("SELECT w.*,a.name as agent_name from otk_wx_user w
            left join otk_agent a on w.agent_id=a.id 
            WHERE $where");
        //$list=   D('Wx_user')->where($where)->order('subscribe_time asc')->select();
        foreach($list as $k=>$v){
            $list[$k]['sex'] = $v['sex']==1?'男':'女';
        }
        $account=count($list);
        $this->meta_title = '微信用户管理';
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('rs', $rs);
        $this->assign('info', $voList);
        $this->assign('account', $account);
        $this->assign('_page', $p? $p: '');
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();       
    }

    public function edit($id){
        $model = D('Wx_user');
        $stumodel = D('Student');
        $where['id'] = $id;
        $data=$model->find($id);
        $where_stu['wx_id'] = $data['wx_id'];
        $data_student = $stumodel->where($where_stu)->select();
        $this->assign('data_stu',$data_student);
        $this->assign('data',$data);
        $this->meta_title = '编辑微信用户信息';
        $this->display();
    }

    public function update(){
        $db  =D('Wx_user');
        $_POST['id'] = intval($_POST['id']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Wxuser/index'));
        } else {
            $this->error('更新失败!');
        }
    }

    
    public function deleteInfos(){
        $id = array_unique((array)I('id',0));       
        $slide_id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] = array('in',$id);
        $model = D('Wx_user');
        if($model->where($map)->delete()){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}

