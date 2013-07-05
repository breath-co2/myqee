<?php
/**
 * SAE  地理信息
 *
 * @package sae
 * @version $Id$
 * @author liuxin
 */


/**
 * SAE  地理信息
 *
 * <code>
 * <?php
 * $SaeLocationObj = new SaeLocation();
 *
 * //根据起点与终点数据查询自驾车路线信息
 * $drive_route_arr = array('begin_coordinate'=>'116.317245,39.981437','end_coordinate'=>'116.328422,40.077796');
 * $drive_route = $SaeLocationObj->getDriveRoute($drive_route_arr);
 * echo 'drive_rote: ';
 * print_r($drive_route);
 * echo '</br>';
 *
 * // 失败时输出错误码和错误信息
 * if ($bus_route === false)
 *         var_dump($SaeLocationObj->errno(), $SaeLocationObj->errmsg());
 *
 * //根据起点与终点数据查询公交乘坐路线信息
 * $bus_route_arr = array('begin_coordinate'=>'116.317245,39.981437','end_coordinate'=>'116.328422,40.077796');
 * $bus_route = $SaeLocationObj->getBusRoute($bus_route_arr);
 * echo 'bus_rote: ';
 * print_r($bus_route);
 * echo '</br>';
 *
 *  // 失败时输出错误码和错误信息
 * if ($drive_route === false)
 *         var_dump($SaeLocationObj->errno(), $SaeLocationObj->errmsg());
 *
 * // 根据关键词查询公交线路及其站点信息
 * $bus_line_arr = array('q'=>'320路区间');
 * $bus_line = $SaeLocationObj->getBusLine($bus_line_arr);
 * echo 'bus_line: ';
 * print_r($bus_line);
 * echo '</br>';
 *
 *  // 失败时输出错误码和错误信息
 * if ($bus_line === false)
 *         var_dump($SaeLocationObj->errno(), $SaeLocationObj->errmsg());
 *
 * //根据关键词查询公交线路
 * $bus_station_arr = array('q'=>'回龙观');
 * $bus_station = $SaeLocationObj->getBusStation($bus_station_arr);
 * echo 'bus_station: ';
 * print_r($bus_station);
 * echo '</br>';
 *
 *  // 失败时输出错误码和错误信息
 * if ($bus_station === false)
 *         var_dump($SaeLocationObj->errno(), $SaeLocationObj->errmsg());
 *
 * //根据IP地址返回地理信息坐标
 * $ip_to_geo_arr = array('ip'=>'202.106.0.20,202.108.5.20');
 * $ip_to_geo = $SaeLocationObj->getIpToGeo($ip_to_geo_arr);
 * echo 'ip_to_geo: ';
 * print_r($ip_to_geo);
 * echo '</br>';
 *
 *  // 失败时输出错误码和错误信息
 * if ( $ip_to_geo === false)
 *         var_dump($SaeLocationObj->errno(), $SaeLocationObj->errmsg());
 * ?>
 * </code>
 *
 * 错误码参考：
 *  - errno: 0      成功
 *  - errno: -1     不合法参数
 *  - errno: -2     错误的参数输入（为空）
 *  - errno: -3     接口内部错误
 *  - errno: -4     其他错误
 *  - errno: 607    服务未初始化
 *
 * @package sae
 * @author liuxin
 *
 */

class SaeLocation extends SaeObject {
    private $_errno = SAE_Success;
    private $_errmsg = "OK";
    private $_errmsgs = array(
        -1 => "invalid parameters",
        -2 => "params can not be empty",
        -3 => "location search internal error",
        -4 => "unkown error",
        607 => "service is not enabled",
    );
    /**
     * @ignore
     */
    const baseurl_one = "http://locationone.sinaapp.com/com/LevelOne.php";

    /**
     * 构造对象
     */
    function __construct() {
    }

    /**
     * 根据起点与终点数据查询自驾车路线信息
     * @param array $post （关联型数组） 数组键表示要传递的参数，值表示请求的参数值，（*）表示必选的字段
     *         键值：    begin_pid             string     查询起点POI的ID，与begin_coordinate参数必选其一，begin_pid优先。
     *                begin_coordinate      string     查询起点的坐标，经度纬度用逗号分隔，与begin_pid参数必选其一，begin_pid优先。
     *                end_pid                   string     查询终点POI的ID，与end_coordinate参数必选其一，end_pid优先。
     *                end_coordinate     string     查询终点的坐标，经度纬度用逗号分隔，与end_pid参数必选其一，end_pid优先。
     *                type                      int             查询类型，0：速度优先、1：费用优先、2：距离优先，默认值为0。
     * @return array|bool 成功以json格式返回查询结果，失败返回false.
     * @author liuxin
     */
    public function getDriveRoute($post) {
        if(empty($post)) {
            $this->set_error(-2, $this->_errmsgs[-2]);
            return false;
        }
        $r1 = isset($post['begin_id'])&&isset($post['end_pid']);
        $r2 = isset($post['begin_coordinate'])&&isset($post['end_coordinate']);
        if(!($r1||$r2))
        {
            $this->set_error(-1, $this->_errmsgs[-1]);
            return false;
        }
        $url = self::baseurl_one."?type=".self::DRIVE_ROTE;
        $res = $this->_request($url,$post);
        return $res;
    }

    /**
     * 根据起点与终点数据查询公交乘坐路线信息
     * @param array $post （关联型数组） 数组键表示默要传递的参数，值表示请求的参数值。键值：
     *  - begin_pid            string    查询起点POI的ID，与begin_coordinate参数必选其一，begin_pid优先。
     *  - begin_coordinate    string    查询起点的坐标，经度纬度用逗号分隔，与begin_pid参数必选其一，begin_pid优先。
     *  - end_pid            string    查询终点POI的ID，与end_coordinate参数必选其一，end_pid优先。
     *  - end_coordinate    string    查询终点的坐标，经度纬度用逗号分隔，与end_pid参数必选其一，end_pid优先。
     *  - type                int        查询类型，0：速度优先、1：费用优先、2：距离优先，默认值为0。
     * @return array|bool 成功以json格式返回查询结果，失败返回false.
     * @author liuxin
     */
    public function getBusRoute($post) {
        if(empty($post)) {
            $this->set_error(-2, $this->_errmsgs[-2]);
            return false;
        }
        $r1 = isset($post['begin_id'])&&isset($post['end_pid']);
        $r2 = isset($post['begin_coordinate'])&&isset($post['end_coordinate']);
        if(!($r1||$r2))
        {
            $this->set_error(-1, $this->_errmsgs[-1]);
            return false;
        }
        $url = self::baseurl_one."?type=".self::BUS_ROTE;
        $res = $this->_request($url,$post);
        return $res;
    }

    /**
     * 根据关键词查询公交线路
     * @param array $post （关联型数组） 数组键表示要传递的参数，值表示请求的参数值，(*)表示必选的字段。键值：
     *  - q(*)    string    查询的关键词。
     *  - city    string    城市代码，默认为北京搜索。
     *  - page    int        返回结果的页码，默认为1，最大为40。
     *  - count    int        单页返回的记录条数，默认为10，最大为50。
     * @return array|bool 成功以json格式返回查询结果，失败返回false.
     * @author liuxin
     */
    public function getBusLine($post) {
        if(empty($post)||!isset($post['q'])) {
            $this->set_error(-2, $this->_errmsgs[-2]);
            return false;
        }
        $url = self::baseurl_one."?type=".self::BUS_LINE;
        $res = $this->_request($url,$post);
        return $res;
    }

    /**
     * 根据关键词查询公交线路
     * @param array $post （关联型数组） 数组键表示要传递的参数，值表示请求的参数值，(*)表示必选的字段。键值：
     *  - q(*)    string    查询的关键词。
     *  - city    string    城市代码，默认为北京搜索。
     *  - page    int        返回结果的页码，默认为1，最大为40。
     *  - count    int        单页返回的记录条数，默认为10，最大为50。
     * @return array|bool 成功以json格式返回查询结果，失败返回false.
     * @author liuxin
     */
    public function getBusStation($post) {
        if(empty($post)||!isset($post['q'])) {
            $this->set_error(-2,  $this->_errmsgs[-2]);
            return false;
        }
        $url = self::baseurl_one."?type=".self::BUS_STATION;
        $res = $this->_request($url,$post);
        return $res;
    }

    /**
     * 根据IP地址返回地理信息坐标
     * @param array $post （关联型数组） 数组键表示要传递的参数，值表示请求的参数值，(*)表示必选的字段。键值：
     *  - ip(*)        string    需要获取坐标的IP地址，多个IP用逗号分隔，最多不超过10个。
     * @return array|bool 成功以json格式返回查询结果，失败返回false.
     * @author liuxin
     */
    public function getIpToGeo($post) {
        if(empty($post)||!isset($post['ip'])) {
            $this->set_error(-2,   $this->_errmsgs[-2]);
            return false;
        }
        $url = self::baseurl_one."?type=".self::IP_TO_GEO;
        $res = $this->_request($url,$post);
        return $res;
    }


    private function _request($url, $array=null) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if($array != null) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $array);
        }
        $res = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if(empty($info['http_code'])) {
            $this->set_error(-4, $this->_errmsgs[-4]);
        } else if($info['http_code'] == 607) {
            $this->set_error(607, $this->_errmsgs[607]);
        } else if($info['http_code'] != 200) {
            $this->set_error(-3,$this->_errmsgs[-3]);
        } else {
            if($info['size_download'] == 0) { // get MailError header
                $this->set_error(SAE_ErrInternal,$this->_errmsg = "location search service internal error");
            } else {
                $array = json_decode($res, true);
                if ( isset($array['error_code']) ) {
                    $this->set_error($array['error_code'],$array['error']);
                    return $array['error'];
                } else {
                    $this->set_error(SAE_Success, 'OK');
                    return $array;
                }
            }
        }
        return false;
    }

    private function set_error($errno, $errmsg) {
        $this->_errno = $errno;
        $this->_errmsg = $errmsg;
    }

    public function errno() {
        return $this->_errno;
    }

    public function errmsg() {
        return $this->_errmsg;
    }

    /**
     * 查询分类：根据起点与终点数据查询自驾车路线信息
     */
    const DRIVE_ROTE = "LINE_ONE";

    /**
     * 查询分类：根据起点与终点数据查询公交乘坐路线信息
     */
    const BUS_ROTE = "LINE_TWO";

    /**
     * 查询分类：根据关键词查询公交线路信息
     */
    const BUS_LINE = "LINE_THREE";

    /**
     * 查询分类：根据起点与终点数据查询公交乘坐路线信息
     */
    const BUS_STATION = "LINE_FOURE";

    /**
     * 查询分类：根据IP地址返回地理信息坐标
     */
    const IP_TO_GEO = "GEO_ONE";
}