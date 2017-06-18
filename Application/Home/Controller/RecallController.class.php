<?php

namespace Home\Controller;
use Think\Controller;

/**
 * 
 * 
 */
class RecallController extends Controller
{

    /**
     * 初始化
     */
    protected function _initialize()
    {
        // $_SESSION['wx_id'] = 'oEjhSwiytpQ8_Kmf-EZV-h-veOoM';
//        $_SESSION['wx_id'] = 'oEjhSwq5k8Jhb-KQ_hHxB1rhsrmI';
//        get_wx_id();
    }

    public function reCall()
    {
        $str = empty($getStr) ? '耶耶耶耶耶' : $getStr;
        $str = $str . date('Ymd H:i:s');
        $name = 'zhangdada';

        $signPackage = reply_customer($name, $str);
        if ($signPackage->errcode == 40014) {
            unset($_SESSION['qy_access_token']);
            $signPackage = reply_customer($name, $str);
        }

        /*if ($name == 'yoyo') {
            $name = 'zhangdada';
            $signPackage = reply_customer($name, $str);
            var_dump($signPackage);
        }
        if ($name == 'zhangdada') {
            echo $_SESSION['qy_access_token'];
        }*/

        die;
    }

    public function validate()
    {
        /*$str = '';
        foreach($_REQUEST as $k => $v){
            $str .= ' '.$k.'=>'.$v;
        }
        reply_customer('zhangdada', $str);
        die;*/

        Vendor('WXBiz.WXBizMsgCrypt');

        // 假设企业号在公众平台上设置的参数如下
        $encodingAesKey = 'fc5UeBz1F5QHjbQaWJPccXSbMERbTCw4FOCSukSBQxp';
        $token = 'F1Lwh2';
        $corpId = C('WX_CONFIG.qyAppid');

        /*$sVerifyMsgSig = $_REQUEST['msg_signature'];
        $sVerifyTimeStamp = $_REQUEST['timestamp'];
        $sVerifyNonce = $_REQUEST['nonce'];
        $sVerifyEchoStr = $_REQUEST['echostr'];*/

/*// 需要返回的明文
        $sEchoStr = "";

        $wxcpt = new \WXBizMsgCrypt($token, $encodingAesKey, $corpId);
//        $wxcpt->WXBizMsgCrypt($token, $encodingAesKey, $corpId);
//        $errCode = $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);
        $errCode = $wxcpt->DecryptMsg($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyPostData, $sVerifyMsg);

        if ($errCode == 0) {
            //
            // 验证URL成功，将sEchoStr返回
            reply_customer('zhangdada', $sVerifyMsg.' 时间：'.date('Y-m-d H:i:s'));
            echo $sEchoStr;

        } else {
            reply_customer('zhangdada', $errCode.' 时间：'.date('Y-m-d H:i:s'));
            print("ERR: " . $errCode . "\n\n");
        }
        die;*/


        // $sReqMsgSig = HttpUtils.ParseUrl("msg_signature");
//        $sReqMsgSig = "477715d11cdb4164915debcba66cb864d751f3e6";
// $sReqTimeStamp = HttpUtils.ParseUrl("timestamp");
//        $sReqTimeStamp = "1409659813";
// $sReqNonce = HttpUtils.ParseUrl("nonce");
//        $sReqNonce = "1372623149";
// post请求的密文数据
// $sReqData = HttpUtils.PostData();
        /*$sReqMsgSig = $_REQUEST['msg_signature'];
        $sReqTimeStamp = $_REQUEST['timestamp'];
        $sReqNonce = $_REQUEST['nonce'];
        $sReqData = $_POST;*/

        $sReqMsgSig = $sVerifyMsgSig = $_GET['msg_signature'];
        $sReqTimeStamp = $sVerifyTimeStamp = $_GET['timestamp'];
        $sReqNonce = $sVerifyNonce = $_GET['nonce'];
        $sReqData = file_get_contents("php://input");;
        $sVerifyEchoStr = $_GET['echostr'];

        //$sReqData = "<xml><ToUserName><![CDATA[wx5823bf96d3bd56c7]]></ToUserName><Encrypt><![CDATA[RypEvHKD8QQKFhvQ6QleEB4J58tiPdvo+rtK1I9qca6aM/wvqnLSV5zEPeusUiX5L5X/0lWfrf0QADHHhGd3QczcdCUpj911L3vg3W/sYYvuJTs3TUUkSUXxaccAS0qhxchrRYt66wiSpGLYL42aM6A8dTT+6k4aSknmPj48kzJs8qLjvd4Xgpue06DOdnLxAUHzM6+kDZ+HMZfJYuR+LtwGc2hgf5gsijff0ekUNXZiqATP7PF5mZxZ3Izoun1s4zG4LUMnvw2r+KqCKIw+3IQH03v+BCA9nMELNqbSf6tiWSrXJB3LAVGUcallcrw8V2t9EL4EhzJWrQUax5wLVMNS0+rUPA3k22Ncx4XXZS9o0MBH27Bo6BpNelZpS+/uh9KsNlY6bHCmJU9p8g7m3fVKn28H3KDYA5Pl/T8Z1ptDAVe0lXdQ2YoyyH2uyPIGHBZZIs2pDBS8R07+qN+E7Q==]]></Encrypt><AgentID><![CDATA[218]]></AgentID></xml>";
        $sMsg = "";  // 解析之后的明文
        $wxcpt = new \WXBizMsgCrypt($token, $encodingAesKey, $corpId);
        $errCode = $wxcpt->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);
        if ($errCode == 0) {
            $xml = new DOMDocument();
            $xml->loadXML($sMsg);
            $reqToUserName = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;
            $reqFromUserName = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue;
            $reqCreateTime = $xml->getElementsByTagName('CreateTime')->item(0)->nodeValue;
            $reqMsgType = $xml->getElementsByTagName('MsgType')->item(0)->nodeValue;
            $reqContent = $xml->getElementsByTagName('Content')->item(0)->nodeValue;
            $reqMsgId = $xml->getElementsByTagName('MsgId')->item(0)->nodeValue;
            $reqAgentID = $xml->getElementsByTagName('AgentID')->item(0)->nodeValue;
            switch($reqContent){
                case "getRatio":
                    $mycontent="您好，马云！我知道您创建了阿里巴巴！";
                    break;
                case "马化腾":
                    $mycontent="您好，马化腾！我知道创建了企鹅帝国！";
                    break;
                case "史玉柱":
                    $mycontent="您好，史玉柱！我知道您创建了巨人网络！";
                    break;
                default :
                    $mycontent="你是谁啊？！一边凉快去！";
                    break;
            }
            $sRespData =
                "<xml>
<ToUserName><![CDATA[".$reqFromUserName."]]></ToUserName>
<FromUserName><![CDATA[".$corpId."]]></FromUserName>
<CreateTime>".sReqTimeStamp."</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[".$mycontent."]]></Content>
</xml>";
            $sEncryptMsg = ""; //xml格式的密文
            $errCode = $wxcpt->EncryptMsg($sRespData, $sReqTimeStamp, $sReqNonce, $sEncryptMsg);
            if ($errCode == 0) {
//file_put_contents('smg_response.txt', $sEncryptMsg); //debug:查看smg
                reply_customer('zhangdada', $sEncryptMsg.' 00时间：'.date('Y-m-d H:i:s'));

                print($sEncryptMsg);
            } else {
                reply_customer('zhangdada', $errCode.' ERR 11时间：'.date('Y-m-d H:i:s'));
                print($errCode . "\n\n");
            }
        } else {
            reply_customer('zhangdada', $errCode.' ERR2 22时间：'.date('Y-m-d H:i:s'));

            print($errCode . "\n\n");
        }
    }

}
