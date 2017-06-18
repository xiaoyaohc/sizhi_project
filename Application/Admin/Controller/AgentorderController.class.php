<?php
namespace Admin\Controller;
/**
 * 代理商收入
 */
class AgentorderController extends AdminController {
    public function index(){
        $order_id     = I('order_id');
        $agent_name   = I('agent_name');
        $statements_status=I('statements_status');
        $start_time=strtotime(I('start_time'));
        $end=I('end_time');
        $end_time=strtotime($end)+86400;
        //非本月份的订单都显示为已经结算
        $t   =   time();
        $mouth=mktime(0,0,0,date("m",$t),1,date("Y",$t));
        $where = '1';
        if(!empty($order_id)){
            $where  .=  " AND a.order_id = '".$order_id."'";
        }
        if(!empty($agent_name)){
            //$where  .=  " AND a.agent_name like '%$agent_name%'";
            $where  .=" AND a.agent_name ='".$agent_name."'";
        }
        if(!empty($statements_status)){
//             if($statements_status==1){
//                 $where  .=  " AND a.statements_status = '".$statements_status."'";
//             }elseif($statements_status==2){
//                 $where  .=  " AND a.statements_status = 0";
//             }
            if($statements_status==1){
                $where  .=  " AND a.add_time <$mouth";
            }elseif($statements_status==2){
                $where  .=  " AND a.add_time >= $mouth ";
            }
        }
        if(!empty($start_time)){
            $where  .=  " AND a.add_time >=$start_time";
        }
        if(!empty($end)){
        $where  .=  " AND a.add_time <$end_time";
        }
        $rs   = get_site_cate();
        $list = D('Agent_order')->query("SELECT a.*,c.class_name from otk_agent_order a 
            left join otk_class c on a.class_id=c.class_id 
            WHERE $where;");
        foreach($list as $k=>$v){
           // $list[$k]['statements_status']    =$v['statements_status']==1?'已结算':'未结算';
            $list[$k]['statements_status']    =$v['add_time']<$mouth?'已结算':'未结算';
        }
        $data=array();
        foreach($list as $key=>$val){
            $data['all_income']  += $list[$key]['income'];
        }
        $status   = array(0=>'请选择',1=>'已结算',2=>'未结算');
        $this->meta_title = '代理商收入';
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('status', $status);
        $this->assign('start_time', $start_time);
        $this->assign('end_time', $end_time);
        $this->assign('data', $data);
        $this->assign('rs', $rs);
        $this->assign('info', $voList);
        $this->assign('_page', $p? $p: '');
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();
    }
    public function edit($id){
        $model = D('Agent_order');
        $rs    = get_site_cate();
        $this->assign('rs', $rs);
        $where['id'] = $id;
        $data  = $model->find($id);
        $this->assign('data',$data);
        $this->assign('rs', $rs);
        $this->meta_title = '编辑代理商收入信息';
        $this->display();
    }
    
    public function update(){
        $db  =D('Agent_order');
        unset($_POST['parse']);
        $_POST['id'] = intval($_POST['id']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Agentorder/index'));
        } else {
            $this->error('更新失败!');
        }
    }
    public function deleteInfo(){
        $id = array_unique((array)I('id',0));
        $id = is_array($id) ? implode(',',$id) : $id;
        if ( empty($id) ) {
            $this->error('请选择要操作的数据!');
        }
        $map['id'] = array('in',$id);
        $model = D('Agent_order');
        if($model->where($map)->delete()){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }
}

    