<?php
require_once dirname(__FILE__) . "/msg.php";
require_once dirname(__FILE__) . "/../wechat/other/helper.php";

/**
 * 当用户点击“增/改联系方式”按钮时给用户提示，用户状态state并且切换到登记状态
 * @param DOMDocument
 * 
 */
function doClickReg(&$xml)
{
    $FromUserName = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue; //发送消息的UserID
    $ToUserName = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;

    $db = initMySql();
    //用户状态置1，表示处于查询态
    $set = 'call stateTo1("%s")';
    $set = sprintf($set, $FromUserName);
    $db->rawQuery($set);

    $prompt = getPrompt("regTip");
    //查询用户是否已注册，若是，则加载提示消息
    $user = $db->where("openId", $FromUserName)->getOne("user");
    if ($user["isReg"] == 1) {
        $prompt = formatReg($user) . "\n" . $prompt;
    }

    replyMsgToUser($prompt, $FromUserName, $ToUserName);
}

/**
 * 设置用户联系方式的输出格式
 * @param array 用户信息数组
 * @return string 格式化后的字符串
 */

function formatReg($user)
{
    $prompt = getPrompt("regTip1");
    if ($user["qq"] != null) {
        $prompt .= "\n" . "QQ: " . $user["qq"];
    }
    if ($user["weChat"] != null) {
        $prompt .= "\n" . "WX: " . $user["weChat"];
    }
    if ($user["tel"] != null) {
        $prompt .= "\n" . "TEL: " . $user["tel"];
    }

    return $prompt;

}