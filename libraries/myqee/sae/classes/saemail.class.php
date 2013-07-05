<?php
/**
 * SAE邮件服务
 *
 * @package sae
 * @version $Id$
 * @author lijun
 */



/**
 * SAE邮件class , 目前只支持SMTP
 *
 * 最大可发送1MB大小的邮件（含附件）
 *
 * <code>
 * <?php
 * $mail = new SaeMail();
 * $mail->setAttach( array( 'my_photo' => '照片的二进制数据' ) );
 * $ret = $mail->quickSend( 'to@sina.cn' , '邮件标题' , '邮件内容' , 'smtpaccount@gmail.com' , 'password' );
 *
 * //发送失败时输出错误码和错误信息
 * if ($ret === false)
 *         var_dump($mail->errno(), $mail->errmsg());
 *
 * $mail->clean(); // 重用此对象
 * $ret = $mail->quickSend( 'to@sina.cn' , '邮件标题' , '邮件内容' , 'smtpaccount@unknown.com' , 'password' , 'smtp.unknown.com' , 25 ); // 指定smtp和端口
 *
 * //发送失败时输出错误码和错误信息
 * if ($ret === false)
 *         var_dump($mail->errno(), $mail->errmsg());
 * ?>
 * </code>
 *
 * 错误码参考：
 *  - errno: 0         成功
 *  - errno: 3         参数错误
 *  - errno: 500     服务内部错误
 *  - errno: 999     未知错误
 *  - errno: 403     权限不足或超出配额
 *
 * @package sae
 * @author lijun
 *
 */
class SaeMail extends SaeObject
{
    private $_accesskey = "";
    private $_secretkey = "";
    private $_errno=SAE_Success;
    private $_errmsg="OK";
    private $_count = 0;
    private $_attachSize = 0;
    private $_post = array();
    private $_allowedAttachType = array("bmp","css","csv","gif","htm","html","jpeg","jpg","jpe","pdf","png","rss","text","txt","asc","diff","pot","tiff","tif","wbmp","ics","vcf");
    /**
     * @ignore
     */
    const DISPOSITION_ATTACH = 'A';
    /**
     * @ignore
     */
    const DISPOSITION_INLINE = 'I';
    private $_disposition = array(  "bmp"=>self::DISPOSITION_INLINE, "css"=>self::DISPOSITION_ATTACH,
        "csv"=>self::DISPOSITION_ATTACH, "gif"=>self::DISPOSITION_INLINE,
        "htm"=>self::DISPOSITION_INLINE,"html"=>self::DISPOSITION_INLINE,
        "jpeg"=>self::DISPOSITION_INLINE,"jpg"=>self::DISPOSITION_INLINE,
        "jpe"=>self::DISPOSITION_INLINE, "pdf"=>self::DISPOSITION_ATTACH,
        "png"=>self::DISPOSITION_INLINE, "rss"=>self::DISPOSITION_INLINE,
        "text"=>self::DISPOSITION_ATTACH,"txt"=>self::DISPOSITION_ATTACH,
        "asc"=>self::DISPOSITION_ATTACH,"diff"=>self::DISPOSITION_ATTACH,
        "pot"=>self::DISPOSITION_ATTACH,"tiff"=>self::DISPOSITION_ATTACH,
        "tif"=>self::DISPOSITION_ATTACH,"wbmp"=>self::DISPOSITION_INLINE,
        "ics"=>self::DISPOSITION_INLINE,"vcf"=>self::DISPOSITION_INLINE,);
    private $msp = array("sina.com"    => array("smtp.sina.com",25,0),
        "sina.cn"        => array("smtp.sina.cn",25,0),
        "163.com"        => array("smtp.163.com",25,0),
        "263.com"        => array("smtp.263.com",25,0),
        "gmail.com"    => array("smtp.gmail.com",587,1),
        "sohu.com"    => array("smtp.sohu.com",25,0),
        "qq.com"        => array("smtp.qq.com",25,0),
        "vip.qq.com"    => array("smtp.qq.com",25,0),
        "126.com"        => array("smtp.126.com",25,0),
    );

    /**
     * @ignore
     */
    const baseurl = "http://mail.sae.sina.com.cn/index.php";
    /**
     * @ignore
     */
    const mail_limitsize = 1048576;
    /**
     * @ignore
     */
    const subject_limitsize = 256;

    /**
     * 构造对象，此处options选项的设置和函数setOpt相同
     *
     * @param array $options 邮件发送参数，详细参数请参考SaeMail::setOpt($options)
     */
    function __construct($options = array()) {
        $this->_accesskey = SAE_ACCESSKEY;
        $this->_secretkey = SAE_SECRETKEY;

        $this->setOpt($options);
    }

    /**
     * 设置发送参数,此处设置的参数只有使用send()方法发送才有效;quickSend()时将忽略此设置.
     *
     *
     * @param array $options 支持的Key如下:
     * <pre>
     *    from              string (only one)
     *    -----------------------------------------
     *    to                string (多个用,分开)
     *    -----------------------------------------
     *    cc                string (多个用,分开)
     *    -----------------------------------------
     *    smtp_host         string
     *    -----------------------------------------
     *    smtp_port         port,default 25
     *    -----------------------------------------
     *    smtp_username     string
     *    -----------------------------------------
     *    smtp_password     string
     *    -----------------------------------------
     *    subject           string,最大长度256字节
     *    -----------------------------------------
     *    content           text
     *    -----------------------------------------
     *    content_type      "TEXT"|"HTML",default TEXT
     *    -----------------------------------------
     *    charset           default utf8
     *    -----------------------------------------
     *    tls               default false
     *    -----------------------------------------
     *    compress          string 设置此参数后，SaeMail会将所有附件压缩成一个zip文件，此参数用来指定压缩后的文件名。
     *    -----------------------------------------
     *    callback_url      string SMTP发送失败时的回调地址，回调方式为post，postdata格式：timestamp=时间戳&from=from地址&to=to地址（如有多个to，则以,分割）
     *    -----------------------------------------
     * </pre>
     *
     * @return bool
     * @author Lijun
     */
    public function setOpt( $options ) {
        if (isset($options['subject']) && strlen($options['subject']) > self::subject_limitsize) {
            $this->_errno = SAE_ErrParameter;
            $this->_errmsg = "subject cannot larger than ".self::subject_limitsize." bytes";
            return false;
        }
        if(isset($options['content']))
            $this->_count = $this->_attachSize + strlen($options['content']);
        if($this->_count > self::mail_limitsize) {
            $this->_errno = SAE_ErrParameter;
            $this->_errmsg = "mail size cannot larger than ".self::subject_limitsize." bytes";
            return false;
        }

        $this->_post = array_merge($this->_post, $options);

        return true;
    }

    /**
     * 用于重用实例化对象时，将上一次的相关数据清零
     *
     * @return bool
     * @author Lijun
     */
    public function clean() {
        $this->_post = array();
        $this->_count = 0;
        $this->_attachSize = 0;
        return true;
    }

    /**
     * 快速发送邮件
     *
     * 由于采用邮件队列发送,本函数返回成功时,只意味着邮件成功送到发送队列,并不等效于邮件已经成功发送.
     * 邮件编码默认为UTF-8,如需发送其他编码的邮件,请使用setOpt()方法设置charset,否则收到的邮件标题和内容都将是空的.
     *
     * @param string $to 要发送到的邮件地址,多个邮件地址之间用英文逗号","隔开
     * @param string $subject 邮件标题
     * @param string $msgbody 邮件内容
     * @param string $smtp_user smtp用户名，必须为邮箱地址。注：和setOpt()中的smtp_user不同。
     * @param string $smtp_pass smtp用户密码
     * @param string $smtp_host smtp服务host,使用sina,gmail,163,265,netease,qq,sohu,yahoo的smtp时可不填
     * @param string $smtp_port smtp服务端口,使用sina,gmail,163,265,netease,qq,sohu,yahoo的smtp时可不填
     * @param string $smtp_tls smtp服务是否开启tls(如gmail),使用sina,gmail,163,265,netease,qq,sohu,yahoo的smtp时可不填
     * @return bool
     * @author Lijun
     */
    function quickSend($to, $subject, $msgbody, $smtp_user, $smtp_pass, $smtp_host='', $smtp_port=25, $smtp_tls=false)
    {
        $to = trim($to);
        $subject = trim($subject);
        $msgbody = trim($msgbody);
        $smtp_user = trim($smtp_user);
        $smtp_host = trim($smtp_host);
        $smtp_port = intval($smtp_port);

        $this->_count = strlen($msgbody) + $this->_attachSize;
        if(strlen($subject) > self::subject_limitsize) {
            $_errno = SAE_ErrParameter;
            $this->_errmsg = "subject cannot larger than ".self::subject_limitsize." bytes";
            return false;
        }
        if($this->_count > self::mail_limitsize) {
            $this->_errno = SAE_ErrParameter;
            $this->_errmsg = "mail size cannot larger than ".self::subject_limitsize." bytes";
            return false;
        }

        //if(preg_match('/([a-zA-Z0-9_-]+)@([a-zA-Z0-9-]+\.[a-zA-Z0-9-]+\.?[a-zA-Z0-9-]*)/', $smtp_user, $match)) {
        if (filter_var($smtp_user, FILTER_VALIDATE_EMAIL)) {
            preg_match('/([^@]+)@(.*)/', $smtp_user, $match);
            $user = $match[1]; $host = $match[2];
            if(empty($smtp_host)) {
                //print_r($match);
                if(isset($this->msp[$host])) { $smtp_host = $this->msp[$host][0]; }
                else {
                    $this->_errno = SAE_ErrParameter;
                    $this->_errmsg = "you can set smtp_host explicitly or choose msp from sina,gmail,163,265,netease,qq,sohu,yahoo";
                    return false;
                }
            }
            if($smtp_port == 25 and isset($this->msp[$host])) {
                $smtp_port = $this->msp[$host][1];
            }
            if(!$smtp_tls and isset($this->msp[$host])) {
                $smtp_tls = $this->msp[$host][2];
            }
            $smtp_tls = ($smtp_tls == true);
            $username = $user;
        } else {
            $this->_errno = SAE_ErrParameter;
            $this->_errmsg = "invalid email address";
            return false;
        }
        $this->_post = array_merge($this->_post, array("from"=>$smtp_user, "smtp_username"=>$username, "smtp_password"=>$smtp_pass, "smtp_host"=>$smtp_host, "smtp_port"=>$smtp_port, 'to'=>$to,'subject'=>$subject,'content'=>$msgbody, 'tls'=>$smtp_tls));

        return $this->send();
    }

    /**
     * 取得错误码
     *
     * @return int
     * @author Lijun
     */
    public function errno() {
        return $this->_errno;
    }

    /**
     * 取得错误信息
     *
     * @return string
     * @author Lijun
     */
    public function errmsg() {
        return $this->_errmsg;
    }

    /**
     * 设置key
     *
     * 只有使用其他应用的key时才需要调用
     *
     * @param string $accesskey
     * @param string $secretkey
     * @return bool
     * @author Lijun
     * @ignore
     */
    public function setAuth( $accesskey, $secretkey) {
        $accesskey = trim($accesskey);
        $secretkey = trim($secretkey);
        $this->_accesskey = $accesskey;
        $this->_secretkey = $secretkey;
        return true;
    }

    /**
     * 添加附件
     *
     * 附件和邮件正文的总大小不可超过1MB。
     *
     * @param array $attach , key为文件名称,附件类型由文件名后缀决定,value为文件内容;文件内容支持二进制<br>
     * 支持的文件后缀:bmp,css,csv,gif,htm,html,jpeg,jpg,jpe,pdf,png,rss,text,txt,asc,diff,pot,tiff,tif,wbmp,ics,vcf
     * @return bool
     * @author Lijun
     */
    public function setAttach($attach) {
        if(!is_array($attach)) {
            $this->_errmsg = "attach parameter must be an array!";
            $this->_errno = SAE_ErrParameter;
            return false;
        }
        $this->_attachSize = 0;
        foreach($attach as $fn=>$blob) {
            $suffix = end(explode(".", $fn));
            if(!in_array($suffix, $this->_allowedAttachType)) {
                $this->_errno = SAE_ErrParameter;
                $this->_errmsg = "Invalid attachment type";
                return false;
            }
            $this->_attachSize += strlen($blob);
            $this->_count = $this->_attachSize + strlen($this->_post['content']);
            if($this->_count > self::mail_limitsize) {
                $this->_errno = SAE_ErrForbidden;
                $this->_errmsg = "mail size cannot larger than ".self::mail_limitsize." bytes";
                return false;
            }
            $this->_post = array_merge($this->_post, array("attach:$fn:B:".$this->_disposition[$suffix] => base64_encode($blob)));
            //print_r(strlen(base64_encode($blob)));
        }
        return true;
    }

    /**
     * 发送邮件
     *
     * @return bool
     * @author Lijun
     */
    public function send() {
        $missed_keys = array();
        foreach ( array('from', 'to', 'smtp_host', 'smtp_username', 'smtp_password', 'subject') as $key ) {
            if ( empty($this->_post[$key]) ) {
                $missed_keys[] = $key;
            }
        }
        if ( !empty($missed_keys) ) {
                $this->_errno = SAE_ErrParameter;
                $this->_errmsg = "parameters " . join(', ', $missed_keys) . " can no be empty";
                return false;
        }

        if($this->_count > self::mail_limitsize) {
            $this->_errno = SAE_ErrForbidden;
            $this->_errmsg = "mail size cannot larger than ".self::mail_limitsize." bytes";
            return false;
        }
        //print_r($this->_post);
        if ( isset($this->_post['charset']) ) {
            $charset = strtolower($this->_post['charset']);
            $content_conved = @iconv($charset, 'utf-8', $this->_post['content']);
            if ($content_conved)
                $this->_post['content'] = $content_conved;
            $subject_conved = @iconv($charset, 'utf-8', $this->_post['subject']);
            if ($subject_conved)
                $this->_post['subject'] = $subject_conved;
            $this->_post['charset'] = 'utf-8';
        }
        $tobepost = json_encode($this->_post);
        return $this->postData(array("saemail"=>$tobepost));
    }


    private function postData($post) {
        $url = self::baseurl;
        $s = curl_init();
        curl_setopt($s,CURLOPT_URL,$url);
        curl_setopt($s,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
        curl_setopt($s,CURLOPT_TIMEOUT,5);
        curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($s,CURLOPT_HEADER, 1);
        curl_setopt($s,CURLINFO_HEADER_OUT, true);
        curl_setopt($s,CURLOPT_HTTPHEADER, $this->genReqestHeader($post));
        curl_setopt($s,CURLOPT_POST,true);
        curl_setopt($s,CURLOPT_POSTFIELDS,$post);
        $ret = curl_exec($s);
        // exception handle, if error happens, set errno/errmsg, and return false
        $info = curl_getinfo($s);
        //print_r($info);
        //echo 'abab';
        //print_r($ret);
        //echo 'abab';
        if(empty($info['http_code'])) {
            $this->_errno = SAE_ErrInternal;
            $this->_errmsg = "mail service segment fault";
            return false;
        } else if($info['http_code'] != 200) {
            $this->_errno = SAE_ErrInternal;
            $this->_errmsg = "mail service internal error";
            return false;
        } else {
            if($info['size_download'] == 0) { // get MailError header
                $header = substr($ret, 0, $info['header_size']);
                $mailheader = $this->extractCustomHeader("MailError", $header);
                if($mailheader == false) { // not found MailError header
                    $this->_errno = SAE_ErrUnknown;
                    $this->_errmsg = "unknown error";
                    return false;
                }
                $err = explode(",", $mailheader, 2);
                $this->_errno = $err[0];
                $this->_errmsg = $err[1];
                return false;
            } else {
                $body = substr($ret, -$info['size_download']);
                $body = json_decode(trim($body), true);
                $this->_errno = $body['errno'];
                $this->_errmsg = $body['errmsg'];
                if ($body['errno'] != 0) {
                    return false;
                }
            }
        }
        return true;
    }

    private function genSignature($content, $secretkey) {
        $sig = base64_encode(hash_hmac('sha256',$content,$secretkey,true));
        return $sig;
    }

    private function genReqestHeader($post) {
        $timestamp = date('Y-m-d H:i:s');
        $cont1 = "ACCESSKEY".$this->_accesskey."TIMESTAMP".$timestamp;
        $reqhead = array("TimeStamp: $timestamp","AccessKey: ".$this->_accesskey, "Signature: " . $this->genSignature($cont1, $this->_secretkey));
        //print_r($reqhead);
        return $reqhead;
    }

    private function extractCustomHeader($key, $header) {
        $pattern = '/'.$key.'(.*?)'."\n/";
        if (preg_match($pattern, $header, $result)) {
            return $result[1];
        } else {
            return false;
        }
    }

}