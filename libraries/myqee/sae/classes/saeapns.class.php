<?php
/**
 * Apple 应用消息推送服务
 *
 * <code>
 * <?php
 * $cert_id = 1;
 * $device_token = "xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx xxxxxxxx";
 *
 * $message = "测试消息";
 * $body = array(
 *     'aps' => array( 'alert' => $message , 'badge' => 1, 'sound' => 'in.caf')
 * );
 * $apns = new SaeAPNS();
 * $result = $apns->push( $cert_id , $body , $device_token );
 *
 * if( $result && is_array($result) ){
 *     echo '发送成功！';
 *     var_dump( $result );
 * }
 * else {
 *     echo '发送失败。';
 *     var_dump($apns->errno(), $apns->errmsg());
 * }
 * ?>
 * </code>
 *
 * 错误码参考：
 *  - errno: 0        成功
 *  - errno: -1        PUSH服务器数据库不能连接
 *  - errno: -2        权限不足，即accesskey参数有错
 *  - errno: -3        证书序号错误，即num参数有错
 *  - errno: -4        证书不存在
 *  - errno: -5        消息进入推送队列出错
 *  - errno: -6        推给的用户Iphone的token错误，即client参数为空
 *  - errno: -7        推给的用户Iphone的token错误，即client参数格式不正确
 *  - errno: -8        未知错误
 *  - errno: -9        参数错误
 *
 * @package sae
 * @author Elmer Zhang
 *
 */
class SaeAPNS extends SaeObject
{
    private $_errno = SAE_Success;
    private $_errmsg = "OK";
    private $_errmsgs = array
    (
        -1 => "push service database error",
        -2 => "authorize faild",
        -3 => "certificate number error",
        -4 => "certificate does not exist",
        -5 => "error when pushing to the queue",
        -6 => "client token can not be empty",
        -7 => "invalid format of client token",
        -8 => "unknown error",
    );

    /**
     * @ignore
     */
    const baseurl = "http://push.sae.sina.com.cn/api.php";

    /**
     * 构造对象
     *
     */
    function __construct() {
    }

    /**
     * 推送消息
     *
     * @param int $cert_id  许可证序号
     * @param array $body 消息体（包括消息、提醒声音等等），格式请参考示例和{@link http://developer.apple.com/library/ios/#documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/ApplePushService/ApplePushService.html#//apple_ref/doc/uid/TP40008194-CH100-SW1 Apple官方文档}
     * @param string $device_token 设备令牌
     * @return bool 成功返回true，失败返回false.
     * @author Elmer Zhang
     */
    function push($cert_id, $body, $device_token) {
        if(!is_array($body) || !isset($body['aps']['alert'])){
            $this->_errmsg = 'body must be an array';
            $this->_errno  = -9;
            return false;
        }
        $post = array();
        $params = array();
        $params['cert_id'] = intval($cert_id);
        $params['device_token'] = trim($device_token);

        $encodings = array( 'UTF-8', 'GBK', 'BIG5' );
        $charset = mb_detect_encoding( $body['aps']['alert'] , $encodings);
        if ( $charset !='UTF-8' ) {
            $body['aps']['alert'] = mb_convert_encoding( $body['aps']['alert'], "UTF-8", $charset);
        }
        $post['body'] = json_encode($body);

        $ret = $this->postData($post, $params);

        return $ret;
    }

    /**
     * 取得错误码
     *
     * @return int
     * @author Elmer Zhang
     */
    public function errno() {
        return $this->_errno;
    }

    /**
     * 取得错误信息
     *
     * @return string
     * @author Elmer Zhang
     */
    public function errmsg() {
        return $this->_errmsg;
    }

    private function postData($post, $params) {
        $url = self::baseurl . '?' . http_build_query( $params );
        $s = curl_init();
        if (is_array($post)) {
            $post = http_build_query($post);
        }
        curl_setopt($s,CURLOPT_URL,$url);
        curl_setopt($s,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
        curl_setopt($s,CURLOPT_TIMEOUT,5);
        curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($s,CURLINFO_HEADER_OUT, true);
        curl_setopt($s,CURLOPT_POST,true);
        curl_setopt($s,CURLOPT_POSTFIELDS,$post);
        $ret = curl_exec($s);
        $info = curl_getinfo($s);

        if(empty($info['http_code'])) {
            $this->_errno = -8;
            $this->_errmsg = "can not reach push service server";
        } else if($info['http_code'] != 200) {
            $this->_errno = -8;
            $this->_errmsg = $this->_errmsgs[-8];
        } else {
            if($info['size_download'] == 0) { // get MailError header
                $this->_errno = -8;
                $this->_errmsg = "apple push service internal error";
            } else {
                $array = json_decode(trim($ret), true);
                if ( is_array($array) && is_int( $array['code'] ) && $array['code'] < 0 ) {
                    $this->_errno = $array['code'];
                    $temp = $array['code'];
                    $this->_errmsg = $this->_errmsgs[ $temp ];

                    return false;
                } elseif( is_array($array) && is_int($array['code']) ) {
                    $this->_errno = SAE_Success;
                    $this->_errmsg = 'OK';

                    return $array;
                }
            }
        }
        return false;
    }

}