<?php
require_once '../lib/mysql/MysqliDb.php';
require_once '../lib/wechat/msg.php';
require_once '../lib/wechat/doText.php';
require_once '../lib/wechat/doScancodeWaitMsg.php';
define("receiveAgent", "1000004");

$msg = receiveMsgFromQyWx(receiveAgent); //接收消息
echo "test\n";
var_export($msg[1]);

if ($msg[0] === 0) { //成功接收
    $judge = judgeMsg($msg[1]);
    switch ($judge) {
        case GlobalCode::$UnknowType:
            print("ERR: " . ErrorCode::$UnknowMsgType . "\n\n"); //类型不合法
            break;
        case GlobalCode::$Text: //文本消息
            doText($msg[1]);
            break;
        case GlobalCode::$ScancodeWaitMsg: //扫描消息
            doScancodeWaitMsg($msg[1]);
            break;
    }
} else { //接收消息过程中出错
    print("ERR: " . $msg[0] . "\n\n");
}
