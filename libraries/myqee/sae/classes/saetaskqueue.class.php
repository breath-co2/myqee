<?php
/**
 * SAE TaskQueue服务
 *
 * @package sae
 * @version $Id$
 * @author Elmer Zhang
 */



/**
 * SAE TaskQueue<br />
 * 任务队列服务
 *
 * <code>
 * <?php
 * $queue = new SaeTaskQueue('test');
 *
 * //添加单个任务
 * $queue->addTask("http://freedemo.sinaapp.com/page1.php");
 * $queue->addTask("http://freedemo.sinaapp.com/page2.php", "key=value", true);
 *
 * //批量添加任务
 * $array = array();
 * $array[] = array('url'=>"http://freedemo.sinaapp.com/page3.php", "postdata"=>"act=test");
 * $array[] = array('url'=>"http://freedemo.sinaapp.com/page4.php", "postdata"=>"act=test", "prior"=>true);
 * $queue->addTask($array);
 *
 * //将任务推入队列
 * $ret = $queue->push();
 *
 * //任务添加失败时输出错误码和错误信息
 * if ($ret === false)
 *         var_dump($queue->errno(), $queue->errmsg());
 * ?>
 * </code>
 *
 * 错误码参考：
 *  - errno: 0         成功
 *  - errno: 1         认证失败
 *  - errno: 3         参数错误
 *  - errno: 10        队列不存在
 *  - errno: 11        队列已满或剩余长度不足
 *  - errno: 500     服务内部错误
 *  - errno: 999     未知错误
 *  - errno: 403     权限不足或超出配额
 *
 * @package sae
 * @author Elmer Zhang
 *
 */
class SaeTaskQueue extends SaeObject
{
    private $_accesskey = "";
    private $_secretkey = "";
    private $_errno=SAE_Success;
    private $_errmsg="OK";
    private $_post = array();

    /**
     * @ignore
     */
    const baseurl = "http://taskqueue.sae.sina.com.cn/index.php";

    /**
     * 构造对象
     *
     * @param string $queue_name 队列名称
     */
    function __construct($queue_name) {
        $this->_accesskey = SAE_ACCESSKEY;
        $this->_secretkey = SAE_SECRETKEY;

        $this->_queue_name = $queue_name;
        $this->_post['name'] = $queue_name;
        $this->_post['queue'] = array();
    }

    /**
     * 添加任务
     *
     * @param string|array $tasks 任务要访问的URL或以数组方式传递的多条任务。添加多条任务时的数组格式：
     * <code>
     * <?php
     * $tasks = array( array("url" => "http://xxxxxxxxxx",
     *                       "postdata" => "data", //要POST的数据。可选
     *                       "prior" => false,  //是否优先执行，默认为false，如果设为true，则将此任务插入到队列最前面。可选
     *                       "options" => array('key1' => 'value1', ....),  //附加参数，可选。
     * ), ................);
     * ?>
     * </code>
     * @param string $postdata 要POST的数据。可选，且仅当$tasks为URL时有效
     * @param bool prior 是否优先执行，默认为false，如果设为true，则将此任务插入到队列最前面。可选，且仅当$tasks为URL时有效
     * @param array options 附加参数，可选，且仅当$tasks为URL时有效。目前支持的参数：
     *  - delay, 延时执行，单位秒，最大延时600秒。
     * @return bool
     * @author Elmer Zhang
     */
    function addTask($tasks, $postdata = NULL, $prior = false, $options = array()) {
        if ( is_string($tasks) ) {
            if ( !filter_var($tasks, FILTER_VALIDATE_URL) ) {
                $this->_errno = SAE_ErrParameter;
                $this->_errmsg = "Unavailable tasks";
                return false;
            }

            //添加单条任务
            $item = array();
            $item['url'] = $tasks;
            if ($postdata != NULL) $item['postdata'] = base64_encode($postdata);
            if ($prior) $item['prior'] = true;
            $this->setOptions($item, $options);
            $this->_post['queue'][] = $item;

        } elseif ( is_array($tasks) ) {
            if ( empty($tasks) ) {
                $this->_errno = SAE_ErrParameter;
                $this->_errmsg = "Unavailable tasks";
                return false;
            }

            //添加多条任务
            foreach($tasks as $k => $v) {
                if (is_array($v) && isset($v['url'])) {
                    if (isset($v['postdata'])) {
                        $v['postdata'] = base64_encode($v['postdata']);
                    }
                    if (isset($v['options'])) {
                        $this->setOptions($v, $v['options']);
                        unset($v['options']);
                    }
                    $this->_post['queue'][] = $v;
                } elseif ( isset($tasks['url']) ) {
                    if (isset($tasks['postdata'])) {
                        $tasks['postdata'] = base64_encode($tasks['postdata']);
                    }
                    if (isset($tasks['options'])) {
                        $this->setOptions($tasks, $tasks['options']);
                        unset($tasks['options']);
                    }
                    $this->_post['queue'][] = $tasks;
                    break;
                } else {
                    $this->_errno = SAE_ErrParameter;
                    $this->_errmsg = "Unavailable tasks";
                    return false;
                }
            }
        }

        return true;
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

    /**
     * 设置key
     *
     * 只有使用其他应用的key时才需要调用
     *
     * @param string $accesskey
     * @param string $secretkey
     * @return void
     * @author Elmer Zhang
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
     * 将任务列表推入队列
     *
     * @return bool
     * @author Elmer Zhang
     */
    public function push() {
        $post = json_encode($this->_post);
        if (count($this->_post['queue']) > 0) {
            $this->_post['queue'] = array();
            return $this->postData(array("taskqueue"=>$post));
        } else {
            $this->_errno = SAE_ErrParameter;
            $this->_errmsg = "The queue is empty.";
            return false;
        }
    }

    /**
     * 查询队列剩余长度（可再添加的任务数）
     *
     * @return int
     * @author Elmer Zhang
     */
    function leftLength() {
        $this->_act = 'leftlen';
        //$this->_post['name'] = $this->_queue_name;

        return $this->send();
    }

    /**
     * 查询队列当前长度（剩余未执行的任务数）
     *
     * @return int
     * @author Elmer Zhang
     */
    function curLength() {
        $this->_act = 'curlen';
        //$this->_post['name'] = $this->_queue_name;

        return $this->send();
    }

    /**
     * @author Elmer Zhang
     */
    private function send() {
        $post = urlencode(json_encode($this->_post));
        if ($post) {
            return $this->postData(array("params"=>$post, "act"=>$this->_act));
        } else {
            return false;
        }
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
            $this->_errmsg = "taskqueue service segment fault";
        } else if($info['http_code'] != 200) {
            $this->_errno = SAE_ErrInternal;
            $this->_errmsg = "taskqueue service internal error";
        } else {
            if($info['size_download'] == 0) { // get MailError header
                $header = substr($ret, 0, $info['header_size']);
                $taskheader = $this->extractCustomHeader("TaskQueueError", $header);
                if($taskheader == false) { // not found MailError header
                    $this->_errno = SAE_ErrUnknown;
                    $this->_errmsg = "unknown error";
                } else {
                    $err = explode(",", $taskheader, 2);
                    $this->_errno = trim($err[0]);
                    $this->_errmsg = trim($err[1]);
                }
            } else {
                $body = substr($ret, -$info['size_download']);
                $body = json_decode(trim($body), true);
                $this->_errno = $body['errno'];
                $this->_errmsg = $body['errmsg'];
                if ($body['errno'] == 0) {
                    if (isset($body['data'])) {
                        return $body['data'];
                    } else {
                        return true;
                    }
                }
            }
        }
        return false;
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
        $pattern = '/'.$key.':(.*?)'."\n/";
        if (preg_match($pattern, $header, $result)) {
            return $result[1];
        } else {
            return false;
        }
    }

    private function setOptions(&$item, $options) {
        if (is_array($options) && !empty($options)) {
            foreach($options as $k => $v) {
                switch ($k) {
                    case 'delay':
                        $item['delay'] = intval($v);
                        break;
                    default:
                        break;
                }
            }
        }
    }
}