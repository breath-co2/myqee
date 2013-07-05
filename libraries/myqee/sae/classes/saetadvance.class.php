<?php
/**
 * 新浪微博高级操作类
 *
 * @author  Elmer Zhang
 * @version $Id$
 * @package sae
 *
 */


include_once('saet.ex.class.php');

/**
 * 新浪微博高级操作类
 * 继承于新浪微博操作类，添加一些仅面向合作开发者的接口的封装。使用此类前，请确认您的微博应用为合作开发者应用。<br />
 *
 * 使用前需要先手工调用saetadvance.ex.class.php <br />
 *
 * @package sae
 * @author Elmer Zhang
 * @version 1.0
 */
class SaeTAdvance extends SaeTClient {

    /**
     * 注册新浪微博帐号
     *
     * @access public
     * @param string $nick 昵称, 必须参数.不超过20个汉字.
     * @param string $gender 性别, 必须参数. m,男，f,女.
     * @param string $password 密码, 必须参数.
     * @param string $email 注册邮箱, 必须参数.
     * @param string $ip 注册用户用户当前真实的IP.必须参数.
     * @param string $province 可选参数. 参考省份城市编码表
     * @param string $city 可选参数. 参考省份城市编码表,1000为不限
     * @return array
     */
    function register($nick, $gender, $password, $email, $ip, $province = NULL, $city = NULL)
    {
        $params = array();
        $params['nick'] = $nick;
        $params['gender'] = $gender;
        $params['passsword'] = $password;
        $params['email'] = $email;
        $params['ip'] = $ip;
        if (isset($province)) $params['province'] = $province;
        if (isset($city)) $params['city'] = $city;

        return $this->oauth->post('http://api.t.sina.com.cn/account/register.json', $params);
    }

    /**
     * 二次注册微博的接口
     *
     * @access public
     * @param string $uid 用户UID，必选参数
     * @param string $nickname 昵称, 必须参数.不超过20个汉字.
     * @param string $gender 性别, 必须参数. m,男，f,女.
     * @param string $email 注册邮箱, 必须参数.
     * @param string $ip 注册用户用户当前真实的IP.必须参数.
     * @param string $province 参考省份城市编码表,必须参数.
     * @param string $city 可选参数. 参考省份城市编码表,1000为不限
     * @return array
     */
    function activate($uid, $nickname, $gender, $email, $ip, $province, $city = NULL)
    {
        $params = array();
        $params['uid'] = $uid;
        $params['nickname'] = $nickname;
        $params['gender'] = $gender;
        $params['email'] = $email;
        $params['ip'] = $ip;
        $params['province'] = $province;
        if (isset($city)) $params['city'] = $city;

        return $this->oauth->post('http://api.t.sina.com.cn/account/activate.json', $params);
    }

    /**
     * 搜索微博用户
     *
     * @access public
     * @param string $q 搜索的关键字。
     * @param int $page 页码，可选。
     * @param int $count 每页返回的微博数。默认返回10条，可选。
     * @param array $extra 附加选项。格式：array('key0'=>'value0', 'key1'=>'value1', ....)。支持的key:<br />
     *  - snick    int    搜索范围是否包含昵称。0为不包含，1为包含。
     *  - sdomain    int    搜索范围包含个性域名。0为不包含，1为包含。
     *  - sintro    int    搜索范围包含简介。0为不包含，1为包含。
     *  - province    int    省份ID，参考省份城市编码表
     *  - city    int    城市ID，参考省份城市编码表
     *  - gender    string    性别 (m 为男，f 为女)
     *  - comorsch    string    公司学校名称。
     *  - sort    int    排序方式，1为按更新时间，2为按粉丝数。
     *  - callback    string    仅JSON方式支持，用于JSONP跨域数据访问。
     *  - base_app    int    是否基于当前应用来获取数据。1表示基于当前应用来获取数据。
     * @return array
     */
    function search_users($q, $page = 1, $count = 10, $extra = array() )
    {
        $params = array();
        $params['q'] = $q;
        if ($page) $params['page'] = $page;
        if ($count) $params['count'] = $count;
        $params = array_merge($params, $extra);

        return $this->oauth->post('http://api.t.sina.com.cn/users/search.json', $params);
    }

    /**
     * 搜索微博(多条件组合)
     *
     * @access public
     * @param string $q 搜索的关键字。
     * @param int $page 页码，可选。
     * @param int $count 每页返回的微博数。默认返回10条，可选。
     * @param array $extra 附加选项。格式：array('key0'=>'value0', 'key1'=>'value1', ....)。支持的key:<br />
     *  - filter_ori    int    过滤器，是否原创 (0为全部，5为原创，4为转发，默认0)
     *  - filter_pic    int    过滤器，是否含图 (0为全部，1为含图，2为不含图)
     *  - fuid    int64    微博作者UID
     *  - province    int    省份ID
     *  - city    int    城市ID
     *  - starttime    int    开始时间(unix时间戳)
     *  - endtime    int    截止时间 (unix时间戳)
     *  - needcount    int    是否返回搜索结果数开关量，可选true/false，值为true时，返回值中，增加搜索结果数量字段。
     *  - base_app    int    可选参数. 是否按照当前应用信息对搜索结果进行过滤，可选True/False或者1/0，值为True/1时，返回值中，仅返回通过该应用发送的微博。
     * @return array
     */
    function search_status($q, $page = 1, $count = 10, $extra = array() )
    {
        $params = array();
        $params['q'] = $q;
        if ($page) $params['page'] = $page;
        if ($count) $params['count'] = $count;
        $params = array_merge($params, $extra);

        return $this->oauth->post('http://api.t.sina.com.cn/statuses/search.json', $params);
    }

    /**
     * 搜索微博文章
     *
     * @access public
     * @param string $q 搜索的关键字。
     * @param int $page 页码，可选。
     * @param int $count 每页返回的微博数。默认返回10条，最大200条，可选。
     * @param array $extra 附加选项。格式：array('key0'=>'value0', 'key1'=>'value1', ....)。支持的key:<br />
     *  - geocode    string    返回指定经纬度附近的信息。经纬度参数格式是“纬度，经度，半径”，半径支持km（公里），m（米），mi（英里）。格式需要URL Encode编码
     * @return array
     */
    function search($q, $page = 1, $count = 10, $extra = array() )
    {
        $params = array();
        $params['q'] = $q;
        if ($page) $params['page'] = $page;
        if ($count) $params['rpp'] = $count;
        $params = array_merge($params, $extra);

        return $this->oauth->post('http://api.t.sina.com.cn/search.json', $params);
    }

    /**
     * 驾车路线查询接口。
     *
     * @access public
     * @param array $params 参数。格式：array('key0'=>'value0', 'key1'=>'value1', ....)。支持的key:<br />
     *  - begin_id    int    起点的id（必须与city1结合使用，会覆盖beginxy）
     *  - beginxy    string    起点的坐标（逗号分隔，若有begin_id以begin_id为准）
     *  - end_id    int    终点的id（必须与city2结合使用，会覆盖endxy）
     *  - endxy    string    终点的坐标（逗号分隔，若有end_id以end_id为准）
     *  - city1    string    起点所在城市代码，默认为0010（北京）
     *  - city2    string    终点所在城市代码，默认为0010（北京）
     *  - t    int    查询类型（0：速度优先，1：费用优先，2：距离优先）
     * @return array
     */
    function location_route( $params )
    {
        return $this->get('http://api.map.sina.com.cn/i/route.php', $params);
    }

    /**
     * 公交路线查询接口。
     *
     * @access public
     * @param array $params 参数。格式：array('key0'=>'value0', 'key1'=>'value1', ....)。支持的key:<br />
     *  - begin_id    int    起点的id（必须与city结合使用，会覆盖beginxy）
     *  - beginxy    string    起点的坐标（逗号分隔，若有begin_id以begin_id为准）
     *  - end_id    int    终点的id（必须与city结合使用，会覆盖endxy）
     *  - endxy    string    终点的坐标（逗号分隔，若有end_id以end_id为准）
     *  - city    string    所在城市代码，默认为0010（北京）
     *  - t    int    查询类型（0：最快捷，1：最经济，2：最少换乘，3：最少步行，4：最舒适）
     * @return array
     */
    function location_bus( $params )
    {
        return $this->get('http://api.map.sina.com.cn/i/bus.php', $params);
    }

    /**
     * 根据ip返回坐标信息的接口。
     *
     * @access public
     * @param mixed $ips 需要获取坐标的ip（多个用半角逗号分隔），或者由一组ip组成的数组。例如："219.145.178.178,60.194.172.177"或array("219.145.178.178","60.194.172.177");
     * @return array
     */
    function ip2xy( $ips )
    {
        $params = array();
        if (is_array($ips) && !empty($ips)) {
            $params['ip'] = join(',', $ips);
        } else {
            $params['ip'] = $ips;
        }

        return $this->get('http://api.map.sina.com.cn/i/ip2xy.php', $params);
    }

    /**
     * 根据地址返回坐标的接口。
     *
     * @access public
     * @param string $addr 需要获取坐标的地址
     * @param string $city 可选，所在城市对应代码
     * @return array
     */
    function addr2xy( $addr , $city = NULL )
    {
        $params = array();
        $params['addr'] = $addr;
        if ($city) $params['city'] = $city;

        return $this->get('http://api.map.sina.com.cn/i/addr2xy.php', $params);
    }

    /**
     * 根据地址返回坐标的接口。
     *
     * @access public
     * @param string $xy 需要获取信息的坐标，以逗号隔开。例如：116.30987,39.98437
     * @return array
     */
    function get_addr( $xy )
    {
        $params = array();
        $params['xy'] = $xy;
        $params['format'] = 'json';

        return $this->get('http://api.map.sina.com.cn/i/get_addr.php', $params);
    }

    private function get($url, $params = array()) {
        if ( !isset($params['format']) || $params['format'] != 'json' ) {
            $params['format'] = 'json';
        }
        if ( !isset($params['source']) || $params['format'] != 'source' ) {
            $params['source'] = $this->oauth->consumer->key;
        }
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        $response = $this->oauth->http($url, 'GET');

        return json_decode($response, true);
    }

}