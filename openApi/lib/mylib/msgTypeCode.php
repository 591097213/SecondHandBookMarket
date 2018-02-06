<?php

/**
 * msgTypeCode 说明.
 * <ul>
 *    <li>1000: UnknowType</li>
 *    <li>1001: scancode_waitmsg事件</li>
 *    <li>1002: text文本消息</li>
 *    <li>1003: 用户关注消息</li>
 *    <li>1004: 用户点击“买书”按钮</li>
 *    <li>1005: 用户点击“增/改联系方式”按钮</li>
 *    <li>1006: 用户点击“管理我的在售书籍”按钮</li>
 * </ul>
 */
class msgTypeCode
{
    public static $UnknowType = 1000;
    public static $ScancodeWaitMsg = 1001;
    public static $Text = 1002;
    public static $Subscribe = 1003;
    public static $ClickBuy = 1004;
    public static $ClickReg = 1005;
    public static $ClickManag = 1006;
}
