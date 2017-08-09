<?php
namespace Admin\Controller;
/**
 * 区域收入
 */
class AreaorderController extends AdminController {
    public function index(){
        $order_id     = I('order_id');
        $area_own_name   = I('area_own_name');
        $statements_status=I('statements_status');
        $start_time=strtotime(I('start_time'));
        $area_id   = I('area_id');
        $end=I('end_time');
        $type=I('type');
        $end_time=strtotime($end)+86400;
        //非本月份的订单都显示为已经结算
        $t   =   time();
        $mouth=mktime(0,0,0,date("m",$t),1,date("Y",$t));
        $where = '1';
        if(!empty($order_id)){
            $where  .=  " AND o.order_id = '".$order_id."'";
        }
        if(!empty($area_own_name)){
            //$where  .=  " AND a.agent_name like '%$agent_name%'";
            $where  .=" AND o.area_own_name ='".$area_own_name."'";
        }
        if(!empty($statements_status)){
//             if($statements_status==1){
//                 $where  .=  " AND o.statements_status = '".$statements_status."'";
//             }elseif($statements_status==2){
//                 $where  .=  " AND o.statements_status = 0";
//             }
            if($statements_status==1){
                $where  .=  " AND o.add_time <$mouth";
            }elseif($statements_status==2){
                $where  .=  " AND o.add_time >= $mouth ";
            }
        }
        if(!empty($start_time)){
            $where  .=  " AND o.add_time >=$start_time";            
        }
        if(!empty($end)){
            $where  .=  " AND o.add_time <$end_time";
        }
        if(!empty($area_id)){
            //$where  .=  " AND a.agent_name like '%$agent_name%'";
            $where  .=" AND a.id =".$area_id;
        }
        $rs   = get_site_cate();
        //权限限制条件
        if(UID!=1){
            $teacher_1=D()->table('otk_member')->field('teacher_id')->where('uid='.UID)->find();
            if($teacher_1){
                $where  .=" AND a.teacher_id =".$teacher_1['teacher_id'];
            }else{
                $where  .=" AND a.teacher_id =0";
            }
        }
        $list = D('Area_order')->query("SELECT o.*,a.area_name,a.name area_own_names from otk_area_order o 
            left join otk_area a on a.id=o.area_id 
            WHERE $where");
        foreach($list as $k=>$v){
            $list[$k]['pay_status']    =$v['pay_status']==1?'已付款':'未付款';
            $list[$k]['statements_status']    =$v['add_time']<$mouth?'已结算':'未结算';
        }
       //实现导出功能
        if($type){
            $xlsName  = "机构收入";
            $xlsCell  = array(
                array('order_id','订单号'),
                array('area_name','校区'),
                array('area_own_names','负责人'),
                array('class_name','课程名称'),
                array('pay_money','订单金额'),
                array('pay_time','下单时间'),
                array('area_ratio','校区提成比例'),
                array('income','收入'),
                array('statements_status','结算状态'),
                array('add_time','添加时间'),
            );
            foreach ($list as $k => $v) {
                $list[$k]['order_id'] = ' '.$v['order_id'].' ';
                $list[$k]['pay_time'] = $v['pay_time'] == null ? '-' : date("Y-m-d H:i",$v['pay_time']);
                $list[$k]['add_time'] =  $v['add_time'] == null ? '-' : date("Y-m-d H:i",$v['add_time']);
            }
            exportExcel($xlsName,$xlsCell,$list);
        }
        $data=array();
        foreach($list as $key=>$val){
            $data['all_income']  += $list[$key]['income'];
        }
        $status   = array(0=>'请选择',1=>'已结算',2=>'未结算');
        $this->meta_title = '机构收入';
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $area = D('Area')->select();
        $this->assign('area',$area);
        $this->assign('statements_status', $statements_status);
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
        $model = D('Area_order');
        $rs    = get_site_cate();
        $this->assign('rs', $rs);
        $where['id'] = $id;
        $data  = $model->find($id);
        $this->assign('data',$data);
        $this->assign('rs', $rs);
        $this->meta_title = '编辑校区收入信息';
        $this->display();
    }
    
    public function update(){
        $db  =D('Area_order');
        unset($_POST['parse']);
        $_POST['id'] = intval($_POST['id']);
        $list  = $db->save($_POST);
        if ($list !== false) {
            $this->success('更新成功',U('Areaorder/index'));
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

    