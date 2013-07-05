<?php
/**
 * SAE DeferredJob
 *
 * @package sae
 * @version
 * @author Seeker Zhang
 */



/**
 * SAE DeferredJob<br />
 * 大文件导入导出服务
 *
 * <code>
 * <?php
 * $dj = new SaeDeferredJob();
 *
 * //添加任务
 * $taskID=$dj->addTask("import","mysql","domainA","abc.sql","databaseA","tableA","callback.php");
 * if($taskID===false)
 *     var_dump($dj->errno(), $dj->errmsg());
 * else
 *    var_dump($taskID);
 *
 * $taskID=$dj->addTask("export","mysql","domainB","abc.csv.zip","databaseB","tableB","csv.php");
 * if($taskID===false)
 *     var_dump($dj->errno(), $dj->errmsg());
 *
 * //获得任务状态
 * $ret=$dj->getStatus($taskID);
 * if($ret===false)
 *     var_dump($dj->errno(), $dj->errmsg());
 *
 * //删除任务
 * $ret=$dj->deleteTask($taskID);
 * if($ret===false)
 *     var_dump($dj->errno(), $dj->errmsg());
 *
 * ?>
 * </code>
 *
 * 错误码参考
 *  - errno: 0      成功
 *  - errno: -1     参数错误
 *  - errno: -2     数据库错误
 *  - errno: -3     没有找到taskid
 *  - errno: -4     已经删除过的taskid
 *  - errno: -5     任务已经开始执行
 *  - errno: -6     超过每天可执行任务数量上限
 *  - errno: -7     系统错误
 *  - errno: -8     未知错误
 *
 * @package sae
 * @author Seeker Zhang
 *
 */
class SaeDeferredJob extends SaeObject
{
    private $_accesskey = "";
    private $_secretkey = "";
    private $_errno=SAE_Success;
    private $_errmsg="OK";
    private $_post = array();

    /**
     * @ignore
     */
    const baseurl = "http://deferredjob.sae.sina.com.cn/rest.php";
    //const baseurl = "http://127.0.0.1/deferredjob/rest.php";

    /**
     * 构造对象
     *
     */
    function __construct() {
        $this->_accesskey = SAE_ACCESSKEY;
        $this->_secretkey = SAE_SECRETKEY;
    }

    /**
     * 取得错误码
     *
     * @return int
     * @author Seeker Zhang
     */
    public function errno()
    {
        return $this->_errno;
    }

    /**
     * 取得错误信息
     *
     * @return string
     * @author Seeker Zhang
     */
    public function errmsg()
    {
        return $this->_errmsg;
    }

    /**
     * 添加任务
     *
     * @param string $tasktype 任务的类型：“import”|“export”。导入任务：“import”；导出任务：“export”。
     * @param string $dbtype 数据库的类型：“mysql”|“kvdb”。目前只支持“mysql”。
     * @param stirng $stor_domain 存放导入/导出文件的storage的domain名称。
     * @param stirng $stor_filename 导入/导出文件名称，格式：prefix[.format][.compression]，例：abc.csv.zip，服务根据format来判断数据类型，数据类型包括sql、csv。
     * @param stirng $dbname 导入/导出数据库的名称。
     * @param stirng $tbname 导入/导出数据库类型为mysql时，使用的表名。
     * @param stirng $callbackurl 任务成功时，调用的回调url，只支持应用默认版本中的url，为空时，不执行回调url。
     * @return mix 成功返回任务id，失败返回false。<br />
     * 注意：每天最多可执行10个任务。更多说明请查看文档中心。
     * @author Seeker Zhang
     */
    public function addTask($tasktype,$dbtype,$stor_domain,$stor_filename,$dbname,$tbname,$callbackurl){
            $this->_errno=SAE_Success;
            $this->_errmsg="OK";

        $tt=array('import','export');
        $dt=array('mysql');

        $tasktype=trim($tasktype);
        if(!in_array($tasktype,$tt)){
            $this->setError("tasktype");
            return false;
        }
        $dbtype=trim($dbtype);
        if(!in_array($dbtype,$dt)){
            $this->setError("dbtype");
            return false;
        }

        $sf=trim($stor_filename);
        if(empty($sf)){
            $this->setError("stor filename");
            return false;
        }
        $dm=trim($stor_domain);
        if(empty($dm)){
            $this->setError("stor domain");
            return false;
        }
        $md=trim($dbname);
        if(empty($md)){
            $this->setError("mysql database");
            return false;
        }

        $task=array('function'=>'add','tasktype'=>$tasktype,'dbtype'=>$dbtype,'stor_domain'=>$dm,'stor_filename'=>$sf,'dbname'=>$md,'tbname'=>$tbname,'callback'=>$callbackurl,'from'=>'api');
        $ary=$this->postData($task);
        //return $ary;
        if($ary[0]==0){
            $taskid=$ary[2];
            return $taskid;
        }
        else{
            $this->_errno=$ary[0];
            $this->_errmsg=$ary[1];
            return false;
        }
    }

    /**
     * 获得任务状态
     *
     * @param int $taskID 任务的id。
     * @return mix 成功返回任务状态：未进入队列：waiting；等待执行：inqueue；执行中：excuting；完成：done；失败：abort。<br />
     *            失败返回false。
     * @author Seeker Zhang
     */
    public function getStatus($taskID){
            $this->_errno=SAE_Success;
            $this->_errmsg="OK";

        if(!is_numeric($taskID)){
            $this->setError("taskid");
            return false;
        }

        $task=array('function'=>'getstatus','id'=>$taskID);
        $ary=$this->postData($task);
        if($ary[0]==0){
            $status=$ary[2];
            return $status;
        }
        else{
            $this->_errno=$ary[0];
            $this->_errmsg=$ary[1];
            return false;
        }
    }

    /**
     * 删除任务
     *
     * @param int $taskID 任务的id。
     * @return bool 成功返回true，失败返回false，进入excuting状态的任务不能被删除。
     * @author Seeker Zhang
     */
    public function deleteTask($taskID){
            $this->_errno=SAE_Success;
            $this->_errmsg="OK";

        if(!is_numeric($taskID)){
            $this->setError("taskid");
            return false;
        }

        $task=array('function'=>'delete','id'=>$taskID);
        $ary=$this->postData($task);
        if($ary[0]==0){
            $status=$ary[2];
            return $status;
        }
        else{
            $this->_errno=$ary[0];
            $this->_errmsg=$ary[1];
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
        curl_setopt($s,CURLOPT_HEADER, 0);
        curl_setopt($s,CURLINFO_HEADER_OUT, true);
        curl_setopt($s,CURLOPT_HTTPHEADER, $this->genReqestHeader($post));
        curl_setopt($s,CURLOPT_POST,true);
        curl_setopt($s,CURLOPT_POSTFIELDS,$post);
        #curl_setopt($s, CURLOPT_VERBOSE, true);
        $ret = curl_exec($s);
        //var_dump($ret);
        $info = curl_getinfo($s);
        if(empty($info['http_code'])) {
            $this->_errno = SAE_ErrInternal;
            $this->_errmsg = "deferredjob service segment fault";
        } else if($info['http_code'] != 200) {
            $hc=$info['http_code'];
            $this->_errno = SAE_ErrInternal;
            $this->_errmsg = "deferredjob service internal error";
        } else {
            $ret=substr($ret,0,-2);
            //echo "get data\n\t$ret";
            return explode("\t",$ret);
        }
        return false;
    }

    private function setError($msg=""){
        $this->_errno=-1;
        $this->_errmsg="parameter $msg error";
    }

    private function genSignature($content, $secretkey) {
        $sig = base64_encode(hash_hmac('sha256',$content,$secretkey,true));
        return $sig;
    }

    private function genReqestHeader($post) {
        $timestamp = date('Y-m-d H:i:s');
        $cont1 = "ACCESSKEY".$this->_accesskey."TIMESTAMP".$timestamp;
        $reqhead = array("JOBTimeStamp: $timestamp","JOBAccessKey: ".$this->_accesskey, "JOBSignature: " . $this->genSignature($cont1, $this->_secretkey));
        return $reqhead;
    }
}