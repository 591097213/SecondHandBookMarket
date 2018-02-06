<?php

/**
 * 收法消息相关
 *
 */

require_once dirname(__FILE__) . "/../wechat/other/helper.php";
require_once dirname(__FILE__) . "/../wechat/crypt/WXBizMsgCrypt.php";
require_once dirname(__FILE__) . "/../wechat/other/app_api.php";
require_once dirname(__FILE__) . "/msgTypeCode.php";

/**
 * 企业接收用户在应用的聊天窗口输入后传递过来的数据
 * 支持文本消息、图片消息、语音消息、视频消息、文件消息、文本卡片消息、图文消息等消息类型
 * @param int 接收消息的应用的agentid
 * @return DOMDocument
 */
function receiveMsgFromQyWx($agentId)
{
    //读取config文件里面的配置
    $appConfigs = loadConfig();
    $config = getConfigByAgentId($agentId);

    $token = $config->Token;
    $encodingAesKey = $config->EncodingAESKey;
    $corpId = $appConfigs->CorpId;

    $sReqMsgSig = $_GET["msg_signature"];
    $sReqTimeStamp = $_GET["timestamp"];
    $sReqNonce = $_GET["nonce"];
    $sReqData = file_get_contents("php://input");

    $sMsg = ""; // 解析之后的明文
    $wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);
    $errCode = $wxcpt->DecryptMsg($sReqMsgSig, $sReqTimeStamp, $sReqNonce, $sReqData, $sMsg);

    if ($errCode == 0) {
        // 解密成功，sMsg即为xml格式的明文
        $xml = new DOMDocument(); //此处会有waring，但不影响结果
        $xml->loadXML($sMsg);

        return $xml;

    } else {
        print("ERR: " . $errCode . "\n\n");
    }
}

/**
 * 向用户发送文本消息
 * @param string 向用户的文本消息的内容
 * @param string $ToUserName
 * @param string $FromUserName
 */
function replyMsgToUser($Content, $ToUserName, $FromUserName)
{

    $appConfigs = loadConfig();
    $config = getConfigByAgentId(1000004);

    $token = $config->Token;
    $encodingAesKey = $config->EncodingAESKey;
    $corpId = $appConfigs->CorpId;

    // 需要发送的明文消息
    // TODO：根据用户提交过来的操作封装此数据包
    $sRespData = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                </xml>";

    $CreateTime = time(); //创建时间
    $NonceStr = createNonceStr(); //随机字串
    $temp = sprintf($sRespData, $ToUserName, $FromUserName, $CreateTime, $Content);

    $sEncryptMsg = ""; //xml格式的密文
    $wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);
    $errCode = $wxcpt->EncryptMsg($temp, $CreateTime, $NonceStr, $sEncryptMsg);

    if ($errCode == 0) {
        echo $sEncryptMsg;
    } else {
        print("ERR: " . $errCode . "\n\n");
        // exit(-1);
    }
}

//text msg example:
// <xml>
// <ToUserName><![CDATA[toUser]]></ToUserName>
// <FromUserName><![CDATA[fromUser]]></FromUserName>
// <CreateTime>1348831860</CreateTime>
// <MsgType><![CDATA[text]]></MsgType>
// <Content><![CDATA[this is a test]]></Content>
// <MsgId>1234567890123456</MsgId>可用于消息重排，判断是否为重复收到的消息
// <AgentID>1</AgentID>
// </xml>

// scancode_waitmsg example：
// <xml><ToUserName><![CDATA[toUser]]></ToUserName>
// <FromUserName><![CDATA[FromUser]]></FromUserName>
// <CreateTime>1408090606</CreateTime>
// <MsgType><![CDATA[event]]></MsgType>
// <Event><![CDATA[scancode_waitmsg]]></Event>
// <EventKey><![CDATA[6]]></EventKey>菜单的键值
// <ScanCodeInfo>
//    <ScanType><![CDATA[qrcode]]></ScanType>二维码为qrcode，条码为barcode
//    <ScanResult><![CDATA[2]]></ScanResult>二维码为扫描到的内容,条码为“编码规则字段，码中的内容”
// </ScanCodeInfo>
// <AgentID>1</AgentID>
// </xml>
//可用FromUserName+CreateTime重排判重

//subscribe msg example:
// <xml>
// <ToUserName><![CDATA[toUser]]></ToUserName>
// <FromUserName><![CDATA[UserID]]></FromUserName>
// <CreateTime>1348831860</CreateTime>
// <MsgType><![CDATA[event]]></MsgType>
// <Event><![CDATA[subscribe]]></Event>
// <AgentID>1</AgentID>
// </xml>

// click 事件：
// <xml>
// <ToUserName><![CDATA[toUser]]></ToUserName>
// <FromUserName><![CDATA[FromUser]]></FromUserName>
// <CreateTime>123456789</CreateTime>
// <MsgType><![CDATA[event]]></MsgType>
// <Event><![CDATA[click]]></Event>
// <EventKey><![CDATA[EVENTKEY]]></EventKey>
// <AgentID>1</AgentID>
// </xml>


/**
 * 判断消息类型并返回类型代码
 * @param DOMDocument需要判断的消息类型
 * @return 消息类型代码
 */

function judgeMsg(&$xml)
{
    $msgType = $xml->getElementsByTagName('MsgType')->item(0)->nodeValue;
    switch ($msgType) {

        case "text":
            return msgTypeCode::$Text;
            break;

        case "event":
            $event = $xml->getElementsByTagName('Event')->item(0)->nodeValue;

            switch ($event) {
                case "scancode_waitmsg":
                    return msgTypeCode::$ScancodeWaitMsg;
                    break;

                case "subscribe":
                    return msgTypeCode::$Subscribe;
                    break;

                case "click":
                    $eventkey = $xml->getElementsByTagName('EventKey')->item(0)->nodeValue;

                    switch ($eventkey) {
                        case "buy":
                            return msgTypeCode::$ClickBuy;
                            break;

                        case "reg":
                            return msgTypeCode::$ClickReg;
                            break;

                        case "manag":
                            return msgTypeCode::$ClickManag;
                            break;

                        default:
                            return msgTypeCode::$UnknowType;
                            break;
                    }
                    break;

                default:
                    return msgTypeCode::$UnknowType;
                    break;
            }
            break;

        default:
            return msgTypeCode::$UnknowType;
            break;
    }

}
