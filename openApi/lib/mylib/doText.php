<?php

require_once dirname(__FILE__) . "/msg.php";
require_once dirname(__FILE__) . "/doClickReg.php";
require_once dirname(__FILE__) . "/aboutReg.php";
require_once dirname(__FILE__) . "/../sql/MysqliDb.php";
require_once dirname(__FILE__) . "/../wechat/other/helper.php";

/**
 * 当接收到的是文本消息时，判断用户所处状态，执行相应操作
 * @param
 *
 */
function doText($xml)
{
    $FromUserName = $xml->getElementsByTagName('FromUserName')->item(0)->nodeValue;
    $ToUserName = $xml->getElementsByTagName('ToUserName')->item(0)->nodeValue;
    $db = initMySql();

    $content = $xml->getElementsByTagName('Content')->item(0)->nodeValue;
    $content = trim($content);

    $state = $db->where("openId", $FromUserName)->getOne("user");
    switch ($state["state"]) {

        case 0://0表示正常,执行查询操作

            $judeg = false;

            $content = analysisQuery($judeg, $content);//解析用户的查询指令

            if ($judeg == true) {//格式正确

                //根据关键字查找相关书籍
                $set = 'call query("%s")';
                $set = sprintf($set, $content);
                $data = $db->rawQuery($set);

                if (empty($data) == true) {//如果未找到书籍
                    $prompt = getPrompt("notFind");
                    replyMsgToUser($prompt, $FromUserName, $ToUserName);
                } else {//如果找到书籍

                    $first = 0;

                    $str = "n";
                    foreach ($data as $da) {//构造所回复的文本内容

                        $result = getUser($db, $da);
                        if ($result != "n") {
                            if ($first == 0) {
                                $str = $result;
                                $first = 1;
                            } elseif ($first == 1) {
                                $str .= "\n" . $result;
                            }
                        }
                    }

                    if ($str == "n") {//有书籍记录但所有作者均已下架
                        $prompt = getPrompt("notFind");
                        replyMsgToUser($prompt, $FromUserName, $ToUserName);
                    } else {//有售书记录
                        $prompt = getPrompt("findBook");
                        $prompt = sprintf($prompt, $str);
                        replyMsgToUser($prompt, $FromUserName, $ToUserName);

                    }
                }

            } else {//指令格式不正确
                $prompt = getPrompt("formatError");
                replyMsgToUser($prompt, $FromUserName, $ToUserName);

            }

            break;

        case 1://1表示正在登记联系方式

            $judge = false;
            $contect = analysis($judge, $content);
            if ($judge == true) {//如果输入格式正确

                insertContect($db, $contect, $FromUserName);//插入数据库

                $set = 'call stateTo0("%s")';
                $set = sprintf($set, $FromUserName);
                $db->rawQuery($set);//退出登记状态

                $db->where("openId", $FromUserName);//设置isReg位
                $isReg = array(
                    "isReg" => 1
                );
                $db->update("user", $isReg);

                $user = $db->where("openId", $FromUserName)->getOne("user");
                $prompt = formatReg($user);//设置提示格式

                replyMsgToUser($prompt, $FromUserName, $ToUserName);
            } else {//格式错误

                $db->where("openId", $FromUserName);
                $data = array(
                    "qq" => null,
                    "weChat" => null,
                    "tel" => null
                );
                $db->update("user", $data);//删除数据

                $db->where("openId", $FromUserName);//设置isReg位
                $isReg = array(
                    "isReg" => 0
                );
                $db->update("user", $isReg);

                $set = 'call stateTo0("%s")';
                $set = sprintf($set, $FromUserName);
                $db->rawQuery($set);//退出登记状态

                $prompt = getPrompt("remove");
                replyMsgToUser($prompt, $FromUserName, $ToUserName);
            }
            break;

        case 2://2表示正在查询“我的已发布信息”

            $judge = false;
            $index = analysisManag($judge, $content);
            if ($judge == true) {//如果输入格式正确



                $temp = setRemoveTip($db, $index, $FromUserName);

                if ($temp == "n") {
                    $prompt = getPrompt("removeNone");
                } else {
                    $set = 'call stateTo0("%s")';
                    $set = sprintf($set, $FromUserName);
                    $db->rawQuery($set);//退出管理状态

                    $prompt = getPrompt("removeTip");
                    $prompt = sprintf($prompt, $temp);
                }

                foreach ($index as $ind) {
                    $set = 'call isSellTo1("%s","%s")';
                    $set = sprintf($set, $ind, $FromUserName);
                    $db->rawQuery($set);//下架书籍
                }

                replyMsgToUser($prompt, $FromUserName, $ToUserName);


            } else {//如果输入格式错误
                $prompt = getPrompt("formatError");
                replyMsgToUser($prompt, $FromUserName, $ToUserName);

            }
            break;

        case 3://3表示正在举报不良用户
            break;

        default:
            $prompt = getPrompt("userStateError");
            replyMsgToUser($prompt, $FromUserName, $ToUserName);
            break;
    }
}


/**
 * 设置下架书籍的提示格式
 * 要考虑到输入的序号不存在的情况
 * @param db
 * @param array
 * @param string     
 * @return string
 */
function setRemoveTip(&$db, $index, $FromUserName)
{
    $firstLine = 0;
    $prompt = "n";

    foreach ($index as $ind) {
        $result = search($db, $ind, $FromUserName);
        if ($result[0] == 1) {
            if ($firstLine == 0) {
                $prompt = $result[1];
                $firstLine = 1;
            } elseif ($firstLine == 1) {
                $prompt .= "\n" . $result[1];
            }
        }
    }
    return $prompt;
}
/**
 * 根据ind值查找用户所对应的书籍
 * 要考虑用户所输入的序号不存在的情况
 * @param db
 * @param int
 * @param string
 * @return array 当返回的数组0号元素为1时，1号元素为所找到的用户书籍
 * 
 */
function search(&$db, $ind, $FromUserName)
{
    $re = array("0", "");

    $db->where("openId", $FromUserName);
    $db->where("number", $ind);
    $db->where("isSell", 0);
    $result = $db->getOne("bookUser");
    if (empty($result) == true) {
        return $re;
    } else {
        $db->where("bookId", $result["bookId"]);
        $temp = $db->getOne("book");
        $re[0] = 1;
        $re[1] = $ind . "." . "《" . $temp["title"] . "》";
        return $re;
    }
}

/**
 * 分析用户输入的序号字符串。当judge为true时$content有效
 * @param bool
 * @param string
 * @return array
 */

function analysisManag(&$judge, $content)
{
    $content = str_replace("，", ",", $content);//将中文逗号换成英文逗号
    $content = str_replace(".", "", $content);
    $content = str_replace(" ", "", $content);
    $content = explode(",", $content);//以逗号分割字符串
    foreach ($content as $con) {
        $con = trim($con);
    }
    $content = array_unique($content);//去除重复值
    foreach ($content as $con) {
        if (is_numeric($con) == true) {
            $judge = true;
        }
    }
    return $content;

}


/**
 * 分析用户的查询指令
 * @param bool
 * @param string
 * @return string
 */
function analysisQuery(&$judge, $content)
{
    $content = str_replace("，", ",", $content);
    $content = str_replace(" ", "", $content);
    $content = explode(",", $content);//以逗号分割字符串
    foreach ($content as $con) {
        if ($con != null) {
            $judge = true;
        }
    }
    $content = implode(" ", $content);//合并字符
    return $content;


}


/**
 * 根据书籍查找在售的用户并format
 * 无在售记录时返回“n”
 * @param db
 * @param array
 * @return string
 * 
 */

function getUser(&$db, $data)
{
    $str = "n";

    $db->join("user u", "b.openId=u.openId", "RIGHT");
    $db->where("b.bookId", $data["bookId"]);
    $db->where("b.isSell", 0);
    $db->where("u.isReg", 1);
    $user = $db->get("bookUser b", null, "u.qq, u.weChat, u.tel");

    if (empty($user) == false) {//如果有在售用户

        //够造书名，作者，出版社
        $title = $data["title"];
        if ($data["subtitle"] != null) {
            $title .= "——" . $data["subtitle"];
        }
        $publisher = $data["publisher"];
        $authors = $db->where("bookId", $data["bookId"])->getValue("bookAuthor", "author", null);
        $author = $authors[0];
        for ($i = 1; $i < count($authors); $i++) {
            $author .= "，" . $authors[$i];
        }
        $prompt = getPrompt("book");
        $prompt = sprintf($prompt, $title, $author, $publisher) . "\n";

        //添加在售用户
        foreach ($user as $us) {
            if ($us["qq"] != null) {
                $prompt .= "\n" . "QQ:" . $us["qq"];
            }
            if ($us["weChat"] != null) {
                $prompt .= "\n" . "WX:" . $us["weChat"];
            }
            if ($us["tel"] != null) {
                $prompt .= "\n" . "TEL:" . $us["tel"];
            }
            $prompt .= "\n";
        }
        $str = "------------\n" . $prompt;
    }

    return $str;

}