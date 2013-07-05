<?php
/**
 * SAE Rank服务
 *
 * @package
 * @version
 * @author Seeker Zhang
 */

/**
 * SAE Rank<br />
 * 排行榜服务
 *
 * <code>
 * <?php
 * $bill = new SaeRank();
 *
 * //添加排行榜
 * $ret = $bill->create("zhang_money", 100);
 * if($ret===false)
 *        var_dump($bill->errno(), $bill->errmsg());
 *
 * //添加或设置key的value
 * $ret = $bill->set("zhang_money", "friendA", 30, true);
 * if($ret===false)
 *        var_dump($bill->errno(), $bill->errmsg());
 *
 * //获得排行榜
 * $ret = $bill->getList("zhang_money");
 * if($ret===false)
 *        var_dump($bill->errno(), $bill->errmsg());
 *
 * //清除排行榜
 * $ret = $bill->clear();
 * if($ret===false)
 *        var_dump($bill->errno(), $bill->errmsg());
 *
 * ?>
 * </code>
 *
 * 错误码参考：
 *  - errno: 0         成功
 *  - errno: -1     参数错误
 *  - errno: -2     查找的元素没有在排行榜中
 *  - errno: -3     查找的元素的排名超出排行榜的范围
 *  - errno: -4     查找的元素的值改变后小于排行榜的最小值
 *  - errno: -5     排行榜不存在
 *  - errno: -6        内部错误
 *  - errno: -7        rest端错误
 *  - errno: -8        排行榜php扩展错误
 *  - errno: -9     App中排行榜数量超限
 *  - errno：-10    排行榜已经存在
 *  - errno: -11    应用中排行榜个数为0
 * @package sae
 * @author Seeker Zhang
 *
 */

class SaeRank extends SaeObject
{
    private $_accesskey = "";
    private $_secretkey = "";
    private $_errno = SAE_Success;
    private $_errmsg ="OK";

    const baseurl = "http://rank.sae.sina.com.cn/index.php";

    /**
     * 构造对象
     *
     */
    function __construct()
    {
        $this->_accesskey = SAE_ACCESSKEY;
        $this->_secretkey = SAE_SECRETKEY;
        $this->_url = self::baseurl;
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
     * 设置key
     *
     * 只有使用其他应用的key时才需要调用
     *
     * @param string $accesskey
     * @param string $secretkey
     * @return void
     * @author Seeker Zhang
     */
    public function setAuth( $accesskey, $secretkey)
    {
        $accesskey = trim($accesskey);
        $secretkey = trim($secretkey);
        $this->_accesskey = $accesskey;
        $this->_secretkey = $secretkey;
        return true;
    }

    /**
     * 创建排行榜
     *
     * @param string $namespace 排行榜的命名空间，namespace的最大长度为128个字节
     * @param int $number 排行榜中key的数量，上限为10000，超出则失败，errno：-1
     * @param int $expire 排行榜失效时间，单位（分min）
     * @return bool 成功返回true，失败返回false。<br />
     * 注意：排名的起止区间为[0, $number-1]，即排名为1时返回排名为0
     * @author Seeker Zhang
     */
    function create($namespace, $number, $expire=0)
    {
        $funname="create";
        $mnamespace=substr($namespace,0,128);
        $retstr=rankSend($this->_url, $funname, $mnamespace, $number, $expire);
        $retary=$this->buildRet($retstr);

        if($retary[0]==0)
            return true;
        else
            return false;
    }

    /**
     * 设置或添加key，value
     *
     * @param string $namespace 排行榜的命名空间
     * @param string $key 排行榜中的元素的名称,key的长度最大为256个字节
     * @param int $value 要设置的值
     * @param bool $rankReturn 是否返回排名的标识
     * @return int   当rankReturn=true时，返回更新后元素的排名，失败返回false。<br />
     *               当rankReturn=fasle时，成功返回true，失败返回false。
     * @author Seeker Zhang
     */
    function set($namespace, $key, $value, $rankReturn=false)
    {
        if(!$rankReturn)
            $rankReturn=0;
        $funname="set";
        $mkey=substr($key,0,256);
        $retstr=rankSend($this->_url, $funname, $namespace, $mkey, $value, $rankReturn);
        $retary=$this->buildRet($retstr);

        if($rankReturn)
        {
            if($retary[0]==0)
                return $retary[2];
            else
                return false;
        }
        else
        {
            if($retary[0]==0)
                return true;
            else
                return false;
        }
    }

    /**
     * 在排行榜元素$key的值的基础上加$value
     *
     * @param string $namespace 排行榜的命名空间
     * @param string $key 排行榜中的元素的名称，key的长度最大为256个字节
     * @param int $value 值的增量。
     * @param bool $rankReturn 是否返回排名的标识
     * @return int     当rankReturn=true时，返回更新后元素的排名，失败返回false。<br />
     *                当rankReturn=fasle时，成功返回true，失败返回false。
     * @author Seeker Zhang
     */
    function increase($namespace, $key, $value, $rankReturn=false)
    {
        if(!$rankReturn)
            $rankReturn=0;
        $funname="increase";
        $mkey=substr($key,0,256);
        $retstr=rankSend($this->_url, $funname, $namespace, $mkey, $value, $rankReturn);
        $retary=$this->buildRet($retstr);

        if($rankReturn)
        {
            if($retary[0]==0)
                return $retary[2];
            else
                return false;
        }
        else
        {
            if($retary[0]==0)
                return true;
            else
                return false;
        }
    }

    /**
     * 在排行榜元素$key的值的基础上减$value
     *
     * @param string $namespace 排行榜的命名空间
     * @param string $key 排行榜中的元素的名称，key的长度最大为256个字节
     * @param int $value 值的减量。
     * @param bool $rankReturn 是否返回排名的标识
     * @return int     当rankReturn=true时，返回更新后元素的排名，失败返回false。<br />
     *                当rankReturn=false时，成功返回true，失败返回false。<br />
     *                当元素$key的值改变后，排名不在排行榜的存储范围内，返回false。
     * @author Seeker Zhang
     */
    function decrease($namespace, $key, $value, $rankReturn=false)
    {
        if(!$rankReturn)
               $rankReturn=0;
        $funname="decrease";
        $mkey=substr($key,0,256);
        $retstr=rankSend($this->_url, $funname, $namespace, $mkey, $value, $rankReturn);
        $retary=$this->buildRet($retstr);

        if($rankReturn)
        {
            if($retary[0]==0)
                return $retary[2];
            else
                return false;
        }
        else
        {
            if($retary[0]==0)
                return true;
            else
                return false;
        }
    }

    /**
     * 获得元素的排名
     *
     * @param string $namespace 排行榜的命名空间
     * @param string $key 排行榜中的元素的名称，key的长度最大为256个字节
     * @return int     成功时返回$key在排行榜中的排名，失败返回false。
     * @author Seeker Zhang
     */
    function getRank($namespace, $key)
    {
        $funname="getrank";
        $mkey=substr($key,0,256);
        $retstr=rankSend($this->_url, $funname, $namespace, $mkey);
        $retary=$this->buildRet($retstr);

        if($retary[0]==0)
            return $retary[2];
        else
            return false;
    }

    /**
     * 获得元素的值
     *
     * @param string $namespace 排行榜的命名空间
     * @param string $key 排行榜中的元素的名称，key的长度最大为256个字节
     * @return int     成功时返回$key在排行榜中的值，失败返回false。
     * @author Seeker Zhang
     */
    function getValue($namespace, $key)
    {
        $funname="getvalue";
        $mkey=substr($key,0,256);
        $retstr=rankSend($this->_url, $funname, $namespace, $mkey);
        $retary=$this->buildRet($retstr);

        if($retary[0]==0)
            return $retary[2];
        else
            return false;
    }

    /**
     * 删除排行榜中的元素$key
     *
     * @param string $namespace 排行榜的命名空间
     * @param string $key 排行榜中的元素的名称，key的长度最大为256个字节
     * @param bool $rankReturn 是否返回排名的标识
     * @return int     当rankReturn=true时，返回删除前元素的排名，失败返回false。<br />
     *                当rankReturn=false时，成功返回true，失败返回false。
     * @author Seeker Zhang
     */
    function delete($namespace, $key, $rankReturn=false)
    {
        if(!$rankReturn)
            $rankReturn=0;
        $funname="delete";
        $mkey=substr($key,0,256);
        $retstr=rankSend($this->_url, $funname, $namespace, $mkey, $rankReturn);
        $retary=$this->buildRet($retstr);

        if($rankReturn)
        {
            if($retary[0]==0)
                return $retary[2];
            else
                return false;
        }
        else
        {
            if($retary[0]==0)
                return true;
            else
                return false;
        }
    }

    /**
     * 获得实时排行榜数据
     *
     * @param string $namespace 排行榜的命名空间
     * @param bool $order 返回结果是否有序的标识
     * @param int $offsetFrom 希望获得的起始排名，可选，仅当order为true时有效
     * @param int $offsetTo 希望获得的终止排名，可选，仅当order为true时有效
     * @return array 返回值的形式：array（array（key，value) ....)<br />
     * 注意：offsetFrom和offsetTo的取值区间为[0, $number-1]
     * 当order=true时，返回排名在[offsetFrom,offsetTo]之间的有序的结果，offsetFrom<offsetTo ，不设置时返回所有结果。<br />
     *    当order=false时，返回的结果不是有序的。<br />
     * 失败时返回false。
     * @author Seeker Zhang
     */
    function getList($namespace, $order=false, $offsetFrom=0, $offsetTo=PHP_INT_MAX)
    {
        $funname="getlist";
        $retstr=rankSend($this->_url, $funname, $namespace, $order, $offsetFrom, $offsetTo);
        $retary=$this->buildRet($retstr);
        $ret=array();
        if($retary[0]==0)
        {
            $i=0;
            $data=explode("\t", $retary[2]);
            $count=count($data)-1;

            for($i=0;$i<$count;$i+=2)
            {
                $ret[$i/2]=array($data[$i] => $data[$i+1]);
            }
            return $ret;
        }
        else
            return false;
    }

    /**
     * 获得应用中现有排行榜名称
     *
     * @return array 返回值的形式：array（rankNum，rank1，rank2，....) <br />
     * 成功返回包括排行榜的数量和名称，失败返回false。
     * @author Seeker Zhang
     */
    function getAllName()
    {
        $funname="getallname";
        $retstr=rankSend($this->_url, $funname);
        $retary=$this->buildRet($retstr);
        $ret=array();
        if($retary[0]==0)
        {
            $i=0;
            $data=explode("\t", $retary[2]);
            $count=count($data);

            for($i=0;$i<$count-1;$i++)
            {
                $ret[$i]=$data[$i];
            }
            return $ret;
        }
        else
            return false;
    }

    /**
     * 获得排行榜的具体信息
     *
     * @param string $namespace 排行榜的命名空间
     * @return array 返回值的形式：array（info1，info2，....) <br />
     * 成功返回包括排行榜的具体信息，失败返回false。
     * @author Seeker Zhang
     */
    function getInfo($namespace)
    {
        $funname="getinfo";
        $retstr=rankSend($this->_url, $funname, $namespace);
        $retary=$this->buildRet($retstr);
        if($retary[0]==0)
        {
            $i=0;
            $data=explode("\t", $retary[2]);
            $count=count($data)-1;

            for($i=0;$i<$count;$i+=2)
            {
                $ret[$data[$i]]=$data[$i+1];
            }
            return $ret;
        }
        else
            return false;
    }
    /**
     * 清除数据
     *
     * @param string $namespace 排行榜的命名空间
     * @return int  成功返回true，失败返回false。
     * @author Seeker Zhang
     */
    function clear($namespace)
    {
        $funname="clear";
        $retstr=rankSend($this->_url, $funname, $namespace);
        $retary=$this->buildRet($retstr);
        if($retary[0]==0)
            return true;
        else
            return false;
    }

    private function buildRet($ret)
    {
       $retary=explode("\n", $ret);
       if(count($retary)!=4)
       {
           $this->_errno = -5;
           $this->_errmsg = "return value error:$ret";
           $retary=array($this->_errno);
           return $retary;
       }
       $retary[2]=trim($retary[2], "\r");
       if($retary[0]==0)
       {
           $this->_errno = SAE_Success;
           $this->_errmsg = "OK";
       }
       else
       {
           $this->_errno = $retary[0];
           $this->_errmsg = $retary[1];
       }
       return $retary;
    }
}