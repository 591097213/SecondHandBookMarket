<?php
require_once dirname(__FILE__) . "/msg.php";
require_once dirname(__FILE__) . "/../wechat/other/helper.php";

/**
 * 提示用户事件类型未知
 * @param DOMDocument
 * 
 */
function doDefault(&$xml)
{
    $prompt = getPrompt("unknowEvent");
    $FromUserName = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue; //发送消息的UserID
    $ToUserName = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;

    replyMsgToUser($prompt, $FromUserName, $ToUserName);
}
