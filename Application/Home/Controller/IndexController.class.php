<?php

namespace Home\Controller;
use Home\Model\ClassModel;
use Home\Model\IndexModel;
use Home\Model\SubjectModel;
use Home\Model\TeacherModel;
use Home\Model\UserModel;

/**
 * 
 * 
 */
class IndexController extends HomeController {
    const APPID      = 'wxa3fec0285ab83c09';
    const APPSECRET  = '7904ce4c83a8d89d0bfbdec1ff9f7ad4';

    /**
     * 初始化
     */
    protected function _initialize(){
          $_SESSION['wx_id'] = 'oEjhSwiytpQ8_Kmf-EZV-h-veOoM';
//        $_SESSION['wx_id'] = 'oEjhSwq5k8Jhb-KQ_hHxB1rhsrmI';
//        $_SESSION['wx_id'] = 'oEjhSwnDMvwOByAujlDEFp9Meuco';
        //$_SESSION['wx_id'] = 'oEjhSwkPDVqPNjaOOc4B1EzwxC4Y';
        if(!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['REQUEST_URI'])){
            $reUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        }else{
            $reUrl = "http://".C('WX_CONFIG.domain')."/Home/Index/home/";
        }
        get_wx_id($reUrl);
        //微信sSDK
        $jssdk = new JSSDK(C('WX_CONFIG.appid'), C('WX_CONFIG.secret'));
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage',$signPackage);
    }

	//首页
    public function index(){
        //网页的方式获取openid
        $this->home();
    }

    public function home(){
        if(!empty($_SERVER['HTTP_HOST']) && !empty($_SERVER['REQUEST_URI'])){
            $reUrl = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        }else{
            $reUrl = "http://".C('WX_CONFIG.domain')."/Home/Index/home/";
        }
        get_wx_id($reUrl);

        //是否已登陆判断代理商
        if(empty($_SESSION['is_login_agent'])){
            $userInfo = UserModel::getInfo($_SESSION['wx_id']);
            if(empty($userInfo['agent_id'])){
                $_SESSION['is_login_agent'] = 0;
//                $this->display('index/loading');
//                die;
            }else{
                $_SESSION['is_login_agent'] = 1;
            }
        }

        $slideList = IndexModel::getSlideList();//获取幻灯片列表
        $subjectList = SubjectModel::getList();//获取科目列表
        $teacherList = TeacherModel::getRank();//获取推荐教师
        $this->assign('slideList',$slideList);
        $this->assign('subjectList',$subjectList);
        $this->assign('teacherList',$teacherList);
    	$this->display('index/home');
    }

    //关于我们
    public function about(){
        $this->display('index/about');
    }

    //联系我们
    public function contact(){
        $this->display('index/contact');
    }

    //进步之星
    public function rank(){
        $rank = ClassModel::getRankNew();
        $this->assign('rank',$rank);
        $this->display('index/rank');
    }

    //纪律之星
    public function discipline(){
        $rank = ClassModel::getDiscipline();
        $this->assign('rank',$rank);
        $this->display('index/discipline');
    }

    private function get_php_file($filename) {
        return trim(substr(file_get_contents($filename), 15));
    }

    public function test(){
//        $WechatAuth = new \Com\WechatAuth(C('WX_CONFIG.appid'),C('WX_CONFIG.secret'));
//        $access_token = $WechatAuth->getAccessToken();
//        $jssdk = new JSSDK(C('WX_CONFIG.appid'), C('WX_CONFIG.secret'));
//        $signPackage = $jssdk->GetSignPackage();
        echo '<pre>';
        $getStr = I('str');
        $str = empty($getStr)?'耶耶耶耶耶':$getStr;
        $getName = I('name');
        $name = empty($getName)?'yoyo':$getName;

        $signPackage = reply_customer($name,$str);
        if($signPackage->errcode==40014){
            unset($_SESSION['qy_access_token']);
            $signPackage = reply_customer($name,$str);
        }
        var_dump($signPackage);

        if($name=='yoyo'){
            $name = 'zhangdada';
            $signPackage = reply_customer($name,$str);
            var_dump($signPackage);
        }
        if($name=='zhangdada'){
            echo $_SESSION['qy_access_token'];
        }

        die;
        $this->display('index/courses');
    }


    public function invite(){
        $userInfo = UserModel::getInfo($_SESSION['wx_id']);
        if(empty($_POST)){
            $inviteCode = M('Agent')->where('id='.$userInfo['agent_id'])->getField('code');
            $this->assign('Code',$inviteCode);
            $this->display('index/loading');
            die;
        }

        $res = M('agent')->where('code='.$_POST['inviteCode'])->find();

        if(empty($res)){
            $_SESSION['is_login_agent'] = 0;
            echo '<script>alert("邀请码错误")</script>';
            $this->index();
            die;
        }

        $userInfo = UserModel::getInfo($_SESSION['wx_id']);
        unset($userInfo['students']);
        $userInfo['agent_id'] = $res['id'];
        $result = UserModel::userInfoEdit($userInfo);
        if($result){
            $_SESSION['is_login_agent'] = 1;
            echo '<script>alert("邀请成功")</script>';
            $this->index();
        }

    }

    public function inviteJump(){
        $_SESSION['is_login_agent'] = 1;
        $this->index();

    }

    public function inviteJumpBack(){
        $_SESSION['is_login_agent'] = 0;
        $this->index();

    }

    public function reCall(){
//        $WechatAuth = new \Com\WechatAuth(C('WX_CONFIG.appid'),C('WX_CONFIG.secret'));
//        $access_token = $WechatAuth->getAccessToken();
//        $jssdk = new JSSDK(C('WX_CONFIG.appid'), C('WX_CONFIG.secret'));
//        $signPackage = $jssdk->GetSignPackage();
        echo '<pre>';
        $getStr = I('str');
        $str = empty($getStr)?'耶耶耶耶耶':$getStr;
        $getName = I('name');
        $name = empty($getName)?'zhangdada':$getName;

        $signPackage = reply_customer($name,$str);
        if($signPackage->errcode==40014){
            unset($_SESSION['qy_access_token']);
            $signPackage = reply_customer($name,$str);
        }
        var_dump($signPackage);

        if($name=='yoyo'){
            $name = 'zhangdada';
            $signPackage = reply_customer($name,$str);
            var_dump($signPackage);
        }
        if($name=='zhangdada'){
            echo $_SESSION['qy_access_token'];
        }

        die;
        $this->display('index/courses');
    }

    public function getRatio()
    {
        $month = date('Y-m-01');
        $thisMonth = strtotime($month);
        $lastMonth = strtotime("$month -1 month ");

        //代理商
        $agent = M('Agent')->where("wx_id='".$_SESSION['wx_id']."'")->find();
        if($agent) {
            $IsAgentRatio = M('AgentOrder')->where("agent_wx_id='".$_SESSION['wx_id']."'")->find();
            if($IsAgentRatio){

                $AgentRatio = M('AgentOrder')->where("agent_wx_id='".$_SESSION['wx_id']."' and add_time>=" . $thisMonth)->getField('income', 0);
                $AgentRatio = array_sum($AgentRatio);
                $this->assign('AgentRatio', $AgentRatio);

                //历史订单金额
                $FirstTime = M('AgentOrder')->where("agent_wx_id='" . $_SESSION['wx_id'] . "'")->order('add_time')->getField('add_time');
                //计算出需要计算的月份数量，即循环次数
                $Year = date('Y',$thisMonth)-date('Y',$FirstTime);
                if($Year>0){
                    $Mon = 12-date('m',$thisMonth)+date('m',$FirstTime)+12*($Year-1);
                }else{
                    $Mon = date('m',$thisMonth)-date('m',$FirstTime);
                }

                $reFirstMonth = $FirstTime;
                $LastAgentRatio = array();
                //循环计算金额
                for($i=0;$i<$Mon;$i++){
                    $reFirstMonth = date('Y-m',$reFirstMonth);
                    $reMonth = strtotime("$reFirstMonth +1 month ");
                    $TempRatio = array();
                    $TempRatio = M('AgentOrder')->where("agent_wx_id='".$_SESSION['wx_id']."' and add_time between ".strtotime($reFirstMonth)." and ".$reMonth)->getField('income',0);
                    if($TempRatio){
                        $TempArea = array('time'=>$reFirstMonth,'ratio'=>array_sum($TempRatio));
                    }else{
                        $TempArea = array('time'=>$reFirstMonth,'ratio'=>0);
                    }
                    $LastAgentRatio[] = $TempArea;

                    $reFirstMonth = $reMonth;
                }

                $this->assign('LastAgentRatio', $LastAgentRatio);
            }
        }

        //区域代理
        $area = M('Area')->where("wx_id='".$_SESSION['wx_id']."'")->find();
        if($area){
            $IsAreaRatio = M('AreaOrder')->where("area_wx_id='". $_SESSION['wx_id']."'")->find();
            if($IsAreaRatio){

                $AreaRatio = M('AreaOrder')->where("area_wx_id='".$_SESSION['wx_id']."' and add_time>=".$thisMonth)->getField('income',0);
                $AreaRatio = array_sum($AreaRatio);
                $this->assign('AreaRatio',$AreaRatio);

                //历史订单金额
                $FirstTime = M('AreaOrder')->where("area_wx_id='" . $_SESSION['wx_id'] . "'")->order('add_time')->getField('add_time');
                //计算出需要计算的月份数量，即循环次数
                $Year = date('Y',$thisMonth)-date('Y',$FirstTime);
                if($Year>0){
                    $Mon = 12-date('m',$thisMonth)+date('m',$FirstTime)+12*($Year-1);
                }else{
                    $Mon = date('m',$thisMonth)-date('m',$FirstTime);
                }

                $reFirstMonth = $FirstTime;
                $LastAreaRatio = array();
                //循环计算金额
                for($i=0;$i<$Mon;$i++){
                    $reFirstMonth = date('Y-m',$reFirstMonth);
                    $reMonth = strtotime("$reFirstMonth +1 month ");
                    $TempRatio = array();
                    $TempRatio = M('AreaOrder')->where("area_wx_id='".$_SESSION['wx_id']."' and add_time between ".strtotime($reFirstMonth)." and ".$reMonth)->getField('income',0);
                    if($TempRatio){
                        $TempArea = array('time'=>$reFirstMonth,'ratio'=>array_sum($TempRatio));
                    }else{
                        $TempArea = array('time'=>$reFirstMonth,'ratio'=>0);
                    }
                    $LastAreaRatio[] = $TempArea;

                    $reFirstMonth = $reMonth;
                }

                $this->assign('LastAreaRatio',$LastAreaRatio);
            }
        }

        //教师
        $teacher = M('Teacher')->where("wx_id='".$_SESSION['wx_id']."'")->find();
        if($teacher){
            $IsTeachRatio = M('TeachOrder')->where("teach_wx_id='". $_SESSION['wx_id']."'")->find();
            if($IsTeachRatio) {

                $TeachRatio = M('TeachOrder')->where("teach_wx_id='" . $_SESSION['wx_id'] . "' and add_time>=" . $thisMonth)->getField('income', 0);
                $TeachRatio = array_sum($TeachRatio);
                $this->assign('TeachRatio', $TeachRatio);

                //历史订单金额
                $FirstTime = M('TeachOrder')->where("teach_wx_id='" . $_SESSION['wx_id'] . "'")->order('add_time')->getField('add_time');
                //计算出需要计算的月份数量，即循环次数
                $Year = date('Y',$thisMonth)-date('Y',$FirstTime);
                if($Year>0){
                    $Mon = 12-date('m',$thisMonth)+date('m',$FirstTime)+12*($Year-1);
                }else{
                    $Mon = date('m',$thisMonth)-date('m',$FirstTime);
                }

                $reFirstMonth = $FirstTime;
                $LastTeachRatio = array();
                //循环计算金额
                for($i=0;$i<$Mon;$i++){
                    $reFirstMonth = date('Y-m',$reFirstMonth);
                    $reMonth = strtotime("$reFirstMonth +1 month ");
                    $TempRatio = array();
                    $TempRatio = M('TeachOrder')->where("teach_wx_id='".$_SESSION['wx_id']."' and add_time between ".strtotime($reFirstMonth)." and ".$reMonth)->getField('income',0);
                    if($TempRatio){
                        $TempArea = array('time'=>$reFirstMonth,'ratio'=>array_sum($TempRatio));
                    }else{
                        $TempArea = array('time'=>$reFirstMonth,'ratio'=>0);
                    }
                    $LastTeachRatio[] = $TempArea;

                    $reFirstMonth = $reMonth;
                }

                $this->assign('LastTeachRatio', $LastTeachRatio);
            }
        }

//        var_dump($AgentRatio,$LastAgentRatio,$AreaRatio,$LastAreaRatio,$TeachRatio,$LastTeachRatio);
//        die;
        $this->display('index/ratio');
    }

    //点赞
    public function vote(){
        $return['status'] = 0;
        $vote['student_id'] = I('student_id',0);

        if($vote['student_id']>0){
            $vote['vote_type'] = I('vote_type',0);
            $vote['wx_id'] = $_SESSION['wx_id'];
            $vote['add_time'] = time();
            $where = "vote_type={$vote['vote_type']} AND wx_id='{$vote['wx_id']}' AND student_id={$vote['student_id']}";
            if($vote['vote_type']==0){
                //进步之星
                $vote['test_time'] = M('score')->where('rank_status=1')->order('test_time DESC')->getField('test_time');
                $where .= " AND test_time={$vote['test_time']}";
                $execute = "update otk_score set vote_num = vote_num + 1 where test_time={$vote['test_time']} AND student_id={$vote['student_id']}";
            }else{
                //纪律之星
                $execute = "update otk_score_new set vote_num = vote_num + 1 where student_id={$vote['student_id']}";
            }

            $isVoted = M('ScoreVote')->where($where)->find();

            //执行点赞/取消
            if(empty($isVoted)){//点赞
                $res = M('ScoreVote')->add($vote);
                if($res){
                    $result = M()->execute($execute);

                    if($result){
                        $return['status'] = 1;
                    }
                }
            }else{//取消
                if($vote['vote_type']==0){
                    //进步之星
                    $execute = "update otk_score set vote_num = vote_num - 1 where test_time={$vote['test_time']} AND student_id={$vote['student_id']}";
                }else{
                    //纪律之星
                    $execute = "update otk_score_new set vote_num = vote_num - 1 where student_id={$vote['student_id']}";
                }

                $res = M('ScoreVote')->where('id='.$isVoted['id'])->delete();
                if($res){
                    $result = M()->execute($execute);

                    if($result){
                        $return['status'] = 2;
                    }
                }
            }
        }
        $this->ajaxReturn($return);

    }

}