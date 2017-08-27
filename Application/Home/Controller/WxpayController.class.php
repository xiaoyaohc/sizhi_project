<?php
namespace Home\Controller;
use Home\Model\ClassModel;
use Home\Model\ClassReserveModel;
use Home\Model\UserModel;
use Think\Controller;
//微信支付类
class WxpayController extends Controller {
    public function _initialize() {
        header("Content-type: text/html; charset=utf-8");
    }
    //获取access_token过程中的跳转uri，通过跳转将code传入jsapi支付页面
    public function js_api_call() {

        //header_index(C('WX_CONFIG.domain'));
        $openid = $_SESSION['wx_id'];
        $order_sn = I('order_sn');

        if (empty($order_sn)){
            $url = "http://".C('WX_CONFIG.domain')."/Home/Index/index/";
            Header("Location: $url"); 
        }
        $db    = M('Pay');
        $_POST['order_id'] = $order_sn;
        $_POST['pay_status'] = 0;
        
        $sql  = "SELECT a.*,b.teacher_name,c.class_name,c.class_price,c.times,c.class_room,c.class_type,c.period,d.area_name,c.time_interval,ca.week_id FROM otk_pay a LEFT JOIN otk_teacher b ON a.teacher_id = b.teacher_id LEFT JOIN otk_class c on a.class_id = c.class_id  LEFT JOIN otk_area d on a.area_id = d.id LEFT JOIN otk_class_times ca on ca.class_id=a.class_id WHERE a.order_id ='".$order_sn."' and a.pay_status = 0 limit 1";
        $res_pay = D('otk_pay')->query($sql);
        if($res_pay[0]){
            $res = array(
                    'order_sn' => $res_pay[0]['order_id'],
                    'order_amount' => $res_pay[0]['pay_money']
                    );
            $content = $res_pay['class_name'].$res_pay['teacher_name'];
        }else{
            $url = C('WX_CONFIG.domain')."/Home/User/UserCenter/";
            Header("Location: http://$url"); 
        }
        






        vendor('Weixinpay.WxPayPubHelper');
        //使用jsapi接口
        $jsApi = new \JsApi_pub();

        /*
        //=========步骤1：网页授权获取用户openid============
        //通过code获得openid
        if (!isset($_GET['code'])){
            //触发微信返回code码
            $url = $jsApi->createOauthUrlForCode('http://yjty.weifenz.com/Home/Wxpay/js_api_call/?order_sn='.$order_sn);
            //$url = $jsApi->createOauthUrlForCode(\WxPayConf_pub::JS_API_CALL_URL);
            
            Header("Location: $url"); 
        }else{
            //获取code码，以获取openid
            $code = $_GET['code'];
            $jsApi->setCode($code);
            $openid = $jsApi->getOpenId();
        }
        */
        

        
        //=========步骤2：使用统一支付接口，获取prepay_id============
        //使用统一支付接口
        $unifiedOrder = new \UnifiedOrder_pub();
        //设置统一支付接口参数
        //设置必填参数
        //appid已填,商户无需重复填写
        //mch_id已填,商户无需重复填写
        //noncestr已填,商户无需重复填写
        //spbill_create_ip已填,商户无需重复填写
        //sign已填,商户无需重复填写
        $total_fee = $res['order_amount']*100;
        //$total_fee = 1;
        $body = $content.$res['order_sn'];
        $unifiedOrder->setParameter("openid", "$openid");//用户标识
        $unifiedOrder->setParameter("body", $body);//商品描述
        //自定义订单号，此处仅作举例
        $out_trade_no = $res['order_sn'];
        $unifiedOrder->setParameter("out_trade_no", $out_trade_no);//商户订单号 
        $unifiedOrder->setParameter("total_fee", $total_fee);//总金额
        //$unifiedOrder->setParameter("attach", "order_sn={$res['order_sn']}");//附加数据 
        $unifiedOrder->setParameter("notify_url", \WxPayConf_pub::NOTIFY_URL);//通知地址 
        $unifiedOrder->setParameter("trade_type", "JSAPI");//交易类型
        //非必填参数，商户可根据实际情况选填
        //$unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号  
        //$unifiedOrder->setParameter("device_info","XXXX");//设备号 
        //$unifiedOrder->setParameter("attach","XXXX");//附加数据 
        //$unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
        //$unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间 
        //$unifiedOrder->setParameter("goods_tag","XXXX");//商品标记 
        //$unifiedOrder->setParameter("openid","XXXX");//用户标识
        //$unifiedOrder->setParameter("product_id","XXXX");//商品ID
        $prepay_id = $unifiedOrder->getPrepayId();
        //=========步骤3：使用jsapi调起支付============
        $jsApi->setPrepayId($prepay_id);
        $jsApiParameters = $jsApi->getParameters();
        $wxconf = json_decode($jsApiParameters, true);

        if ($wxconf['package'] == 'prepay_id=') {
            $this->error('当前订单存在异常，不能使用支付');
        }

        $stuList = UserModel::getStudentList($openid);//获取用户对应学生列表
        $this->assign('stuList', $stuList);
        $this->assign('openId', $openid);
        $this->assign('res', $res);
        $this->assign('Info',$res_pay[0]);
        $media_title = "确认支付";
        $this->assign('media_title', $media_title);
        $this->assign('jsApiParameters', $jsApiParameters);
        $this->display('pay');
    }

    //异步通知url，商户根据实际开发过程设定
    public function notify_url() {
        vendor('Weixinpay.WxPayPubHelper');
        //使用通用通知接口
        $notify = new \Notify_pub();
        //存储微信的回调
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];  
        $notify->saveData($xml);
        //验证签名，并回应微信。
        //对后台通知交互时，如果微信收到商户的应答不是成功或超时，微信认为通知失败，
        //微信会通过一定的策略（如30分钟共8次）定期重新发起通知，
        //尽可能提高通知的成功率，但微信不保证通知最终能成功。
        if($notify->checkSign() == FALSE){
            $notify->setReturnParameter("return_code", "FAIL");//返回状态码
            $notify->setReturnParameter("return_msg", "签名失败");//返回信息
        }else{
            $notify->setReturnParameter("return_code", "SUCCESS");//设置返回码
        }
        $returnXml = $notify->returnXml();
        //==商户根据实际情况设置相应的处理流程，此处仅作举例=======
        //以log文件形式记录回调信息
        //$log_name = "./Logs/notify_url.log";//log文件路径
        //$this->log_result($log_name, "【接收到的notify通知】:\n".$xml."\n");

        $parameter = $notify->xmlToArray($xml);
        //$this->log_result($log_name, "【接收到的notify通知】:\n".$parameter."\n");
        if($notify->checkSign() == TRUE){
            if ($notify->data["return_code"] == "FAIL") {
                //此处应该更新一下订单状态，商户自行增删操作
                $this->saveStatus(2,$parameter['openid'],$parameter['out_trade_no']);
                $this->saveLog($parameter['out_trade_no'],$parameter['openid'],"【通信出错】:\n".$xml."\n",2);
                //$this->log_result($log_name, "【通信出错】:\n".$xml."\n");
                //更新订单数据【通信出错】设为无效订单
                echo 'error';
                
            }else if($notify->data["result_code"] == "FAIL"){
                //此处应该更新一下订单状态，商户自行增删操作
                $this->saveStatus(3,$parameter['openid'],$parameter['out_trade_no']);
                $this->saveLog($parameter['out_trade_no'],$parameter['openid'],"【业务出错】:\n".$xml."\n",3);
                //$this->log_result($log_name, "【业务出错】:\n".$xml."\n");
                //更新订单数据【通信出错】设为无效订单
                echo 'error';
                
            }else{
                //
                //$this->log_result($log_name, "【支付成功】:\n".$xml."\n");
                //我这里用到一个process方法，成功返回数据后处理，返回地数据具体可以参考微信的文档
                //$res = $this->process($parameter);
                $res = $this->saveStatus(1,$parameter['openid'],$parameter['out_trade_no'],$parameter['transaction_id']);
                $this->saveLog($parameter['out_trade_no'],$parameter['openid'],"【支付成功】:\n".$xml."\n",1);
                if ($res) {
                    //处理成功后输出success，微信就不会再下发请求了
                    echo 'success';
                }else {
                    //没有处理成功，微信会间隔的发送请求
                    echo 'error';
                }
                
            }
        }
    }



    //保存订单状态
    //1：订单支付成功
    //2：订单支付失败，通信错误
    //3：订单支付失败，业务错误
    //0：订单未支付
    private function saveStatus($status,$openid,$out_trade_no,$transaction_id){
        $db = M('Pay');
        $res  = $db->where("wx_id='{$openid}' and order_id='{$out_trade_no}'")->find();
        if($res['id'] && $res['pay_status'] != 1){
            $_POST['pay_status']      = $status;
            $_POST['pay_time']          = time();
            $_POST['id']                = $res['id'];
            $_POST['transaction_id']    = $transaction_id;
            //$_POST['pay_source']    = 1;
            $res_pay = $db->save($_POST);
            if (empty($res_pay)){
                return false;
            }
        }

        return true;
    }



    private function saveLog($ord_id,$openid,$logMessage,$payStatus){
        $db = M('wx_callback_log');
        $_POST['ord_id']      = $ord_id;
        $_POST['addTime']     = time();
        $_POST['logMessage']  = $logMessage;
        $_POST['openid']      = $openid;
        $_POST['payStatus']   = $payStatus;
        $res = $db->add($_POST);
    }


    //支付成功展示页
    public function success()
    {
        //header_index(C('WX_CONFIG.domain'));
        $order_sn = I('order_sn');
//        $sql  = "SELECT a.*,b.teacher_name,c.class_name,c.class_price,c.times,c.class_room,c.class_type,d.area_name FROM otk_pay a LEFT JOIN  LEFT JOIN otk_teacher b ON a.teacher_id = b.teacher_id LEFT JOIN otk_class c on a.class_id = c.class_id  LEFT JOIN otk_area d on a.area_id = d.area_id WHERE a.order_id ='".$order_sn."' and a.pay_status = 1 limit 1";
//        $res_pay = D('otk_pay')->query($sql);
        $PayModel = M('Pay');
        $res = $PayModel->where("order_id='{$order_sn}'")->find();
        if ($res['pay_status'] != 1) {
            $res['pay_status'] = 1;
            $res['transaction_id'] = 2147483647;
            $PayModel->save($res);

            if ($res['pay_status'] == 1) {
                $userInfo = UserModel::getInfo($res['wx_id']);
                $classInfo = M('Class')->where('class_id=' . $res['class_id'])->find();
                $stuInfo = M('Student')->where('student_id=' . $res['stu_id'])->find();
                //代理订单表
                if (!empty($userInfo['agent_id']) && !empty($classInfo['agent_ratio'])) {
                    $agent = array();
                    $agentInfo = M('Agent')->where('id=' . $userInfo['agent_id'])->find();
                    $agent['agent_id'] = $userInfo['agent_id'];
                    $agent['order_id'] = $res['order_id'];
                    $agent['pay_wx_id'] = $res['wx_id'];
                    $agent['pay_money'] = $res['pay_money'];
                    $agent['pay_time'] = $res['pay_time'];
                    $agent['pay_status'] = $res['pay_status'];
                    $agent['agent_ratio'] = empty($classInfo['agent_ratio']) ? '0.00' : $classInfo['agent_ratio'];
                    $agent['agent_wx_id'] = $agentInfo['wx_id'];
                    $agent['agent_name'] = $agentInfo['name'];
                    $agent['accounts'] = $agentInfo['accounts'];
                    $agent['income'] = $res['pay_money'] * $agent['agent_ratio'];
                    $agent['class_id'] = $res['class_id'];
                    $agent['class_name'] = $classInfo['class_name'];
                    $agent['add_time'] = $res['add_time'];

                    M('AgentOrder')->add($agent);

                    //代理商消息推送
                    $agentStr = '亲，您刚刚又接了个单，订单号“' . $agent['order_id'] . '”，金额：' . $agent['pay_money'] . '元，代理金：' . $agent['income'] . '元。 ';
                    $agentName = $agent['accounts'];
                    $signPackage = reply_customer($agentName, $agentStr);
                    if ($signPackage->errcode == 40014) {
                        unset($_SESSION['qy_access_token']);
                        $signPackage = reply_customer($agentName, $agentStr);
                    }
                    //
                    $agentMsg = array();
                    $agentMsg = array(
                        'agent_order_id' => $agent['order_id'],
                        'errcode' => $signPackage->errcode,
                        'errmsg' => $signPackage->errmsg,
                        'msgtype' => 0,
                        'agent_id' => $agent['agent_id'],
                        'accounts' => $agent['accounts'],
                        'add_time' => time()
                    );
                    M('AgentWxmsg')->add($agentMsg);
                }

                //教师订单表
                $teachInfo = M('teacher')->where('teacher_id=' . $res['teacher_id'])->find();
                if (!empty($classInfo['proportion']) && !empty($teachInfo['accounts'])) {
                    $teach = array();
                    $teach['teacher_id'] = $res['teacher_id'];
                    $teach['order_id'] = $res['order_id'];
                    $teach['pay_wx_id'] = $res['wx_id'];
                    $teach['pay_money'] = $res['pay_money'];
                    $teach['pay_time'] = $res['pay_time'];
                    $teach['pay_status'] = $res['pay_status'];
                    $teach['teach_ratio'] = empty($classInfo['proportion']) ? '0.00' : $classInfo['proportion'];
                    $teach['teach_wx_id'] = $teachInfo['wx_id'];
                    $teach['teach_own_name'] = $teachInfo['teacher_name'];
                    $teach['accounts'] = $teachInfo['accounts'];
                    $teach['income'] = $res['pay_money'] * $teach['teach_ratio'];
                    $teach['class_id'] = $res['class_id'];
                    $teach['class_name'] = $classInfo['class_name'];
                    $teach['add_time'] = $res['add_time'];

                    $result = M('TeachOrder')->add($teach);

                    //教师消息推送
                    $teachPrice = $classInfo['class_price'] * $classInfo['proportion'];
                    $teachStr = '亲，您刚刚又接了个单，订单号“' . $res['order_id'] . '”，团购价：' . $res['pay_money'] . '元，结算价：' . $teachPrice . '元。课程：' . $classInfo['class_name'] . '，学生姓名：' . $stuInfo['student_name'] . '，联系电话：' . $stuInfo['contact'];
                    if(!$result){
                        $teachStr = M('TeachOrder')->getLastSql();
                    }
                    $teachName = $teachInfo['accounts'];
                    $signPackage = reply_customer($teachName, $teachStr);
                    if ($signPackage->errcode == 40014) {
                        unset($_SESSION['qy_access_token']);
                        $signPackage = reply_customer($teachName, $teachStr);
                    }

                    $teachMsg = array();
                    $teachMsg = array(
                        'agent_order_id' => $res['order_id'],
                        'errcode' => $signPackage->errcode,
                        'errmsg' => $signPackage->errmsg,
                        'msgtype' => 1,
                        'agent_id' => $res['teacher_id'],
                        'accounts' => $teach['accounts'],
                        'add_time' => time()
                    );
                    M('AgentWxmsg')->add($teachMsg);
                }

                //区域订单表
                $areaInfo = M('area')->where('id=' . $teachInfo['area_id'])->find();
                if (!empty($areaInfo['accounts']) && !empty($areaInfo['ratio'])) {
                    $area = array();
                    $area['area_id'] = $teachInfo['area_id'];
                    $area['order_id'] = $res['order_id'];
                    $area['pay_wx_id'] = $res['wx_id'];
                    $area['pay_money'] = $res['pay_money'];
                    $area['pay_time'] = $res['pay_time'];
                    $area['pay_status'] = $res['pay_status'];
                    $area['area_ratio'] = empty($areaInfo['ratio']) ? '0.00' : $areaInfo['ratio'];
                    $area['area_wx_id'] = $areaInfo['wx_id'];
                    $area['area_own_name'] = $areaInfo['name'];
                    $area['accounts'] = $areaInfo['accounts'];
                    $area['income'] = $res['pay_money'] * $areaInfo['ratio'];
                    $area['class_id'] = $res['class_id'];
                    $area['class_name'] = $classInfo['class_name'];
                    $area['add_time'] = $res['add_time'];

                    M('AreaOrder')->add($area);

                    //区域代理消息推送
                    $areaPrice = $res['pay_money'] * $area['area_ratio'];
                    $areaStr = '亲，您刚刚又接了个单，订单号“' . $res['order_id'] . '”，团购价：' . $res['pay_money'] . '元，结算价：' . $areaPrice . '元。课程：' . $classInfo['class_name'] . '，学生姓名：' . $stuInfo['student_name'] . '，联系电话：' . $stuInfo['contact'];
                    $areaName = $areaInfo['accounts'];
                    $signPackage = reply_customer($areaName, $areaStr);
                    if ($signPackage->errcode == 40014) {
                        unset($_SESSION['qy_access_token']);
                        $signPackage = reply_customer($areaName, $areaStr);
                    }
                    $areaMsg = array();
                    $areaMsg = array(
                        'agent_order_id' => $res['order_id'],
                        'errcode' => $signPackage->errcode,
                        'errmsg' => $signPackage->errmsg,
                        'msgtype' => 2,
                        'agent_id' => $area['area_id'],
                        'accounts' => $area['accounts'],
                        'add_time' => time()
                    );
                    M('AgentWxmsg')->add($areaMsg);
                }

                //支付成功后小班课程预约
                if ($res['id'] && $res['pay_status'] == 1) {
                    $classType = ClassModel::classType($res['class_id']);//课程类型为2是小班
                    if ($classType == 2) {
                        //预约操作
                        if (empty($_SESSION[$res['order_id']])) {
                            ClassReserveModel::classReserve($res['id']);
                        }
                    }
                }
            }

            $media_title = "购买成功";
            //$this->assign('media_title', $media_title);
            $this->assign('res', $res);
            $this->display('paySuccess');
        }
    }
}
?>