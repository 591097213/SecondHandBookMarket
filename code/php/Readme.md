注意事项：
=======

1. `WXBizMsgCrypt.php`文件提供了`WXBizMsgCrypt`类的实现，是用户接入企业微信的接口类。`Sample.php`提供了示例以供开发者参考。`errorCode.php`, `pkcs7Encoder.php`, `sha1.php`, `xmlparse.php`文件是实现这个类的辅助类，开发者无须关心其具体实现。

2. `WXBizMsgCrypt`类封装了`VerifyURL`, `DecryptMsg`, `EncryptMsg`三个接口，分别用于开发者验证回调url，收到用户回复消息的解密以及开发者回复消息的加密过程。使用方法可以参考`Sample.php`文件。

3. 加解密协议请参考企业微信官方文档。