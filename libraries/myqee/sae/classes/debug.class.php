<?php
class Debug extends Ex_Debug
{

    /**
     * 开启Xhprof调试信息
     */
    public function xhprof_start($type = null)
    {
        $profiler = $this->profiler('xhprof');
        if (true===$profiler->is_open())
        {
            $xhprof_fun = 'sae_xhprof_start';
            if (function_exists($xhprof_fun))
            {
                $xhprof_fun($type);
            }
            $profiler->start('Xhprof', $type === null ? 'default' : 'Type:' . $type);
        }
    }

    /**
     * 停止Xhprof调试信息
     */
    public function xhprof_stop()
    {
        $profiler = $this->profiler('xhprof');
        if (true===$profiler->is_open())
        {
            $xhprof_fun = 'sae_xhprof_end';
            if (function_exists($xhprof_fun))
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
}