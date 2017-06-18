<?php
namespace Admin\Controller;
/**
 * 教师工资
 */
class SalaryController extends AdminController {

    public function index(){
        $teacher_name = I('teacher_name');
        $year   = I('year');
        $month  = I('month');
        $where  = '1';
        if($teacher_name){
            $where .=" AND b.teacher_name  ='".$teacher_name."'";
        }
        if($year){
            $where .=" AND YEAR(FROM_UNIXTIME(a.pay_time))  ='".$year."'";
        }
        if($month){
            $where .=" AND MONTH (FROM_UNIXTIME(a.pay_time))  ='".$month."'";
        }

        //权限限制条件
        $cod['a.uid'] = UID;
        $rs = D('Member')->table('otk_member a')->join('otk_auth_group_access b on a.uid=b.uid')->field('a.area_id,a.teacher_id,b.group_id')->where($cod)->find();
        if($rs['group_id']==5){//校长
            $map['area_id']  = array('in',$rs['area_id']);
            $ret = D('Teacher')->field('teacher_id')->where($map)->select();
            foreach($ret as $k => $v){
                $teacher_id .= '"'.$v['teacher_id'].'",';
            }
            $teacher_id = trim($teacher_id,',');
            $where .= " AND b.teacher_id in ($teacher_id)";
        }
        if($rs['teacher_id']){//教师
           $where .= " AND b.teacher_id = ".$rs['teacher_id']."";
        }
        $list   = D('Pay')->query(" SELECT YEAR(FROM_UNIXTIME(a.pay_time)) y ,MONTH (FROM_UNIXTIME(a.pay_time)) m ,sum(a.pay_money)pay_money,a.teacher_id,b.teacher_name,c.class_id,c.class_name,c.proportion FROM otk_pay a 
            LEFT JOIN  otk_teacher b on a.teacher_id = b.teacher_id
            LEFT JOIN  otk_class c on a.class_id = c.class_id   
            WHERE $where GROUP BY a.teacher_id,a.class_id,y,m ");
        foreach($list as $k=>$v){
            $list[$k]['salary'] = $v['pay_money']*$v['proportion'];
        }
        foreach($list as $key=>$val){
            $data['sumd_salary']  += $list[$key]['salary'];
        }
        $this->meta_title = '教师工资';
        $request    =   (array)I('request.');
        $total      =   $list? count($list) : 1 ;
        $listRows   =   C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        $page       =   new \Think\Page($total, $listRows, $request);
        $voList     =   array_slice($list, $page->firstRow, $page->listRows);
        $p          =   $page->show();
        $this->assign('year_type', $this->get_year());
        $this->assign('month_type', $this->get_month());

        $this->assign('year', $year);
        $this->assign('month', $month);


        $this->assign('info', $voList);
        $this->assign('data', $data);
        $this->assign('_page', $p? $p: '');
        // 记录当前列表页的cookie
        Cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display();       
    }

    public function get_year(){
        $year_type = array(2016=>'2016',2017=>'2017',2018=>'2018');
        return $year_type;
    }
    public function get_month(){
        $month_type = array(1=>'1月',2=>'2月',3=>'3月',4=>'4月',5=>'5月',6=>'6月',7=>'7月',8=>'8月',9=>'9月',10=>'10月',11=>'11月',12=>'12月');
        return $month_type;
    }   
}

