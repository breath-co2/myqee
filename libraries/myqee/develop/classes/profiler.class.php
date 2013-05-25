<?php

/**
 * 系统运行信息
 *
 * @author   呼吸二氧化碳(jonwang@queyang.com)
 * @category Debug
 * @package    System
 * @subpackage Core
 */
class Library_MyQEE_Develop_Profiler
{

    protected $type;

    protected $token;

    protected static $instances = array();

    protected static $open_type = array();

    /**
     * 获取实例化对象
     * @return Profiler
     */
    public static function &instance( $type = 'default' )
    {
        if ( ! isset( Profiler::$instances[$type] ) )
        {
            Profiler::$instances[$type] = new Profiler( $type );
        }
        return Profiler::$instances[$type];
    }

    function __construct( $type = 'default' )
    {
        $this->type = $type;
    }

    /**
     * @var  integer   maximium number of application stats to keep
     */
    public static $rollover = 1000;

    // Collected benchmarks
    protected static $_marks = array();

    /**
     * Starts a new benchmark and returns a unique token.
     *
     * @param   string  group name
     * @param   string  benchmark name
     * @return  Profiler
     */
    public function start( $group, $name )
    {
        if ( ! $this->is_open() ) return $this;
        static $counter = 0;

        // Create a unique token based on the counter
        $token = 'kp/' . base_convert( $counter ++, 10, 32 );

        Profiler::$_marks[$token] = array( 'group' => $group, 'name' => ( string ) $name,

        // Start the benchmark
        'start_time' => microtime( TRUE ), 'start_memory' => memory_get_usage(),

        // Set the stop keys without values
        'stop_time' => FALSE, 'stop_memory' => FALSE );

        $this->token = $token;

        return $this;
    }

    /**
     * Stops a benchmark.
     *
     * @param   string  token
     * @return  Profiler
     */
    public function stop( $data = null )
    {
        if ( ! $this->is_open() ) return $this;
        // Stop the benchmark
        Profiler::$_marks[$this->token]['stop_time'] = microtime( TRUE );
        Profiler::$_marks[$this->token]['stop_memory'] = memory_get_usage();
        if ( $data !== null )
        {
            if ( IS_CLI )
            {
                $mydata = self::total( $this->token );
                $maxlen = 10;
                $strlen = $maxlen + 70;

                echo "\x1b[1;32;44m";
                echo "\n" . str_pad( Profiler::$_marks[$this->token]['group'] . ' - ' . Profiler::$_marks[$this->token]['name'], $strlen, '-', STR_PAD_BOTH );
                $str = "\x1b[36m";
                $str .= "\nTime:\x1b[33m";
                $str .= number_format( $mydata[0], 6 ) . "s	";

                $str .= "\x1b[36mMemory:\x1b[33m";
                $str .= number_format( $mydata[1] / 1024, 4 ) . 'kb';

                echo str_pad( $str, $strlen + 21, ' ' );
                echo "\n" . str_pad( '', $strlen, ' ' );
                echo "\x1b[0;36m";
                echo "\n\$data=";
                print_r( $data );
                echo "\n\n\n";
                echo "\x1b[0m";
            }
            else
            {
                if ( ! is_array( $data ) || ! isset( $data[0] ) )
                {
                    $data = array( $data );
                }
                Profiler::$_marks[$this->token]['data'] = array( 'runtime' => 0, 'memory' => 0, 'rows' => $data );
            }
        }
    }

    /**
     * Deletes a benchmark.
     *
     * @param   string  token
     * @return  void
     */
    public function delete()
    {
        if ( $this->token )
        {
            // Remove the benchmark
            unset( Profiler::$_marks[$this->token] );
        }
    }

    /**
     * 获取随机码
     */
    public function get_token()
    {
        return $this->token;
    }

    /**
     * 初始化系统时间，只运行一次
     */
    public static function setup()
    {
        static $run = null;
        if ( $run !== null ) return false;
        if ( isset( $_REQUEST['debug'] ) )
        {
            if ( $_REQUEST['debug'] == 'yes' )
            {
                $type = array( 'default' => true );
            }
            else
            {
                $mytype = explode( '|', $_REQUEST['debug'] );
                $type = array( 'default' => true );
                foreach ( $mytype as $item )
                {
                    $type[$item] = true;
                }
            }
            Profiler::$open_type = $type;
        }
        $run = true;
        $profiler = Profiler::instance()->start( 'Core', 'System SetUp' );
        Profiler::$_marks[$profiler->get_token()] = array( 'group' => 'Core', 'name' => 'System SetUp',

        // Start the benchmark
        'start_time' => START_TIME, 'start_memory' => START_MEMORY,

        // Set the stop keys without values
        'stop_time' => FALSE, 'stop_memory' => FALSE );
        $profiler->stop();
    }

    public function is_open()
    {
        if ( isset( Profiler::$open_type[$this->type] ) )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns all the benchmark tokens by group and name as an array.
     *
     * @return  array
     */
    public static function groups()
    {
        $groups = array();

        foreach ( Profiler::$_marks as $token => $mark )
        {
            // Sort the tokens by the group and name
            $groups[$mark['group']][$mark['name']][] = $token;
        }

        return $groups;
    }

    /**
     * Gets the min, max, average and total of a set of tokens as an array.
     *
     * @param   array  profiler tokens
     * @return  array  min, max, average, total
     */
    public static function stats( array $tokens )
    {
        // Min and max are unknown by default
        $min = $max = array( 'time' => NULL, 'memory' => NULL );

        // Total values are always integers
        $total = array( 'time' => 0, 'memory' => 0, 'data' => array() );

        foreach ( $tokens as $token )
        {
            // Get the total time and memory for this benchmark
            list ( $time, $memory, $data ) = Profiler::total( $token );

            if ( $max['time'] === NULL or $time > $max['time'] )
            {
                // Set the maximum time
                $max['time'] = $time;
            }

            if ( $min['time'] === NULL or $time < $min['time'] )
            {
                // Set the minimum time
                $min['time'] = $time;
            }

            // Increase the total time
            $total['time'] += $time;

            if ( $max['memory'] === NULL or $memory > $max['memory'] )
            {
                // Set the maximum memory
                $max['memory'] = $memory;
            }

            if ( $min['memory'] === NULL or $memory < $min['memory'] )
            {
                // Set the minimum memory
                $min['memory'] = $memory;
            }

            // Increase the total memory
            $total['memory'] += $memory;

            if ( $data ) $total['data'][] = $data;
        }

        // Determine the number of tokens
        $count = count( $tokens );

        // Determine the averages
        $average = array( 'time' => $total['time'] / $count, 'memory' => $total['memory'] / $count );

        return array( 'min' => $min, 'max' => $max, 'total' => $total, 'average' => $average );
    }

    /**
     * Gets the total execution time and memory usage of a benchmark as a list.
     *
     * @param   string  token
     * @return  array   execution time, memory
     */
    public static function total( $token )
    {
        // Import the benchmark data
        $mark = Profiler::$_marks[$token];

        if ( $mark['stop_time'] === FALSE )
        {
            // The benchmark has not been stopped yet
            $mark['stop_time'] = microtime( TRUE );
            $mark['stop_memory'] = memory_get_usage();
        }
        $memory = $mark['stop_memory'] - $mark['start_memory'];
        $runtime = $mark['stop_time'] - $mark['start_time'];
        if ( isset( $mark['data'] ) && is_array( $mark['data'] ) )
        {
            $mark['data']['memory'] = $memory;
            $mark['data']['runtime'] = $runtime;
        }
        else
        {
            $mark['data'] = null;
        }

        return array( // Total time in seconds
$runtime,

        // Amount of memory in bytes
        $memory,

        $mark['data'] );
    }

    /**
     * Gets the total application run time and memory usage.
     *
     * @return  array  execution time, memory
     */
    public static function application()
    {
        // Load the stats from cache, which is valid for 1 day
        $stats = null; //VeryCD::cache('profiler_application_stats', NULL, 3600 * 24);


        if ( ! is_array( $stats ) or $stats['count'] > Profiler::$rollover )
        {
            // Initialize the stats array
            $stats = array( 'min' => array( 'time' => NULL, 'memory' => NULL ), 'max' => array( 'time' => NULL, 'memory' => NULL ), 'total' => array( 'time' => NULL, 'memory' => NULL ), 'count' => 0 );
        }

        // Get the application run time
        $time = microtime( TRUE ) - START_TIME;

        // Get the total memory usage
        $memory = memory_get_usage() - START_MEMORY;

        // Calculate max time
        if ( $stats['max']['time'] === NULL or $time > $stats['max']['time'] ) $stats['max']['time'] = $time;

        // Calculate min time
        if ( $stats['min']['time'] === NULL or $time < $stats['min']['time'] ) $stats['min']['time'] = $time;

        // Add to total time
        $stats['total']['time'] += $time;

        // Calculate max memory
        if ( $stats['max']['memory'] === NULL or $memory > $stats['max']['memory'] ) $stats['max']['memory'] = $memory;

        // Calculate min memory
        if ( $stats['min']['memory'] === NULL or $memory < $stats['min']['memory'] ) $stats['min']['memory'] = $memory;

        // Add to total memory
        $stats['total']['memory'] += $memory;

        // Another mark has been added to the stats
        $stats['count'] ++;

        // Determine the averages
        $stats['average'] = array( 'time' => $stats['total']['time'] / $stats['count'], 'memory' => $stats['total']['memory'] / $stats['count'] );

        // Cache the new stats
        //Kohana::cache('profiler_application_stats', $stats);


        // Set the current application execution time and memory
        // Do NOT cache these, they are specific to the current request only
        $stats['current']['time'] = $time;
        $stats['current']['memory'] = $memory;

        // Return the total application run time and memory usage
        return $stats;
    }

    public static function bytes( $a )
    {
        $unim = array( "B", "KB", "MB", "GB", "TB", "PB" );
        $c = 0;
        while ( $a >= 1024 )
        {
            $c ++;
            $a = $a / 1024;
        }
        return number_format( $a, ($c ? 1 : 0), ".", "." ) . " " . $unim[$c];
    }

} // End profiler
