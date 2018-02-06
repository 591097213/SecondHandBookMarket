<?php

require_once dirname(__FILE__) . "/msg.php";
require_once dirname(__FILE__) . "/../sql/MysqliDb.php";

/**
 * 分析用户输入的登记联系方式文本
 * 如果输入格式正确则将judge设置为true
 * 返回联系方式数组
 * @param bool 判断是否格式正确
 * @param string 消息
 * @return array 存储联系方式的数组
 */

function analysis(&$judge, $content)
{
    $contect = array(
        "qq" => null,
        "weChat" => null,
        "tel" => null
    );

    $content = str_replace("，", ",", $content);//将中文逗号换成英文逗号
    $content = explode(",", $content);//以逗号分割字符串
    foreach ($content as $con) {
        $con = trim($con);
        switch (substr($con, 0, 1)) {
            case "Q":
            case "W":
                switch (substr($con, 0, 2)) {
                    case "QQ"://不能在这里就设置judge=true，因为用户可能只输入“QQ，WX，TEL”这样的字段。
                        $contect["qq"] = substr($con, 2);
                        break;
                    case "WX":
                        $contect["weChat"] = substr($con, 2);
                        break;
                }
                break;

            case "T":
                if (substr($con, 0, 3) == "TEL") {
                    $contect["tel"] = substr($con, 3);
                }
                break;
        }
    }


    foreach ($contect as $con) {//判断是否有正确的字段
        if ($con != null) {
            $judge = true;
        }
    }

    return $contect;
}

/**
 * 将用户联系方式插入数据库
 * @param de
 * @param array
 * @param string
 * @return int 0表示插入错误，>0表示正常插入
 */
function insertContect(&$db, $contect, $FromUserName)
{
    $db->where("openId", $FromUserName);

    $result = $db->update("user", $contect);
    return $result;
}


