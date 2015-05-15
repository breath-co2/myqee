<?php

/**
 * 列队处理核心类
 *
 *
 *
 * `callback` 参数支持类库方法调用、Http请求、调用命令，具体如下：
 *
 * 类型          |  例子
 * -------------|-----------------------------------
 * 类库方法调用   | `$job = Queue::factory('test', 'myClass::fun', array('a', 'b'));`
 * URL远程调用   | `$job = Queue::factory('test', 'http://localhost/test.php?test=a')`
 * 运行脚本      | `$job = Queue::factory('test', 'node /path/file.js', array('aaa', 'bbb'))`
 *
 * 任务调度说明
 *
 * 类型          |   任务调度
 * -------------|----------------------------------
 * 类库方法调用   | 当方法返回 `false` 时系统会认为执行失败，否则（包括 `null`）会认为成功
 * URL远程调用   | 当返回的http的头信息是200时，系统认为执行成功，否则认为失败
 * 运行脚本      | 当脚本输出内容“最后一行字符串”为 `success` 时(忽略换行符)，系统认为执行成功，其它情况系统都会任务执行其它
 *
 *
 * PHP里创建队列代码例子：
 *
 *      // 创建一个将会调用 `myClass::fun('a' => 'b', $queue)` 方法执行的任务，其中第2个参数 `$queue` 是当前任务的对象
 *      $job = Queue::factory('test', 'myClass::fun', array('a'=>'b'));
 *      $job->push();
 *
 *      // 创建一个将会请求 `http://localhost/test.php?test=a` 页面的任务
 *      Queue::factory('test', 'http://localhost/test.php?test=a')->push();
 *
 *      // 创建一个将会运行 `node /path/file.js aaa bbb` 脚本的任务
 *      Queue::factory('test', 'node /path/file.js', array('aaa', 'bbb'))->push();
 *
 *      // 创建一个30分钟后才会被执行的任务
 *      Queue::factory('test', array('myClass', 'fun'), array('a'), time()+1800)->push();
 *
 *
 *
 * 执行任务请使用 bin/queue 脚本运行
 * -----------------------------------------------------
 *
 * 执行如下命令启动任务：
 *
 *      bin/queue
 *
 * 例如：
 *
 *      # 启用5个子进程执行，每3秒钟刷新一次队列
 *      bin/queue --count=5 --interval=3
 *
 * 其它参数
 *
 *      @option
 *      -q=列队名称
 *      -p=前缀
 *
 *      --debug 是否开启debug
 *      --blocking 是否阻塞
 *      --interval=间隔，单位秒，默认5
 *      --count=worker数量，默认1
 *      --pidfile=pid文件路径
 *      --include=include文件路径
 *
 *      @example
 *      php queue
 *      php queue -q test --debug --count=3
 *
 * 建议使用 supervisor 启动脚本，见 <http://supervisord.org>
 *
 *
 * @author     呼吸二氧化碳 <jonwang@myqee.com>
 * @category   Module
 * @package    Queue
 * @copyright  Copyright (c) 2008-2016 myqee.com
 * @license    http://www.myqee.com/license.html
 */
class Core_Queue
{
    /**
     * 任务数据
     *
     * @var array
     */
    protected $job = array();

    protected $accepting = false;

    /**
     * 数据驱动
     *
     * @var Database
     */
    protected static $driver;

    /**
     * 默认数据库配置
     *
     * @var null
     */
    protected static $driver_config = null;

    /**
     * 列队表名称
     *
     * @var string
     */
    public static $table_name = 'queue';

    /**
     * 存档表名称
     *
     * @var string
     */
    public static $archive_table_name = 'archive';

    /**
     * 状态为等待
     *
     * @var int
     */
    const WAITING = 0;

    /**
     * 状态为重试
     *
     * @var int
     */
    const RETRY = 1;

    /**
     * 状态为执行中
     *
     * @var int
     */
    const RUNNING = 2;

    /**
     * 状态为暂停
     *
     * @var int
     */
    const PAUSE = 3;

    /**
     * 状态为完成
     *
     * @var int
     */
    const COMPLETE = 4;

    /**
     * 状态为失败
     *
     * @var int
     */
    const FAILED = 5;


    /**
     *
     * @param $tag
     * @param $callback
     * @param array $arguments
     * @param int $job_time 设定定时任务
     * @param string $project 所属项目，不指定则为当前项目
     */
    function __construct($tag, $callback, array $arguments = array(), $job_time = 0, $project = null)
    {
        if (is_array($tag) && null === $callback)
        {
            if (is_string($tag['arguments']))
            {
                $tag['arguments'] = unserialize($tag['arguments']);
            }

            $this->job = $tag;
        }
        else if (!$tag || !$callback)
        {
            throw new InvalidArgumentException('need $tag and $callback.');
        }
        else
        {
            if (is_array($callback))
            {
                $callback = implode('::', $callback);
            }

            $m_time = intval(microtime(1) * 1000);
            $this->job = array
            (
                'id'           => null,
                'tag'          => $tag,
                'project'      => $project ? $project : Core::$project,
                'job_time'     => $job_time,
                'create_mtime' => $m_time,
                'update_mtime' => $m_time,
                'status'       => 0,
                'retry_count'  => 0,
                'callback'     => $callback,
                'arguments'    => $arguments,
                'result'       => '',
            );
        }
    }

    /**
     * 获取一个任务实例化对象
     *
     *      // 创建一个将会调用 `myClass::fun('a', 'b')` 方法执行的任务
     *      $job = Queue::factory('test', 'myClass::fun', array('a', 'b'));
     *      $job->push();
     *
     *      // 创建一个将会请求 `http://localhost/test.php?test=a` 页面的任务
     *      Queue::factory('test', 'http://localhost/test.php?test=a')->push();
     *
     *      // 创建一个将会运行 `node /path/file.js aaa bbb` 脚本的任务
     *      Queue::factory('test', 'node /path/file.js', array('aaa', 'bbb'))->push();
     *
     *      // 创建一个30分钟后才会被执行的任务
     *      Queue::factory('test', array('myClass', 'fun'), array('a'), time()+1800)->push();
     *
     * @param string $tag
     * @param string|array $callback
     * @param array $data
     * @param int $job_time
     * @param string $project 所属项目，不指定则为当前项目
     * @return Queue
     */
    public static function factory($tag, $callback, $data = array(), $job_time = 0, $project = null)
    {
        return new Queue($tag, $callback, $data, $job_time, $project);
    }
    
    /**
     * 返回当前队列ID
     *
     * @return int|string
     */
    public function id()
    {
        $this->job['id'];
    }

    /**
     * 将新建的任务推送到列队中
     *
     * 新任务只有执行了此方法才会生效
     *
     * @return bool
     * @throws Exception
     */
    public function push()
    {
        if ($this->id())
        {
            throw new Exception(__('this job is already exists.'));
        }

        # 更新时间
        $this->job['update_mtime'] = $this->job['create_mtime'] = intval(1000 * microtime(1));

        $value = $this->job;

        # 移除=null的ID，以便获取自增ID
        unset($value['id']);

        # 序列化数据
        $value['data'] = serialize($value['data']);

        if ($rs = Queue::driver()->insert(Queue::$table_name, $value))
        {
            $this->job['id'] = $rs[0];

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 移除任务
     *
     * 从队列中直接删除，如果需要存档任务数据，请使用 `$this->archive()` 方法
     *
     * 在执行中的任务不能被删除
     *
     * @return bool
     */
    public function remove()
    {
        if (!$this->id())
        {
            return true;
        }

        $where = array
        (
            'id'       => $this->id(),
            'status!=' => Queue::RUNNING,
        );

        $rs = Queue::driver()->delete(Queue::$table_name, $where);

        if ($rs)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 将任务存档
     *
     * 只能对已经完成或者标记失败的任务进行存档
     *
     * 将移出队列表，存放在archive数据中，如果不需要存档任务数据，请使用 `$this->remove()` 方法
     * 
     * 存档表请设置 `Queue::$archive_table_name` 参数
     * 
     *
     * @return false
     */
    public function archive()
    {
        if (!$this->id())
        {
            return true;
        }

        # 先获取旧数据
        $job = Queue::driver()->where('id', $this->id())->get()->current();

        if (!$job)return false;
        if ($job['status'] != Queue::COMPLETE && $job['status'] != Queue::FAILED)
        {
            # 只存档已经完成或者标记失败的任务
            return false;
        }

        $tr = Queue::driver()->transaction();
        try
        {
            # 开启事务
            $tr->start();

            Queue::driver()
                ->from(Queue::$table_name)
                ->where('id', $this->id())
                ->and_where_open()
                ->where('status', Queue::COMPLETE)
                ->or_where('status', Queue::FAILED)
                ->and_where_close();

            # 先删除
            $rs = Queue::driver()->delete(Queue::$table_name);

            if ($rs)
            {
                $value = array
                (
                    'data_type'    => 'queue',
                    'data_id'      => $job['id'],
                    'archive_time' => time(),
                    'value'        => serialize($job),
                );

                # 数据插入到存档表中
                Queue::driver()->insert(Queue::$archive_table_name, $value);

                # 提交事务
                $tr->commit();

                return true;
            }
            else
            {
                # 回滚事务
                $tr->rollback();

                return false;
            }
        }
        catch (Exception $e)
        {
            $tr->rollback();

            return false;
        }
    }

    /**
     * 将任务设为暂停状态
     * 
     * 只能暂停待执行、待尝试的任务
     *
     * @return bool
     */
    public function pause()
    {
        if (!$this->id())
        {
            return false;
        }

        Queue::driver()
            ->from(Queue::$table_name)
            ->where('id', $this->id())
            ->and_where_open()
            ->where('status', Queue::WAITING)
            ->or_where('status', Queue::RETRY)
            ->and_where_close();

        $value = array
        (
            'status' => Queue::PAUSE,
        );

        $rs = Queue::driver()->update(Queue::$table_name, $value);

        if ($rs)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 执行当前任务
     *
     * @return bool|array
     */
    public function run()
    {
        if ($this->accept())
        {
            // 获取执行

            try
            {
                $callback  = $this->job['callback'];
                $arguments = $this->job['arguments'];

                if (substr($callback, 0, 7) === 'http://' || substr($callback, 0, 7) === 'https://')
                {
                    # url
                    if ($arguments)
                    {
                        $query = http_build_query($arguments, '', '&');

                        if (strpos($callback, '?') !== false)
                        {
                            $url = $callback .'&'. $query;
                        }
                        else
                        {
                            $url = $callback .'?'. $query;
                        }
                    }
                    else
                    {
                        $url = $callback;
                    }

                    $rs = HttpClient::factory()->get($url);

                    if ($rs->code() === 200)
                    {
                        $rs = $rs->data();
                    }
                    else
                    {
                        throw new Exception('queue fail. url:'. $url);
                    }
                }
                elseif (strpos($callback, '::') === false)
                {
                    # exec
                    $callback = escapeshellcmd($callback);

                    if ($arguments)
                    {
                        $p = '';
                        foreach((array)$arguments as $item)
                        {
                            $p .= ' '. escapeshellarg($item);
                        }

                        $callback .= $p;
                    }

                    $rs = trim(shell_exec($callback));

                    $pos = strrpos($rs, "\n");

                    if (false === $pos)
                    {
                        $pos = strrpos($rs, "\r");
                    }

                    if (false === $pos)
                    {
                        $rs2 = $rs;
                    }
                    else
                    {
                        $rs2 = substr($rs, $pos);
                    }

                    if ($rs2 !== 'success')
                    {
                        throw new Exception('queue callback not success. cmd: '. $callback);
                    }
                }
                else
                {
                    $rs = call_user_func($callback, $arguments, $this);

                    if (false === $rs)
                    {
                        throw new Exception('queue callback get false');
                    }
                }

                // 更新状态
                $value = array
                (
                    'status' => Queue::COMPLETE,
                    'result' => (string)$rs,
                );

                $where = array
                (
                    'id'     => $this->id(),
                );

                Queue::driver()->update(Queue::$table_name, $value, $where);

                $this->job['result'] = (string)$rs;
                $this->job['status'] = Queue::COMPLETE;

                return true;
            }
            catch (Exception $e)
            {
                // 更新状态

                $delay = 10;
                $value = array
                (
                    'job_time' => time() + $delay,
                    'status'   => Queue::RETRY,
                );

                $where = array
                (
                    'id' => $this->id(),
                );

                Queue::driver()->value_increment('retry_count', 1)->update(Queue::$table_name, $value, $where);

                throw $e;
            }
        }
        else
        {
            return false;
        }
    }

    /**
     * 获取一个任务的执行权
     *
     * @return bool
     */
    protected function accept()
    {
        if (!$this->id())return false;

        $value = array
        (
            'status'       => Queue::RUNNING,
            'update_mtime' => intval(1000 * microtime(1)),
        );

        $where = array
        (
            'id'       => $this->id(),
            'status!=' => Queue::RUNNING,
        );

        $rs = Queue::driver()->update(Queue::$table_name, $value, $where);

        if ($rs)
        {
            // 更新成功
            $this->job['status'] = Queue::RUNNING;
            $this->job['update_mtime'] = $value['update_mtime'];
            $this->accepting = true;

            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 将任务推迟一会执行
     *
     * @param int $time 推迟时间（当前相对时间，10表示10秒钟后执行）
     */
    public function delay($time = 10)
    {
        if (!$this->id())
        {
            return false;
        }

        Queue::driver()
            ->from(Queue::$table_name)
            ->where('id', $this->id())
            ->and_where_open()
            ->where('status', Queue::WAITING)
            ->or_where('status', Queue::RETRY)
            ->and_where_close();

        $value = array
        (
            'job_time' => time() + $time,
        );

        $rs = Queue::driver()->update(Queue::$table_name, $value);

        if ($rs)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 复制相同参数获得一个新的任务
     *
     * `$auto_push` 默认是 false，复制后需要执行 `$job->push()` 才会推送到列队中，设置 true 则会自动推送到列队中
     *
     * @param bool $auto_push
     * @return Queue
     * @throws Exception
     */
    public function copy($auto_push = false)
    {
        $job = new Queue($this->job['tag'], $this->job['callback'], $this->job['arguments'], $this->job['job_time']);

        if ($auto_push)
        {
            $job->push();
        }

        return $job;
    }

    /**
     * 状态
     *
     * 如果队列没有push过去（没有获取到队列ID）则返回 false
     * `$auto_refresh_status = true` 则立即刷新列队中的处理状态
     *
     * 状态码 | 说明
     * ------|-----------------------
     *  0    | 正常待处理
     *  1    | 等待再次处理，下次处理时间由job_time决定
     *  2    | 处理中
     *  3    | 成功
     *  4    | 失败后放弃处理
     *
     * @var int|false
     */
    public function status($auto_refresh_status = false)
    {
        if (!$this->job)
        {
            return false;
        }
        else
        {
            if ($auto_refresh_status)
            {
                $job = Queue::driver()->from(Queue::$table_name)->where('id', $this->id())->get()->current();
                if ($job)
                {
                    # 更新信息
                    $job['arguments'] = unserialize($job['arguments']);
                    $this->job        = $job;
                }
            }

            return $this->job['status'];
        }
    }

    /**
     * 任务的TAG
     *
     * @return string
     */
    public function tag()
    {
        return $this->job['tag'];
    }

    /**
     * 任务的项目
     *
     * @return string
     */
    public function project()
    {
        return $this->job['project'];
    }

    /**
     * 任务执行定时时间
     * 
     * 当前时间大于次时间列队才会被执行
     *
     * @return int|false
     */
    public function job_time()
    {
        return $this->job['job_time'];
    }

    /**
     * 任务创建时的毫秒数
     *
     * @return int
     */
    public function create_mtime()
    {
        return $this->job['create_mtime'];
    }

    /**
     * 任务最后的毫秒数
     *
     * @return int
     */
    public function update_mtime()
    {
        return $this->job['update_mtime'];
    }

    /**
     * 任务重试次数
     *
     * @return int|false
     */
    public function retry_count()
    {
        return $this->job['retry_count'];
    }

    /**
     * 任务回调的参数
     *
     * @return string
     */
    public function callback()
    {
        return $this->job['callback'];
    }

    /**
     * 任务执行的参数
     *
     * @return array
     */
    public function arguments()
    {
        return $this->job['arguments'];
    }

    /**
     * 任务执行的返回内容
     *
     * @return string
     */
    public function result()
    {
        return $this->job['result'];
    }

    /**
     * 是否获取处理中的对象
     *
     * @return bool
     */
    public function is_accepting()
    {
        return $this->accepting;
    }

    /**
     * 是否已完成
     *
     * 已完成、失败的队列返回 true
     *
     * @return bool
     */
    public function is_done_job()
    {
        switch ($this->job['status'])
        {
            case Queue::COMPLETE:
            case Queue::FAILED:
                return true;

            default:
                return false;
        }
    }

    /**
     * 是否队列中需要处理的任务
     *
     * 等待中、重试中的任务返回 true
     *
     * @return bool
     */
    public function is_todo_job()
    {
        switch ($this->job['status'])
        {
            case Queue::WAITING:
            case Queue::RETRY:
                return true;

            default:
                return false;
        }
    }


    /**
     * 根据ID获取已经任务
     *
     * @param $id
     * @return Queue
     */
    public static function get_by_id($id)
    {
        $data = Queue::driver()->from(Queue::$table_name)->where('id', $id)->get()->current();

        return new Queue($data, null);
    }

    /**
     * 获取需要处理的任务
     *
     * 排序为job_time asc方式排序
     *
     * @param int $limit 获取任务数，默认50，0表示全部
     * @param int $offset 起始位置，默认0
     * @return array
     */
    public static function get_todo_jobs($limit = 50, $offset = 0)
    {
        if ($limit > 0)
        {
            Queue::driver()->limit($limit, $offset);
        }

        $jobs = array();
        foreach(Queue::driver()->from(Queue::$table_name)->where('job_time', time(), '<=')->where('status', 2, '<')->order_by('job_time', 'asc')->get() as $item)
        {
            $jobs[$item['id']] = new Queue($item, null);
        }

        return $jobs;
    }

    /**
     * 获取最新的已经处理结束的任务（包括失败的）
     *
     * 排序为update_mtime desc方式排序
     *
     * @param int $limit 获取任务数，默认50，0表示全部
     * @param int $offset 起始位置，默认0
     * @return array
     */
    public static function get_done_jobs($limit = 50, $offset = 0)
    {
        if ($limit > 0)
        {
            Queue::driver()->limit($limit, $offset);
        }

        $jobs = array();
        foreach(Queue::driver()->from(Queue::$table_name)->in('status', array(Queue::COMPLETE, Queue::FAILED))->order_by('update_mtime', 'desc')->get() as $item)
        {
            $jobs[$item['id']] = new Queue($item, null);
        }

        return $jobs;
    }


    /**
     * 获取最新的已经处理结束的任务（包括失败的）
     *
     * 排序为update_mtime desc方式排序
     *
     *      // 获取暂停的列队
     *      $jobs = $this->get_jobs_by_status(Queue::PAUSE);
     *
     *      // 获取重试或失败的列队
     *      $jobs = $this->get_jobs_by_status(array(Queue::RETRY, Queue::FAILED));
     *
     * @param int|array $status 可以是1个值也可以是个数组
     * @param int $limit 获取任务数，默认50，0表示全部
     * @param int $offset 起始位置，默认0
     * @return array
     */
    public static function get_jobs_by_status($status, $limit = 50, $offset = 0)
    {
        if (is_array($status))
        {
            Queue::driver()->in('status', $status);
        }
        else
        {
            Queue::driver()->where('status', $status);
        }

        if ($limit > 0)
        {
            Queue::driver()->limit($limit, $offset);
        }

        $jobs = array();
        foreach(Queue::driver()->from(Queue::$table_name)->order_by('update_mtime', 'desc')->get() as $item)
        {
            $jobs[$item['id']] = new Queue($item, null);
        }

        return $jobs;
    }


    /**
     * 返回驱动
     *
     * @return Database
     */
    protected static function driver()
    {
        if (!Queue::$driver)
        {
            Queue::$driver = new Database(Queue::$driver_config);
            Queue::$driver->auto_use_master(true);
        }

        return Queue::$driver;
    }

}