<?php
/**
 * SAE 全文检索服务
 *
 * @package sae
 * @version $Id$
 * @author ytf606
 *
 */



/**
 * SAE 全文检索服务<br />
 *
 * <code>
 * $fts = new SaeFTS();
 *
 * // 添加id为1，内容为'content test1'的文档
 * $ret = $fts->addDoc(1, 'content test1');
 * if ( $ret === false) var_dump( $fts->errno(), $fts->errmsg());
 *
 * // 添加id为2，内容为'content test1'的文档,文档分组是1
 * $ret = $fts->addDoc(2, 'content test1', 1);
 * if ( $ret === false ) var_dump( $fts->errno(), $fts->errmsg());
 *
 * // 添加id为3，内容为'content test1'的文档，文档分组是1，文档类型1
 * $options = array('file_mark'=> 1, 'file_long1' => 2, 'file_string1'=> 'a');
 * $ret = $fts->addDoc(3, "content test1", 1, $options);
 * if ( $ret === false ) var_dump( $fts->errno(), $fts->errmsg());
 *
 * // 修改id为1的文档，内容修改为'new content test1'
 * $ret = $fts->modifyDoc(1, "new content test1");
 * if ( $ret === false) var_dump( $fts->errno(), $fts->errmsg());
 *
 * // 修改id为2的文档，内容为'content test2' 文档分组修改为3
 * $ret = $fts->modifyDoc(2, 'content test2', 3);
 * if ( $ret === false ) var_dump( $fts->errno(), $fts->errmsg());
 *
 * // 修改id为3的文档，内容为'content test1' 文档分组为1 文档类型修改为2
 * $options = array('file_mark' => 2);
 * $ret = $fts->modifyDoc(3, 'content test1', 1, $options);
 * if ( $ret === false ) var_dump( $fts->errno(), $fts->errmsg());
 *
 * // 删除id为1的文档
 * $ret = $fts->deleteDoc(1);
 * if ( $ret === false ) var_dump( $fts->errno(), $fts->errmsg());
 *
 * // 在组1中搜索包含'test'的文档
 * $ret = $fts->search('test');
 * if ( $ret === false ) {
 *     var_dump( $fts->errno(), $fts->errmsg());
 * } else {
 *     print_r( $ret );
 * }
 *
 * // 在组1中搜索包含'test'的file_mark为2的文档 按file_long1的升序排列 每页20条
 * $options = array(
 *         'groupid' => 1,
 *         'begintimestamp' => '1335179468',
 *         'endtimestamp' => '1335189468',
 *         'geox' => 0.00,
 *         'geoy' => 0.00,
 *         'range' => 0.00,
 *         'file_mark' => 2
 * );
 * $ret = $fts->search('test', 'file_long1', "asced", 20, 0, $options);
 * if ( $ret === false ) {
 *     var_dump( $fts->errno(), $fts->errmsg());
 * } else {
 *     print_r( $ret );
 * }
 *
 * </code>
 *
 * 错误码参考：
 *  - errno: 0      成功
 *  - errno: -1     参数错误
 *  - errno: -4     系统内部错误
 *  - errno: 607    服务未初始化
 *
 * @package sae
 * @author ytf606
 *
 */
class SaeFTS extends SaeObject
{
    private $_errno = SAE_Success;
    private $_errmsg = "OK";
    private $_errmsgs = array(
            -1 => "invalid parameters",
            -4 => "internal error",
            607 => "service is not enabled",
    );

    /**
     * @ignore
     */
    const searchurl = "http://fts.sae.sina.com.cn/SaeSearch_new/Search.php";

    /**
     * @ignore
     */
    const manageurl="http://fts.sae.sina.com.cn/SaeSearch_new/IndexManage.php";
    /**
     * 构造对象
     *
     */
    function __construct() {
        $this->_accessKey = SAE_ACCESSKEY;
    }

    /**
     * 添加文档
     *
     * 文档id号相同的文档不可重复添加，如需修改已存在文档，请使用modifyDoc
     *
     * @param int $docid 文档的id号为整数。
     * @param string $content 索要索引的文档内容。
     * @param int $groupid 文档分组，默认分组时0
     * @param array $options 可支持一下零到多个参数key：<pre>
     *      file_mark            int,支持范围0-31，每个数字代表一个不同的文档类型
     *      ----------------------------------------------------------
     *      file_long1            long,可按此参数进行排序
     *      ----------------------------------------------------------
     *      file_long2            long,可按此参数进行排序
     *      ----------------------------------------------------------
     *      file_long3            long,可按此参数进行排序
     *      ----------------------------------------------------------
     *      file_sting1            string,可按此参数进行排序
     *      ----------------------------------------------------------
     *      file_string2        string,可按此参数进行排序
     *      ----------------------------------------------------------
     *      file_string3        string,可按此参数进行排序
     *      ----------------------------------------------------------
     *     </pre>
     *
     * @return bool 成功返回true，失败返回false
     * @author ytf606
     */
    function addDoc( $docid, $content, $groupid = 0, $options = array()) {
        $post = array();
        $params = array();
        if ( trim($content) == '' ) {
            $this->_errno = -1;
            $this->_errmsg = 'content can not be empty';
            return false;
        }

        $options['docid'] = $docid;
        $params=$this->fileOp($options, $params);
        if($params === false){
            return false;
        }
        if(isset($options['geox']) && !empty($options['geox'])){
            if(!is_double($options['geox'])){
                $this->_errno = -1;
                $this->_errmsg = "geox must be double type";
                return false;
            }
            if(isset($options['geoy']) && empty($options['geoy'])){
                $this->_errno = -1;
                $this->_errmsg = "geox can not be empty";
                return false;
            }
            if(!is_double($options['geoy'])){
                $this->_errno = -1;
                $this->_errmsg = "geoy must be double type";
                return false;
            }
            $post['geox'] = $options['geox'];
            $post['geoy'] = $options['geoy'];
        }else{
            if(isset($options['geoy']) && !empty($options['geoy'])){
                $this->_errno = -1;
                $this->_errmsg = "geox can not be empty";
                return false;
            }
        }

        if(!is_int($groupid)){
            $this->_errno = -1;
            $this->_errmsg = "groupid must be a int type";
            return false;
        }
        if($groupid<0 || $groupid > 255){
            $this->_errno = -1;
            $this->_errmsg = "groupid must be in 0 to 255";
            return false;
        }
        $params['cmd'] = 'adddoc';
        $params['userid'] = $this->_accessKey;
        $params['docid'] = intval($docid);
        $params['groupid']=$groupid;
        $post['content']=$content;
        $ret = $this->postData(self::manageurl, $post, $params);
        return $ret;
    }

    /**
     * 修改文档
     *
     * @param int $docid 文档的id号。
     * @param string $content 索要索引的文档内容。
     * @param int $groupid 文档添加的分组情况，默认是0
     * @param array $options 可支持一下零到多个参数key：<pre>
     *      file_mark            int,支持范围0-31，每个数字代表一个不同的文档类型
     *      ----------------------------------------------------------
     *      file_long1            long,可按此参数进行排序
     *      ----------------------------------------------------------
     *      file_long2            long,可按此参数进行排序
     *      ----------------------------------------------------------
     *      file_long3            long,可按此参数进行排序
     *      ----------------------------------------------------------
     *      file_sting1            string,可按此参数进行排序
     *      ----------------------------------------------------------
     *      file_string2        string,可按此参数进行排序
     *      ----------------------------------------------------------
     *      file_string3        string,可按此参数进行排序
     *      ----------------------------------------------------------
     *     </pre>
     *
     * @return bool 成功返回true，失败返回false.
     * @author ytf606
     */
    function modifyDoc( $docid, $content, $groupid=0, $options=array()) {
        $post = array();
        $params = array();
        if ( trim($content) == '' ) {
            $this->_errno = -1;
            $this->_errmsg = 'content can not be empty';
            return false;
        }

        $options['docid'] = $docid;
        $params = $this->fileOp($options, $params);
        if($params === false){
            return false;
        }

        if(isset($options['geox']) && !empty($options['geox'])){
            if(!is_double($options['geox'])){
                $this->_errno = -1;
                $this->_errmsg = "geox must be double type";
                return false;
            }
            if(isset($options['geoy']) && empty($options['geoy'])){
                $this->_errno = -1;
                $this->_errmsg = "geox can not be empty";
                return false;
            }
            if(!is_double($options['geoy'])){
                $this->_errno = -1;
                $this->_errmsg = "geoy must be double type";
                return false;
            }
            $post['geox']=$options['geox'];
            $post['geoy']=$options['geoy'];
        }else{
            if(isset($options['geoy']) && !empty($options['geoy'])){
                $this->_errno = -1;
                $this->_errmsg = "geox can not be empty";
                return false;
            }
        }

        $params['cmd'] = 'modifydoc';
        $params['userid'] = $this->_accessKey;
        $params['groupid']=$groupid;
        $params['docid'] = intval($docid);
        $post['content'] = $content;

        $ret = $this->postData(self::manageurl, $post, $params);
        return $ret;
    }

    /**
     * 检测参数
     *
     * @param array $arr 可支持一下零到多个参数key：
     * @param $params 如果参数存在且正确，存入此数组中
     * @return array|bool 成功返回$params数组，失败返回false
     * @author ytf606
     */
    private function fileOp($arr,$params){
        if( !is_int($arr['docid']) && !ctype_digit($arr['docid']) ) {
            $this->_errno = -1;
            $this->_errmsg = 'docid must be an integer';
            return false;
        }

        if(isset($arr['file_mark']) && $arr['file_mark']!=''){
            if(!is_int($arr['file_mark']) && !ctype_digit($arr['file_mark'])){
                $this->_errno = -1;
                $this->_errmsg = 'file_mark must be an integer';
                return false;
            }

            if($arr['file_mark']<0 || $arr['file_mark']>32){
                $this->_errno=-1;
                $this->_errmsg="file_mark must be in 0 to 31";
                return false;
            }
            $params['file_mark']=$arr['file_mark'];
        }

        if(isset($arr['file_long1']) && !empty($arr['file_long1'])){
            if(!is_int($arr['file_long1']) && !ctype_digit($arr['file_long1'])){
                $this->_errno = -1;
                $this->_errmsg = 'file_long1 must be an integer';
                return false;
            }
            $params['file_long1']=$arr['file_long1'];
        }

        if(isset($arr['file_long2']) && !empty($arr['file_long2'])){
            if(!is_int($arr['file_long2']) && !ctype_digit($arr['file_long2'])){
                $this->_errno = -1;
                $this->_errmsg = 'file_long2 must be an integer';
                return false;
            }
            $params['file_long2']=$arr['file_long2'];
        }

        if(isset($arr['file_long3']) && !empty($arr['file_long3'])){
            if(!is_int($arr['file_long3']) && !ctype_digit($arr['file_long3'])){
                $this->_errno = -1;
                $this->_errmsg = 'file_long3 must be an integer';
                return false;
            }
            $params['file_long3']=$arr['file_long3'];
        }

        if(isset($arr['file_string1']) && !empty($arr['file_string1'])){
            if(!is_string($arr['file_string1'])){
                $this->_errno = -1;
                $this->_errmsg = 'file_string1 must be an string';
                return false;
            }
            $params['file_string1']=$arr['file_string1'];
        }

        if(isset($arr['file_string2']) && !empty($arr['file_string2'])){
            if(!is_string($arr['file_string2'])){
                $this->_errno = -1;
                $this->_errmsg = 'file_string2 must be an string';
                return false;
            }
            $params['file_string2']=$arr['file_string2'];
        }

        if(isset($arr['file_string3']) && !empty($arr['file_string3'])){
            if(!is_string($arr['file_string3'])){
                $this->_errno = -1;
                $this->_errmsg = 'file_string3 must be an string';
                return false;
            }
            $params['file_string3']=$arr['file_string3'];
        }
        return $params;
    }

    /**
     * 删除文档
     *
     * @param int $docid 文档的id号。
     * @return bool 成功返回true，失败返回false.
     * @author ytf606
     */
    function deleteDoc( $docid ) {

        if ( !is_int($docid) && !ctype_digit($docid) ) {
            $this->_errno = -1;
            $this->_errmsg = 'docid must be an integer';
            return false;
        }

        $post = array();
        $params = array();
        $params['cmd'] = 'deletedoc';
        $params['userid'] = $this->_accessKey;
        $params['docid'] = intval($docid);

        $ret = $this->postData(self::manageurl, $post, $params);

        return $ret;
    }

    /**
     * 搜索文档
     *
     * 检索规则：
     *  - “+”:表示关键词的AND的关系。
     *  - “-“:表示关键词不再检索结果中。
     * 例如：
     *  - 当我们要搜索同时出现串a、串b但不包含串c的所有文档，检索串为："a+b+-c"
     *
     * @param string $q 检索串。
     * @param string $sortfiled 根据字段排序 值可选范围如下 其他为error
     *      - file_long1 根据file_long1字段进行排序
     *      - file_long2 根据file_long2字段进行排序
     *      - file_long3 根据file_long3字段进行排序
     *      - file_string1 根据file_string1字段进行排序
     *      - file_string2 根据file_string2字段进行排序
     *   - file_string3 根据file_string3字段进行排序
     * @param string $sortmode 排序方式，asced升序，descend降序 其他为error
     * @param int $pagesize 每页显示的结果集,默认是20
     * @param int $pagenum 从第几页开始,默认是0
     * @param array $options 可支持一下零到多个可选参数key：<pre>
     *      groupid                int,支持范围1-255，代表不同分组
     *      ----------------------------------------------------------
     *      begintimestamp        int,文档查询开始时间戳
     *      ----------------------------------------------------------
     *      endtimestamp        int,文档查询结束时间戳，必须存在开始时间戳
     *      ----------------------------------------------------------
     *      geox                double,地理位置横坐标，必须同时存在geoy和range
     *      ----------------------------------------------------------
     *      geoy                double,地理位置纵坐标，必须同时存在geox和range
     *      ----------------------------------------------------------
     *      range                double,检索文档的范围，必须同时存在geox和geoy
     *      ----------------------------------------------------------
     *      file_mark            int,文档的检索类型，支持范围0-31，0表示检索全部
     *      ----------------------------------------------------------
     *     </pre>
     *
     * @return array|bool 成功返回检索结果，失败返回false
     * @author ytf606
     */
    function search( $q, $sortfiled='file_long1', $sortmode="asced", $pagesize=20, $pagenum=0, $options=array()) {
        $post = array();
        $params = array();
        $params['userid'] = $this->_accessKey;
        $params['q'] = $q;
        if($sortfiled!='file_long1' && $sortfiled!='file_long2' && $sortfiled!='file_long3'
                && $sortfiled!='file_string1' && $sortfiled!='file_string2' && $sortfiled!='file_string3'){
            $this->_errno = -1;
            $this->_errmsg = "sortfiled param is wrong";
            return false;
        }

        if($sortmode!="descend" && $sortmode!="asced"){
            $this->_errno = -1;
            $this->_errmsg = "the sortmode param wrong";
            return false;
        }

        if(!is_int($pagesize)){
            $this->_errno = -1;
            $this->_errmsg = "pagesize param is wrong";
            return false;
        }

        if(!is_int($pagenum)){
            $this->_errno = -1;
            $this->_errmsg = "pagenum param is wrong";
            return false;
        }

        if(isset($options['groupid'])){
            if(!is_int($options['groupid'])){
                $this->_errno = -1;
                $this->_errmsg = "groupid param is wrong";
                return false;
            }
            if($options['groupid'] < 0 || $options['groupid'] > 255){
                $this->_errno = -1;
                $this->_errmsg = "groupid param must be in 0 to 255";
                return false;
            }
            $params['groupid']=$options['groupid'];
        }

        if(isset($options['begintimestamp']) && !empty($options['begintimestamp'])){
            if(isset($options['endtimestamp']) && !empty($options['endtimestamp'])){
                $params['timestamp'] = $options['begintimestamp'].":".$options['endtimestamp'];
            }else{
                $params['timestamp'] = $options['begintimestamp'];
            }
        }else{
            if(isset($options['endtimestamp'])){
                $this->_errno = -1;
                $this->_errmsg = "begintimestamp can not empty";
                return false;
            }
        }

        if(isset($options['geox']) && !empty($options['geox'])){
            if(!is_double($options['geox']) || !is_double($options['geoy'])){
                $this->_errno = -1;
                $this->_errmsg = "geox or geoy must be a double type";
                return false;
            }
            if(!is_double($options['range']) && !is_int($options['range'])){
                $this->_errno = -1;
                $this->_errmsg = "range must be a int or double type";
                return false;
            }
            if(!empty($options['geoy']) && !empty($options['range'])){
                $post['geopos']=$options['geox'].":".$options['geoy'].":".$options['range'];
            }else{
                $this->_errno = -1;
                $this->_errmsg = "geox or range can not empty";
                return false;
            }
        }else{
            if(!empty($options['geoy']) || !empty($options['range'])){
                $this->_errno = -1;
                $this->_errmsg = "geox can not empty";
                return false;
            }
        }
        if(isset($options['file_mark']) && $options['file_mark']!=''){
            if(!is_int($options['file_mark'])){
                $this->_errno = -1;
                $this->_errmsg = "flie_mark param must be int type";
                return false;
            }
            if($options['file_mark']<0 || $options['file_mark']>31){
                $this->_errno = -1;
                $this->_errmsg = "file_mark param must be form 0 to 31";
                return false;
            }
            $params['file_mark'] = $options['file_mark'];
        }
        $params['sortfiled'] = $sortfiled;
        $params['pagestart'] = $pagenum;
        $params['pagetotal'] = 1;
        $params['pagenum'] = $pagesize;
        $params['sortmode'] = $sortmode;
        $ret = $this->postData(self::searchurl, $post, $params);
        return $ret;
    }

    /**
     * 取得错误码
     *
     * @return int
     * @author ytf606
     */
    public function errno() {
        return $this->_errno;
    }

    /**
     * 取得错误信息
     *
     * @return string
     * @author ytf606
     */
    public function errmsg() {
        return $this->_errmsg;
    }

    private function postData($baseurl, $post, $params) {
        $url = $baseurl . '?' . http_build_query( $params );
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
          //var_dump($ret,$info);
        if(empty($info['http_code'])) {
            $this->_errno = -4;
            $this->_errmsg = "fulltext search service internal error";
        } else if($info['http_code'] == 607) {
            $this->_errno = 607;
            $this->_errmsg = $this->_errmsgs[607];
        } else if($info['http_code'] != 200) {
            $this->_errno = -1;
            $this->_errmsg = $this->_errmsgs[-4];
        } else {
            if($info['size_download'] == 0) { // get MailError header
                $this->_errno = SAE_ErrInternal;
                $this->_errmsg = "fulltext search service internal error";
            } else {
                $array = json_decode(trim($ret), true);
                if ( $array['errno'] !== 0 ) {
                    $this->_errno = $array['errno'];
                    $this->_errmsg = $array['errmsg'];
                    return false;
                } else {
                    $this->_errno = SAE_Success;
                    $this->_errmsg = 'OK';
                    if ( isset($array['result']) ) {
                        return $array['result'];
                    } else {
                        return true;
                    }
                }
            }
        }
        return false;
    }
}