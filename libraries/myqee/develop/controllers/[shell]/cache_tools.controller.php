<?php
/**
 * 缓存工具
 *
 * @author jonwang
 *
 */
class Controller_Cache_Tools extends Controller_Shell
{
    /**
     * 清除过期的缓存
     *
     * @param string 缓存配置名
     */
    public function action_delete_expired( $config = 'default' )
    {
        Cache::instance( $config )->delete_expired();

        echo 'delete '.$config.' all expired cache finished.'.CRLF;
    }

    /**
     * 删除所有配置下过期缓存
     */
    public function action_delete_all_expired()
    {
        $cache_config = Core::config('cache');
        if ($cache_config)foreach ( $cache_config as $k=>$v )
        {
            $this->action_delete_expired( $k );
        }

        echo 'all finished.'.CRLF;
    }

}