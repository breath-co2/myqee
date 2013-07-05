<?php
/**
 * SAE数据存储服务
 *
 * @author quanjun
 * @version $Id$
 * @package sae
 *
 */

/**
 * SaeStorage class
 * Storage服务适合用来存储用户上传的文件，比如头像、附件等。不适合存储代码类文件，比如页面内调用的JS、CSS等，尤其不适合存储追加写的日志。使用Storage服务来保存JS、CSS或者日志，会严重影响页面响应速度。建议JS、CSS直接保存到代码目录，日志使用sae_debug()方法记录。
 *
 * <code>
 * <?php
 * $s = new SaeStorage();
 * $s->upload( 'example' , 'remote_file.txt' , 'local_file.txt' );
 *
 * echo $s->read( 'example' , 'thebook') ;
 * // will echo 'bookcontent!';
 *
 * echo $s->getUrl( 'example' , 'thebook' );
 * // will echo 'http://appname-example.stor.sinaapp.com/thebook';
 *
 * ?>
 * </code>
 *
 * 常见错误码参考：
 *  - errno: 0         成功
 *  - errno: -2        配额统计错误
 *  - errno: -3        权限不足
 *  - errno: -7        Domain不存在
 *  - errno: -12    存储服务器返回错误
 *  - errno: -18     文件不存在
 *  - errno: -101    参数错误
 *  - errno: -102    存储服务器连接失败
 * 注：可使用SaeStorage::errmsg()方法获得当前错误信息。
 *
 * @package sae
 * @author  quanjun
 *
 */

class SaeStorage extends SaeObject
{
    /**
     * 用户accessKey
     * @var string
     */
    private $accessKey = '';
    /**
     * 用户secretKey
     * @var string
     */
    private $secretKey = '';
    /**
     * 运行过程中的错误信息
     * @var string
     */
    private $errMsg = 'success';
    /**
     * 运行过程中的错误代码
     * @var int
     */
    private $errNum = 0;
    /**
     * 应用名
     * @var string
     */
    private $appName = '';
    /**
     * @var string
     */
    private $restUrl = '';
    /**
     * @var string
     */
    private $filePath= '';
    /**
     * 文件URL主域名
     * @var string
     */
    private $basedomain = 'stor.sinaapp.com';
    /**
     * CDN URL主域名
     * @var string
     */
    private $cdndomain = 'sae.sinacdn.com';
    /**
     * 该类所支持的所有方法
     * @var array
     * @ignore
     */
    protected $_optUrlList = array(
        "uploadfile"=>'?act=uploadfile&ak=_AK_&sk=_SK_&dom=_DOMAIN_&attr=_ATTR_',
        "getdomfilelist"=>'?act=getdomfilelist&ak=_AK_&sk=_SK_&dom=_DOMAIN_&prefix=_PREFIX_&limit=_LIMIT_&skip=_SKIP_',
        "getfileattr"=>'?act=getfileattr&ak=_AK_&sk=_SK_&dom=_DOMAIN_&attrkey=_ATTRKEY_',
        "getfilecontent"=>'?act=getfilecontent&ak=_AK_&sk=_SK_&dom=_DOMAIN_',
        "delfile"=>'?act=delfile&ak=_AK_&sk=_SK_&dom=_DOMAIN_',
        "delfolder"=>'?act=delfolder&ak=_AK_&sk=_SK_&dom=_DOMAIN_',
        "getdomcapacity"=>'?act=getdomcapacity&ak=_AK_&sk=_SK_&dom=_DOMAIN_',
        "setdomattr"=>'?act=setdomattr&ak=_AK_&sk=_SK_&dom=_DOMAIN_&attr=_ATTR_',
        "setfileattr"=>'?act=setfileattr&ak=_AK_&sk=_SK_&dom=_DOMAIN_&attr=_ATTR_',
        "getfilesnum"=>'?act=getfilesnum&ak=_AK_&sk=_SK_&dom=_DOMAIN_&path=_PATH_',
        "getfileslist"=>'?act=getfileslist&ak=_AK_&sk=_SK_&dom=_DOMAIN_&path=_PATH_&limit=_LIMIT_&skip=_SKIP_&fold=_FOLD_',
    );
    /**
     * 构造函数
     * $_accessKey与$_secretKey可以为空，为空的情况下可以认为是公开读文件
     * @param string $_accessKey
     * @param string $_secretKey
     * @return void
     * @author Elmer Zhang
     */
    public function __construct( $_accessKey='', $_secretKey='' )
    {
        if( $_accessKey== '' ) $_accessKey = SAE_ACCESSKEY;
        if( $_secretKey== '' ) $_secretKey = SAE_SECRETKEY;

        $this->setAuth( $_accessKey, $_secretKey );
    }

    /**
     * 设置key
     *
     * 当需要访问其他APP的数据时使用
     *
     * @param string $akey
     * @param string $skey
     * @return void
     * @author Elmer Zhang
     * @ignore
     */
    public function setAuth( $akey , $skey , $_appName = '' )
    {
        $this->initOptUrlList( $this->_optUrlList);

        if( $_appName == '') {
            $this->appName = $_SERVER[ 'HTTP_APPNAME' ];
        } else {
            $this->appName = $_appName;
        }

        $this->init( $akey, $skey );
    }

    /**
     * 返回运行过程中的错误信息
     *
     * @return string
     * @author Elmer Zhang
     */
    public function errmsg()
    {
        $ret = $this->errMsg." url(".$this->filePath.")";
        $this->restUrl = '';
        $this->errMsg = 'success!';
        return $ret;
    }

    /**
     * 返回运行过程中的错误代码
     *
     * @return int
     * @author Elmer Zhang
     */
    public function errno()
    {
        $ret = $this->errNum;
        $this->errNum = 0;
        return $ret;
    }

    /**
     * 获取appname，供集成的类使用
     *
     * @return string
     * @author lazypeople
     */
    public function getAppname()
    {
        $ret = $this->appName;
        return $ret;
    }

    /**
     * 取得通过CDN访问存储文件的url
     *
     * @param string $domain
     * @param string $filename
     * @return string
     * @author Elmer Zhang
     */
    public function getCDNUrl( $domain, $filename ) {

        // make it full domain
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);

        if ( SAE_CDN_ENABLED ) {
            $filePath = "http://".$this->appName.'.'.$this->cdndomain . "/.app-stor/$domain/$filename";
        } else {
            $domain = $this->getDom($domain);
            $filePath = "http://".$domain.'.'.$this->basedomain . "/$filename";
        }
        return $filePath;
    }

    /**
     * 取得访问存储文件的url
     *
     * @param string $domain
     * @param string $filename
     * @return string
     * @author Elmer Zhang
     */
    public function getUrl( $domain, $filename ) {

        // make it full domain
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);
        $domain = $this->getDom($domain);

        $this->filePath = "http://".$domain.'.'.$this->basedomain . "/$filename";
        return $this->filePath;
    }

    private function setUrl( $domain , $filename )
    {
        $domain = trim($domain);
        $filename = trim($filename);

        $this->filePath = "http://".$domain.'.'.$this->basedomain . "/$filename";
    }

    /**
     * 将数据写入存储
     *
     * 注意：文件名左侧所有的'/'都会被过滤掉。
     *
     * @param string $domain 存储域,在在线管理平台.storage页面可进行管理
     * @param string $destFileName 文件名
     * @param string $content 文件内容,支持二进制数据
     * @param int $size 写入长度,默认为不限制
     * @param array $attr 文件属性，可设置的属性请参考 SaeStorage::setFileAttr() 方法
     * @param bool $compress 是否gzip压缩。如果设为true，则文件会经过gzip压缩后再存入Storage，常与$attr=array('encoding'=>'gzip')联合使用
     * @return string 写入成功时返回该文件的下载地址，否则返回false
     * @author Elmer Zhang
     */
    public function write( $domain, $destFileName, $content, $size=-1, $attr=array(), $compress = false )
    {
        $domain = trim($domain);
        $destFileName = $this->formatFilename($destFileName);

        if ( $domain == '' || $destFileName == '' )
        {
            $this->errMsg = 'the value of parameter (domain,destFileName,content) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        if ( $size > -1 )
            $content = substr( $content, 0, $size );

        $srcFileName = tempnam(SAE_TMP_PATH, 'SAE_STOR_UPLOAD');
        if ($compress) {
            file_put_contents("compress.zlib://" . $srcFileName, $content);
        } else {
            file_put_contents($srcFileName, $content);
        }

        $re = $this->upload($domain, $destFileName, $srcFileName, $attr);
        unlink($srcFileName);
        return $re;
    }

    /**
     * 将文件上传入存储
     *
     * 注意：文件名左侧所有的'/'都会被过滤掉。
     *
     * @param string $domain 存储域,在在线管理平台.storage页面可进行管理
     * @param string $destFileName 目标文件名
     * @param string $srcFileName 源文件名
     * @param array $attr 文件属性，可设置的属性请参考 SaeStorage::setFileAttr() 方法
     * @param bool $compress 是否gzip压缩。如果设为true，则文件会经过gzip压缩后再存入Storage，常与$attr=array('encoding'=>'gzip')联合使用
     * @return string 写入成功时返回该文件的下载地址，否则返回false
     * @author Elmer Zhang
     */
    public function upload( $domain, $destFileName, $srcFileName, $attr = array(), $compress = false )
    {
        $domain = trim($domain);
        $destFileName = $this->formatFilename($destFileName);

        if ( $domain == '' || $destFileName == '' || $srcFileName == '' )
        {
            $this->errMsg = 'the value of parameter (domain,destFile,srcFileName) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        if ($compress) {
            $srcFileNew = tempnam(SAE_TMP_PATH, 'SAE_STOR_UPLOAD');
            file_put_contents("compress.zlib://" . $srcFileNew, file_get_contents($srcFileName));
            $srcFileName = $srcFileNew;
        }

        $domain = $this->getDom($domain);
        $parseAttr = $this->parseFileAttr($attr);

        $this->setUrl( $domain, $destFileName );

        $urlStr = $this->optUrlList['uploadfile'];
        $urlStr = str_replace( '_DOMAIN_', $domain , $urlStr );
        $urlStr = str_replace( '_ATTR_', urlencode(json_encode($parseAttr)), $urlStr );
        $postData = array( 'srcFile' => "@{$srcFileName}" , 'destfile' => $destFileName);
        $ret = $this->parseRetData( $this->getJsonContentsAndDecode( $urlStr, $postData ) );
        if ( $ret !== false )
            return $this->filePath;
        else
            return false;
    }


    /**
     * 获取指定domain下的文件名列表
     *
     * <code>
     * <?php
     * // 列出 Domain 下所有路径以photo开头的文件
     * $stor = new SaeStorage();
     *
     * $num = 0;
     * while ( $ret = $stor->getList("test", "photo", 100, $num ) ) {
     *         foreach($ret as $file) {
     *             echo "{$file}\n";
     *             $num ++;
     *         }
     * }
     *
     * echo "\nTOTAL: {$num} files\n";
     * ?>
     * </code>
     *
     * @param string $domain    存储域,在在线管理平台.storage页面可进行管理
     * @param string $prefix    路径前缀
     * @param int $limit        返回条数,最大100条,默认10条
     * @param int $offset        起始条数。limit与offset之和最大为10000，超过此范围无法列出。
     * @return array 执行成功时返回文件列表数组，否则返回false
     * @author Elmer Zhang
     */
    public function getList( $domain, $prefix=NULL, $limit=10, $offset = 0 )
    {
        $domain = trim($domain);

        if ( $domain == '' )
        {
            $this->errMsg = 'the value of parameter (domain) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $domain = $this->getDom($domain);

        $urlStr = $this->optUrlList['getdomfilelist'];
        $urlStr = str_replace( '_DOMAIN_', $domain, $urlStr );
        $urlStr = str_replace( '_PREFIX_', urlencode($prefix), $urlStr );
        $urlStr = str_replace( '_LIMIT_', $limit, $urlStr );
        $urlStr = str_replace( '_SKIP_', $offset, $urlStr );

        return $this->parseRetData( $this->getJsonContentsAndDecode( $urlStr ) );
    }

    /**
     * 获取指定Domain、指定目录下的文件列表
     *
     * @param string $domain    存储域
     * @param string $path        目录地址
     * @param int $limit        单次返回数量限制，默认100，最大1000
     * @param int $offset        起始条数
     * @param int $fold            是否折叠目录
     * @return array 执行成功时返回列表，否则返回false
     * @author Elmer Zhang
     */
    public function getListByPath( $domain, $path = NULL, $limit = 100, $offset = 0, $fold = true )
    {
        $domain = trim($domain);

        if ( $domain == '' )
        {
            $this->errMsg = 'the value of parameter (domain) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $domain = $this->getDom($domain);

        $urlStr = $this->optUrlList['getfileslist'];
        $urlStr = str_replace( '_DOMAIN_', $domain, $urlStr );
        $urlStr = str_replace( '_PATH_', urlencode($path), $urlStr );
        $urlStr = str_replace( '_LIMIT_', $limit, $urlStr );
        $urlStr = str_replace( '_SKIP_', $offset, $urlStr );
        $urlStr = str_replace( '_FOLD_', intval($fold), $urlStr );

        return $this->parseRetData( $this->getJsonContentsAndDecode( $urlStr ) );
    }

    /**
     * 获取指定domain下的文件数量
     *
     *
     * @param string $domain    存储域,在在线管理平台.storage页面可进行管理
     * @param string $path        目录
     * @return array 执行成功时返回文件数，否则返回false
     * @author Elmer Zhang
     */
    public function getFilesNum( $domain, $path = NULL )
    {
        $domain = trim($domain);

        if ( $domain == '' )
        {
            $this->errMsg = 'the value of parameter (domain) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $domain = $this->getDom($domain);

        $urlStr = $this->optUrlList['getfilesnum'];

        $urlStr = str_replace( '_DOMAIN_', $domain, $urlStr );
        $urlStr = str_replace( '_PATH_', urlencode($path), $urlStr );

        return $this->parseRetData( $this->getJsonContentsAndDecode( $urlStr ) );
    }

    /**
     * 获取文件属性
     *
     * @param string $domain     存储域
     * @param string $filename    文件地址
     * @param array $attrKey    属性值,如 array("fileName", "length")，当attrKey为空时，以关联数组方式返回该文件的所有属性。
     * @return array 执行成功以数组方式返回文件属性，否则返回false
     * @author Elmer Zhang
     */
    public function getAttr( $domain, $filename, $attrKey=array() )
    {
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);

        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter (domain,filename) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $domain = $this->getDom($domain);

        $this->setUrl( $domain, $filename );

        $urlStr = $this->optUrlList['getfileattr'];
        $urlStr = str_replace( '_DOMAIN_', $domain, $urlStr );
        $urlStr = str_replace( '_ATTRKEY_', urlencode( json_encode( $attrKey ) ), $urlStr );
        $postData = array( 'filename' => $filename);
        $ret = $this->parseRetData( $this->getJsonContentsAndDecode( $urlStr, $postData ) );
        if ( is_object( $ret ) )
            return (array)$ret;
        else
            return $ret;
    }

    /**
     * 检查文件是否存在
     *
     * @param string $domain     存储域
     * @param string $filename     文件地址
     * @return bool
     * @author Elmer Zhang
     */
    public function fileExists( $domain, $filename )
    {
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);

        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter (domain,filename) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        if ( $this->getAttr( $domain, $filename, array('length') ) ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取文件的内容
     *
     * @param string $domain
     * @param string $filename
     * @return string 成功时返回文件内容，否则返回false
     * @author Elmer Zhang
     */
    public function read( $domain, $filename )
    {
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);

        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter (domain,filename) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $domain = $this->getDom($domain);

        $this->setUrl( $domain, $filename );
        $urlStr = $this->optUrlList['getfilecontent'];
        $urlStr = str_replace( '_DOMAIN_', $domain, $urlStr );

        $postData = array( 'filename' => $filename);
        $ret =  $this->getJsonContentsAndDecode( $urlStr, $postData, false );
        if ( is_array($ret) && isset( $ret['errno'] ) )
        {
            $this->parseRetData( $ret );
            return false;
        }
        return $ret;
    }

    /**
     * 删除目录
     *
     * @param string $domain    存储域
     * @param string $path      目录地址
     * @return bool
     * @author Elmer Zhang
     * @ignore
     */
    public function deleteFolder( $domain, $path )
    {
        $domain = trim($domain);
        $path = $this->formatFilename($path);

        if ( $domain == '' || $path == '' )
        {
            $this->errMsg = 'the value of parameter (domain,path) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $domain = $this->getDom($domain);

        $this->setUrl( $domain, $path );
        $urlStr = $this->optUrlList['delfolder'];
        $urlStr = str_replace( '_DOMAIN_', $domain, $urlStr );
        $postData = array( 'path' => $path);
        $ret = $this->parseRetData( $this->getJsonContentsAndDecode( $urlStr, $postData ) );
        if ( $ret === false )
            return false;
        if ( $ret[ 'errno' ] == 0 )
            return true;
        else
            return false;
    }

    /**
     * 删除文件
     *
     * @param string $domain
     * @param string $filename
     * @return bool
     * @author Elmer Zhang
     */
    public function delete( $domain, $filename )
    {
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);

        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter (domain,filename) can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $domain = $this->getDom($domain);

        $this->setUrl( $domain, $filename );
        $urlStr = $this->optUrlList['delfile'];
        $urlStr = str_replace( '_DOMAIN_', $domain, $urlStr );
        $postData = array( 'filename' => $filename);
        $ret = $this->parseRetData( $this->getJsonContentsAndDecode( $urlStr, $postData ) );
        if ( $ret === false )
            return false;
        if ( $ret[ 'errno' ] == 0 )
            return true;
        else
            return false;
    }


    /**
     * 设置文件属性
     *
     * 目前支持的文件属性
     *  - expires: 浏览器缓存超时,设置规则和domain expires的规则一致
     *  - encoding: 设置通过Web直接访问文件时，Header中的Content-Encoding。
     *  - type: 设置通过Web直接访问文件时，Header中的Content-Type。
     *  - private: 设置文件为私有，则文件不可被下载。
     *
     * <code>
     * <?php
     * $stor = new SaeStorage();
     *
     * $attr = array('expires' => 'modified 1y');
     * $ret = $stor->setFileAttr("test", "test.txt", $attr);
     * if ($ret === false) {
     *         var_dump($stor->errno(), $stor->errmsg());
     * }
     * ?>
     * </code>
     *
     * @param string $domain
     * @param string $filename     文件名
     * @param array $attr         文件属性。格式：array('attr0'=>'value0', 'attr1'=>'value1', ......);
     * @return bool
     * @author Elmer Zhang
     */
    public function setFileAttr( $domain, $filename, $attr = array() )
    {
        $domain = trim($domain);
        $filename = $this->formatFilename($filename);

        if ( $domain == '' || $filename == '' )
        {
            $this->errMsg = 'the value of parameter domain,filename can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $parseAttr = $this->parseFileAttr($attr);
        if ($parseAttr == false) {
            $this->errMsg = 'the value of parameter attr must be an array, and can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $domain = $this->getDom($domain);

        $urlStr = $this->optUrlList['setfileattr'];
        $urlStr = str_replace( '_DOMAIN_', $domain, $urlStr );
        $urlStr = str_replace( '_ATTR_', urlencode( json_encode( $parseAttr ) ), $urlStr );
        $postData = array( 'filename' => $filename);
        $ret = $this->parseRetData( $this->getJsonContentsAndDecode( $urlStr, $postData ) );
        if ( $ret === true )
            return true;
        if ( is_array($ret) && $ret[ 'errno' ] === 0 )
            return true;
        else
            return false;
    }

    /**
     * 设置Domain属性
     *
     * 目前支持的Domain属性
     *  - expires: 浏览器缓存超时
     *  - expires_type: 浏览器对于指定文件类型的缓存超时
     *  - allowReferer: 根据Referer防盗链
     *  - private: 是否私有Domain
     *  - 404Redirect: 404跳转页面，只能是本应用页面，或本应用Storage中文件。例如http://appname.sinaapp.com/404.html或http://appname-domain.stor.sinaapp.com/404.png
     *  - tag: Domain简介。格式：array('tag1', 'tag2')
     * <code>
     * <?php
     * // 缓存过期设置
     * $expires = 'modified 1d';
     * $expires_type = 'text/html 48h,image/png modified 1y';
     *
     * // 防盗链设置
     * $allowReferer = array();
     * $allowReferer['hosts'][] = '*.elmerzhang.com';        // 允许访问的来源域名，千万不要带 http://。支持通配符*和?
     * $allowReferer['hosts'][] = 'elmer.sinaapp.com';
     * $allowReferer['hosts'][] = '?.elmer.sinaapp.com';
     * $allowReferer['redirect'] = 'http://elmer.sinaapp.com/';    // 盗链时跳转到的地址，仅允许跳转到本APP的页面，且不可使用独立域名。如果不设置或者设置错误，则直接拒绝访问。
     * //$allowReferer = false;  // 如果要关闭一个Domain的防盗链功能，直接将allowReferer设置为false即可
     *
     * $stor = new SaeStorage();
     *
     * $attr = array('expires'=>$expires,'expires_type'=>$expires_type,'allowReferer'=>$allowReferer);
     * $ret = $stor->setDomainAttr("test", $attr);
     * if ($ret === false) {
     *         var_dump($stor->errno(), $stor->errmsg());
     * }
     *
     * ?>
     * </code>
     *
     * @param string $domain
     * @param array $attr         Domain属性。格式：array('attr0'=>'value0', 'attr1'=>'value1', ......);
     * 说明：
     *  - expires 格式：[modified] TIME_DELTA，例如modified 1y或者1y，modified关键字用于指定expire时间相对于文件的修改时间。默认expire时间是相对于access time。如果TIME_DELTA为负， Cache-Control header会被设置为no-cache。
     *  - TIME_DELTA，TIME_DELTA是一个表示时间的字符串，例如： 1y3M 48d 5s
     *  <pre>
     *  s   seconds
     *  ----------------------------------------------------------
     *  m   minutes
     *  ----------------------------------------------------------
     *  h   hours
     *  ----------------------------------------------------------
     *  d   days
     *  ----------------------------------------------------------
     *  w   weeks
     *  ----------------------------------------------------------
     *  M   months, 30 days
     *  ----------------------------------------------------------
     *  y   years, 365 days
     *  ----------------------------------------------------------
     *  </pre>
     *  - 两个特殊的TIME_DELTA：<pre>
     *  epoch sets the Expires header to 1 January, 1970 00:00:01 GMT.
     *  -----------------------------------------------------------------------------------------------
     *  max sets the Expires header to 31 December 2037 23:59:59 GMT, and the Cache-Control max-age to 10 years.
     *  -----------------------------------------------------------------------------------------------
     *  </pre>
     *  - expires_type 格式:TYPE [modified] TIME_DELTA,TYPE为文件的mimetype，例如text/html, text/plain, image/gif。多条expires-type规则之间以 , 隔开。例如：text/html 48h,image/png modified 1y
     * @return bool
     * @author Elmer Zhang,Lazypeople
     */
    public function setDomainAttr( $domain, $attr = array() )
    {
        $domain = trim($domain);

        if ( $domain == '' )
        {
            $this->errMsg = 'the value of parameter domain can not be empty!';
            $this->errNum = -101;
            return false;
        }

        // make it full domain
        $domain = $this->getDom($domain);

        $parseAttr = $this->parseDomainAttr($attr);

        if ($parseAttr == false) {
            $this->errMsg = 'the value of parameter attr must be an array, and can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $urlStr = $this->optUrlList['setdomattr'];
        $urlStr = str_replace( '_DOMAIN_', $domain, $urlStr );
        $urlStr = str_replace( '_ATTR_', urlencode( json_encode( $parseAttr ) ), $urlStr );
        $ret = $this->parseRetData( $this->getJsonContentsAndDecode( $urlStr ) );
        if ( $ret === true )
            return true;
        if ( is_array($ret) && $ret['errno'] === 0 )
            return true;
        else
            return false;
    }

    /**
     * 获取domain所占存储的大小
     *
     * @param string $domain
     * @return int
     * @author Elmer Zhang
     */
    public function getDomainCapacity( $domain )
    {
        $domain = trim($domain);

        if ( $domain == '' )
        {
            $this->errMsg = 'the value of parameter \'$domain\' can not be empty!';
            $this->errNum = -101;
            return false;
        }

        $domain = $this->getDom($domain);

        $urlStr = $this->optUrlList['getdomcapacity'];
        $urlStr = str_replace( '_DOMAIN_', $domain, $urlStr );
        $ret = (array)$this->parseRetData( $this->getJsonContentsAndDecode( $urlStr ) );
        if ( $ret[ 'errno' ] == 0 )
            return $ret['data'];
        else
            return false;
    }

    // =================================================================

    /**
     * @ignore
     */
    protected function parseDomainAttr($attr) {
        $parseAttr = array();

        if ( !is_array( $attr ) || empty( $attr ) ) {
            return false;
        }

        foreach ( $attr as $k => $a ) {
            switch ( strtolower( $k ) ) {
                case '404redirect':
                    if ( !empty($a) && is_string($a) ) {
                        $parseAttr['404Redirect'] = trim($a);
                    }
                    break;
                case 'private':
                    $parseAttr['private'] = $a ? true : false;
                    break;
                case 'expires':
                    $parseAttr['expires'] = $a;
                    break;
                case 'expires_type':
                    $parseAttr['expires_type'] = $a;
                    break;
                case 'allowreferer':
                    if ( isset($a['hosts']) && is_array($a['hosts']) && !empty($a['hosts']) ) {
                        $parseAttr['allowReferer'] = array();
                        $parseAttr['allowReferer']['hosts'] = $a['hosts'];

                        if ( isset($a['redirect']) && is_string($a['redirect']) ) {
                            $parseAttr['allowReferer']['redirect'] = $a['redirect'];
                        }
                    } else {
                        $parseAttr['allowReferer']['host'] = false;
                    }
                    break;
                case 'tag':
                    if (is_array($a) && !empty($a)) {
                        $parseAttr['tag'] = array();
                        foreach ($a as $v) {
                            $v = trim($v);
                            if (is_string($v) && !empty($v)) {
                                $parseAttr['tag'][] = $v;
                            }
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        return $parseAttr;
    }

    /**
     * @ignore
     */
    protected function parseFileAttr($attr) {
        $parseAttr = array();

        if ( !is_array( $attr ) || empty( $attr ) ) {
            return false;
        }

        foreach ( $attr as $k => $a ) {
            switch ( strtolower( $k ) ) {
                case 'expires':
                    $parseAttr['expires'] = $a;
                    break;
                case 'encoding':
                    $parseAttr['encoding'] = $a;
                    break;
                case 'type':
                    $parseAttr['type'] = $a;
                    break;
                case 'private':
                    $parseAttr['private'] = intval($a);
                    break;
                default:
                    break;
            }
        }

        return $parseAttr;
    }

    /**
     * @ignore
     */
    protected function initOptUrlList( $_optUrlList=array() )
    {
        $this->optUrlList = array();
        $this->optUrlList = $_optUrlList;

        while ( current( $this->optUrlList ) !== false ) {
            $this->optUrlList[ key( $this->optUrlList ) ] = SAE_STOREHOST.current($this->optUrlList);
            next( $this->optUrlList );
        }

        reset( $this->optUrlList );
        //$this->init( $this->accessKey, $this->secretKey );



    }
    /**
     * 构造函数运行时替换所有$this->optUrlList值里的accessKey与secretKey
     * @param string $_accessKey
     * @param string $_secretKey
     * @return void
     * @ignore
     */
    protected function init( $_accessKey, $_secretKey )
    {
        $_accessKey = trim($_accessKey);
        $_secretKey = trim($_secretKey);

        //$this->appName = $_SERVER[ 'HTTP_APPNAME' ];
        $this->accessKey = $_accessKey;
        $this->secretKey = $_secretKey;
        while ( current( $this->optUrlList ) !== false )
        {
            $this->optUrlList[ key( $this->optUrlList ) ] = str_replace( '_AK_', $this->accessKey, current( $this->optUrlList ) );
            $this->optUrlList[ key( $this->optUrlList ) ]= str_replace( '_SK_', $this->secretKey, current( $this->optUrlList ) );
            next( $this->optUrlList );
        }


        reset( $this->optUrlList );
    }

    /**
     * 最终调用server端方法的rest函数封装
     * @ignore
     */
    protected function getJsonContentsAndDecode( $url, $postData = array(), $decode = true ) //获取对应URL的JSON格式数据并解码
    {
        if( empty( $url ) )
            return false;
        $this->restUrl = $url;
        $ch=curl_init();

        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HTTPGET, true );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );


        if ( !Empty( $postData ) )
        {
            curl_setopt($ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $postData );
        }


        curl_setopt( $ch, CURLOPT_USERAGENT, 'SAE PHP Runtime' );
        $content=curl_exec( $ch );
        $info = curl_getinfo($ch);
        //var_dump($content, $info);
        curl_close($ch);
        if( false !== $content )
        {
            if ($decode) {
                $tmp = json_decode( $content , true);

                if ( !empty( $tmp ) )//若非结构数据则直接抛出数据源
                    return $tmp;
            }
            return $content;
        }
        else
            return array( 'errno'=>-102, 'errmsg'=>'bad request' );
    }

    /**
     * 解析并验证server端返回的数据结构
     * @ignore
     */
    public function parseRetData( $retData = array() )
    {
        //    print_r( $retData );
        if ( !isset( $retData['errno'] ) || !isset( $retData['errmsg'] ) )
        {
            //    print_r( $retData );
            $this->errMsg = 'bad request';
            $this->errNum = -12;
            return false;
        }
        if ( $retData['errno'] !== 0 )
        {
            $this->errMsg = $retData[ 'errmsg' ];
            $this->errNum = $retData['errno'];
            return false;
        }
        if ( isset( $retData['data'] ) )
            return $retData['data'];
        return $retData;
    }

    /**
     * domain拼接
     * @param string $domain
     * @param bool $concat
     * @return string
     * @author Elmer Zhang
     * @ignore
     */
    protected function getDom($domain, $concat = true) {
        $domain = strtolower(trim($domain));

        if ($concat) {
            if( strpos($domain, '-') === false ) {
                $domain = $this->appName .'-'. $domain;
            }
        } else {
            if ( ( $pos = strpos($domain, '-') ) !== false ) {
                $domain = substr($domain, $pos + 1);
            }
        }
        return $domain;
    }

    private function formatFilename($filename) {
        $filename = trim($filename);

        $encodings = array( 'UTF-8', 'GBK', 'BIG5' );

        $charset = mb_detect_encoding( $filename , $encodings);
        if ( $charset !='UTF-8' ) {
            $filename = mb_convert_encoding( $filename, "UTF-8", $charset);
        }
        $filename = preg_replace('/\/\.\//', '/', $filename);
        $filename = ltrim($filename, '/');
        $filename = preg_replace('/^\.\//', '', $filename);
        while ( preg_match('/\/\//', $filename) ) {
            $filename = preg_replace('/\/\//', '/', $filename);
        }

        return $filename;
    }
}