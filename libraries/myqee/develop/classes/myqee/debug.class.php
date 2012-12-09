<?php

class MyQEE_Debug extends FB
{

    /**
     * @var Debug
     */
    protected static $instance = null;

    public static function instance()
    {
        if ( null === Debug::$instance )
        {
            Debug::$instance = new Debug();
        }
        return Debug::$instance;
    }

    /**
     * @return Profiler
     */
    public function &profiler( $type = 'default' )
    {
        return Profiler::instance( $type );
    }

    /**
     * 开启Xhprof调试信息
     */
    public function xhprof_start( $type = null )
    {
        $profiler = $this->profiler( 'xhprof' );
        if ( true === $profiler->is_open() )
        {
            $xhprof_fun = 'xhprof_enable';
            if ( function_exists( $xhprof_fun ) )
            {
                $xhprof_fun( $type );
            }
            $profiler->start( 'Xhprof', $type === null ? 'default' : 'Type:' . $type );
        }
    }

    /**
     * 停止Xhprof调试信息
     */
    public function xhprof_stop()
    {
        $profiler = $this->profiler( 'xhprof' );
        if ( true === $profiler->is_open() )
        {
            $xhprof_fun = 'xhprof_disable';
            if ( function_exists( $xhprof_fun ) )
            {
                $data = $xhprof_fun();
            }
            else
            {
                $data = null;
            }
            $profiler->stop();
            return $data;
        }
    }

    public function __call( $m, $v )
    {
        return $this;
    }
}