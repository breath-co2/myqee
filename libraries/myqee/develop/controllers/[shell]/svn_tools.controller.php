<?php

/**
 * SVN 工具
 *
 * @author jonwang
 *
 */
class Controller_Svn_Tools extends Controller_Shell
{

//    const SVN_LOG_REVISION_PATTERN = '#^r(\d+)\s+\|\s+(\w+)\s+\|#';

    /**
     * 合并日志格式
     *
     * @var string
     */
    protected static $SVN_LOG_TOP_MERGED_PATTERN = '#^merge\s+working\((\d+)\)\s*->\s*trunk$#';

    /**
     * 已全部合并日志格式
     *
     * @var string
     */
    protected static $SVN_LOG_MERGED_PATTERN = '#^merge\s+working:(\d+):(\d+)\s*->\s*trunk$#';

    /**
     * SVN 日志缓存目录
     *
     * @var string
     */
    protected static $SVN_LOG_CACHEFILE;

    /**
     * SVN 合并TRUNK路径，支持本地目录
     *
     * @var string
     */
    protected static $SVNMERGE_TRUNK_DIR;

    /**
     * SVN 工作路径，支持本地目录
     *
     * @var string
     */
    protected static $SVNMERGE_WORKING_URL;

    /**
     * 查看合并情况，并显示未合并版本
     *
     * * Top Merge:
     * 	Pattern: merge working($a) -> trunk
     * 	Desc: $a and revisions under $a from working all merged into trunk.
     * * Common Merge:
     * 	Pattern: merge working:$b:$c -> trunk
     * 	Desc: Revisions from $b to $c from working all merged into trunk.
     *
     */
    public function action_analyze_merge_log()
    {
        self::$SVN_LOG_CACHEFILE = $_SERVER['HOME'] . '/.svnlogcache.dat';

        // config
        $config = Core::config('shell/svn_tools');

        /*
         * TODO 暂不支持
         *
        if ( isset($config['svn_log_top_merged_pattern']) && $config['svn_log_top_merged_pattern'] )
        {
            self::$SVN_LOG_TOP_MERGED_PATTERN = $config['svn_log_top_merged_pattern'];
        }

        if ( isset($config['svn_log_merged_pattern']) && $config['svn_log_merged_pattern'] )
        {
            self::$SVN_LOG_TOP_MERGED_PATTERN = $config['svn_log_merged_pattern'];
        }
        */

        // parse arguments
        $opts = self::getopt('vhp:', array('help', 'verbose', 'path:', 'trunk-url:', 'working-url:'));

        if ( is_array($opts) ) foreach ( $opts as $opt => $val )
        {
            switch ( $opt )
            {
                case 'verbose' :
                case 'v' :
                    $config['verbose'] = true;
                    break;
                case 'path' :
                case 'p' :
                    if ( $val[0] != '/' )
                    {
                        $val = '/' . $val;
                    }
                    $config['path'] = $val;
                    break;
                case 'trunk-url' :
                    $config['trunk-url'] = $val;
                    break;
                case 'working-url' :
                    $config['working-url'] = $val;
                    break;
                case 'help' :
                case 'h' :
                    self::usage();
                    break;
                default :
                    printf("Unknown option: %s.\n", $opt);
                    break;
            }
        }

        // check
        if ( ! isset($config['trunk-url']) )
        {
            self::usage('Please specify trunk url with --trunk-url=<URL> option.');
        }
        if ( ! isset($config['working-url']) )
        {
            self::usage('Please specify trunk url with --trunk-url=<URL> option.');
        }

        // run
        while ( true )
        {
            system('clear');

            $working_revisions = array();
            $working_logs = array();
            $merge_logs = array();
            $merge_top_revisions = array();

            // get all working revisions
            $working_logs = self::fetch_logs($config['working-url'] . $config['path']);
            $working_revisions = array_keys($working_logs);
            sort($working_revisions, SORT_NUMERIC);
            if ( empty($working_revisions) )
            {
                printf("no logs for %s\n", $config['path']);
                die();
            }

            /*
             * get merge logs of trunk
             *
             * @desc
             * 	 each merge log has two revisions: 'from' and 'to'
             */
            $trunk_logs = self::fetch_logs($config['trunk-url']);
            if ( is_array($trunk_logs) ) foreach ( $trunk_logs as $trunk_log )
            {
                $msg = $trunk_log['msg'];
                foreach ( explode("\n", $msg) as $line )
                {
                    if ( preg_match(self::$SVN_LOG_MERGED_PATTERN, $line, $matches) )
                    {

                        $merge_logs[] = array('from' => (int)$matches[1], 'to' => (int)$matches[2]);
                    }
                    else if ( preg_match(self::$SVN_LOG_TOP_MERGED_PATTERN, $line, $matches) )
                    {
                        $merge_top_revisions[] = (int)$matches[1];
                    }
                    else
                    {
                        $line . "\n";
                    }
                }
            }

            self::analyzer($working_revisions, $merge_logs, $merge_top_revisions, $working_logs);

            if ( ! posix_isatty(STDOUT) )
            {
                break;
            }
            else
            {
                printf("\n");
                printf("Press any key to refresh (CTRL-D to exit):\n");
                $c = fgetc(STDIN);
                if ( $c === false )
                {
                    break;
                }
                else
                {
                    continue;
                }
            }
        }
    }

    protected static function analyzer($working_revisions, $merge_logs, $merge_top_revisions, $working_logs)
    {
        global $config;

        // get top merged revision (0 is initial revision)
        $top_merged_revision = count($merge_top_revisions) ? max($merge_top_revisions) : 1;

        /**
         * Collates merge logs
         *
         * @desc
         * collate all logs before $top_merged_revision into one log that is "0:$top_merged_revision"
         * @see
         * A -> B is merged means that:
         * if  (A < B)
         * A+1, A+2, A+3, ... , B-2, B-1, B revisions are merged.
         * else if (A > B)
         * A, A-1,  A-2, ..., B+2, B+1 revisions are unmerged.
         * else
         * nothing is done.
         * fi
         */
        foreach ( $merge_logs as $key => $merge_log )
        {
            if ( $merge_log['from'] < $top_merged_revision || $merge_log['to'] < $top_merged_revision ) unset($merge_logs[$key]);
        }
        $merge_logs[] = array('from' => 0, 'to' => $top_merged_revision);

        /**
         * Calculate merged revision
         */
        $merged_revisions = array();
        foreach ( $merge_logs as $merge_log )
        {
            $from = $merge_log['from'];
            $to = $merge_log['to'];
            if ( $from < $to )
            {
                $merged_revisions = array_merge($merged_revisions, range($from + 1, $to));
            }
            else if ( $from > $to )
            {
                $merged_revisions = array_diff($merged_revisions, range($to + 1, $from));
            }
            else
            {
                // do nothing
                continue;
            }
        }
        # sort
        sort($merged_revisions, SORT_NUMERIC);

        // output report
        $min_revision = min($working_revisions);
        $max_revision = max($working_revisions);
        printf("### Merge Report For %s ###\n", $config['path']);
        printf("Min working revision: %d \n", $min_revision);
        printf("Max working revision: %d \n", $max_revision);
        printf("Top-Merged revision:%d \n", $top_merged_revision);

        // loop
        for( $i = $min_revision; $i <= $max_revision; $i ++ )
        {
            // skip non-working revisions
            if ( ! self::my_in_array($i, $working_revisions) ) continue;

            $from = $to = $i;

            // check if current revision is merged or not
            if ( ! self::my_in_array($i, $merged_revisions, true) )
            {
                // if not merged, then the backward first merged is [from]
                do
                {
                    $from --;
                    if ( self::my_in_array($from, $merged_revisions, true) && $from >= $min_revision )
                    {
                        break;
                    }
                }
                while ( true );

                // and the forward last not merged revision is [to]
                while ( true )
                {
                    if ( ! self::my_in_array($to + 1, $merged_revisions, true) && ($to + 1) <= $max_revision )
                    {
                        $to ++;
                    }
                    else
                    {
                        break;
                    }
                }
                printf("  %d:%d not merged\n", $from, $to);
                if ( $config['verbose'] )
                {
                    foreach ( range($from + 1, $to) as $non_merged )
                    {
                        if ( isset($working_logs[$non_merged]) )
                        {
                            printf("    %d  %s\n", $non_merged, $working_logs[$non_merged]['author']);
                        }
                    }
                }
            }
            else
            {
                // merged
            }

            $i = $to;
        }
    }

    /**
     * fetch logs from svn path (url, etc)
     *
     * @param string svn path
     * @return array logs, sort by revision asc
     */
    protected static function fetch_logs($path)
    {
        static $logs = array(); /* path => logs */

        // init from cache
        if ( empty($logs) && file_exists(self::$SVN_LOG_CACHEFILE) )
        {
            $logs = @unserialize(file_get_contents(self::$SVN_LOG_CACHEFILE));
        }

        // read diff
        $args = array();
        $args[] = $path;
        if ( ! empty($logs[$path]) )
        {
            $last_revision = max(array_keys($logs[$path]));
            $args[] = '-r';
            $args[] = sprintf('%d:HEAD', $last_revision);
        }
        $args[] = '--xml';
        $cmd = 'svn log ' . implode(' ', array_map('escapeshellarg', $args));

        $descriptorspec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'));
        $process = proc_open($cmd, $descriptorspec, $pipes);
        if ( ! is_resource($process) )
        {
            die('failed to create process');
        }
        $xmldata = stream_get_contents($pipes[1]);
        proc_close($process);

        $objDOM = new DOMDocument();
        $objDOM->loadXML($xmldata);
        $objDOMXPath = new DOMXPath($objDOM);
        $expr = "/log/logentry";
        $nodeList = $objDOMXPath->query($expr);
        for( $i = 0; $i < $nodeList->length; $i ++ )
        {
            $node = $nodeList->item($i);
            $logs[$path][(int)$node->getAttribute('revision')] = array('msg' => $node->getElementsByTagName('msg')->item(0)->textContent, 'author' => $node->getElementsByTagName('author')->item(0)->textContent);
        }

        ksort($logs[$path]);

        // update cache
        file_put_contents(self::$SVN_LOG_CACHEFILE, serialize($logs));

        return $logs[$path];
    }

    /**
     * Checks if a value exists in ascending sorted array using binary search
     *
     * @param mixed needle to search
     * @param array array in asc sort (from lowest to highest)
     * @param boolean using strict comparions with or not
     * @return boolean true if needle is found in array, false otherwise
     */
    protected static function my_in_array($needle, $haystack, $strict = false)
    {
        $top = count($haystack) - 1;
        $btm = 0;

        while ( $top >= $btm )
        {
            $p = floor(($top + $btm) / 2);
            if ( $haystack[$p] < $needle )
            {
                $btm = $p + 1;
            }
            else if ( $haystack[$p] > $needle )
            {
                $top = $p - 1;
            }
            else
            {
                if ( $strict )
                {
                    return $needle === $haystack[$p];
                }
                else
                {
                    return $needle == $haystack[$p];
                }
            }
        }

        return false;
    }

    protected static function usage($msg = null)
    {
        if ( isset($msg) )
        {
            printf("%s\n\n", $msg);
        }
        printf("Usage: %s [OPTIONS]

Avaliable options:
    -v, --verbose       	verbose mode
    -h, --help          	show this help info
    -p, --path PATH     	show merge info of PATH only
    	--trunk-url=<URL>
    	--working-url=<URL>

Examples:
    %s --trunk-url=<TRUNK-URL>  --working-url=<WORKING-URL> -v
    %s --trunk-url=<TRUNK-URL>  --working-url=<WORKING-URL> -v -p config.php
", basename(__FILE__), basename(__FILE__), basename(__FILE__));
        exit();
    }




    /**
     * 合并版本
     * Usage:-c REV [-c REV] -m MSG [--no-comment] [--dry-run]
     * -c 版本 -m 提交内容 --no-comment不自动提交 --dry-run 只输出命令
     *
     */
    public function action_merge()
    {
        // config
        $config = Core::config('shell/svn_tools');

        self::$SVNMERGE_TRUNK_DIR = $config['trunk-dir'];
        self::$SVNMERGE_WORKING_URL = $config['working-url'];

        if ( !is_dir(self::$SVNMERGE_TRUNK_DIR) )
        {
            echo 'trunk-dir error';
            exit;
        }

        $shortopts = "c:m:";
        $longopts = array(
        	'dry-run',
        	'no-comment',
        );
        $opts = self::getopt($shortopts, $longopts);

        if ( ! $opts )
        {
            echo "Usage:-c REV [-c REV] -m MSG [--no-comment] [--dry-run]\n";
        }

        $revisions = array();
        $message = '';
        $GLOBALS['dry-run'] = false;
        foreach ( $opts as $opt => $val )
        {
            switch ( $opt )
            {
                case 'c' :
                    # all to array
                    if ( is_string($val) )
                    {
                        $val = array($val);
                    }
                    $revisions = array_map(array($this,'revision_converter'), $val);
                    break;
                case 'm' :
                    $message = $val;
                    break;
                case 'dry-run' :
                    $GLOBALS['dry-run'] = true;
                    break;
                case 'no-comment' :
                    break;
                default :
                    printf("Unknown option: %s.\n", $opt);
                    break;
            }
        }

        if ( empty($revisions) )
        {
            printf("Usage: %s -c REV [-c REV] -m MSG [--dry-run]\n", basename(__FILE__));
            exit(0);
        }

        chdir(self::$SVNMERGE_TRUNK_DIR);

        # collate
        sort($revisions);

        # up first
        self::do_a_up();

        # merge one by one
        foreach ( $revisions as $revision )
        {
            self::do_a_merge($revision,$message);
        }

        if ( !isset($opts['no-comment']) )
        {
            # commit
            self::do_a_commit($revisions, $message);
        }

    }

    # process
    protected static function revision_converter($val)
    {
        if ( substr($val, 0, 1) === 'r' )
        {
            # rxxxx format
            $revision = intval(substr($val, 1));
        }
        else
        {
            $revision = intval($val);
        }
        return $revision;
    }

    protected static function do_a_up()
    {
        // update
        $cmd = 'svn up';
        self::exec_cmd($cmd);
    }

    protected static function do_a_merge($revision, &$message )
    {
        if ( ! is_int($revision) )
        {
            throw new Exception();
        }

        # merge
        $cmd_tpl = 'svn merge \'%s\' --ignore-ancestry -c %d';
        $cmd = sprintf($cmd_tpl, self::$SVNMERGE_WORKING_URL, $revision);

        self::exec_cmd($cmd);


        # 获取原始版本log
        $cmd_tpl = 'svn log \'%s\' -c %d';
        $cmd = sprintf($cmd_tpl, self::$SVNMERGE_WORKING_URL, $revision);

        exec($cmd, $logs);
        if ( $logs && count($logs)>=4 )
        {
            $message .= "\n".'revision:'.$revision.' >>>>>>'."\n".'    ';
            for( $i=3;$i<=count($logs)-2;$i++ )
            {
                $message .= $logs[$i] . '。';
            }
        }
    }

    protected static function do_a_commit($revisions, $message)
    {
        $message .= "\n";
        foreach ( $revisions as $revision )
        {
            $message .= sprintf("merge working:%d:%d -> trunk\n", $revision - 1, $revision);
        }
        $cmd_tpl = 'svn ci -m %s';
        $cmd = sprintf($cmd_tpl, var_export(trim($message),true));

        self::exec_cmd($cmd);
    }

    protected static function exec_cmd($cmd)
    {
        if ( $GLOBALS['dry-run'] )
        {
            printf("Cmd: %s\n", $cmd);
        }
        else
        {
            system($cmd, $exit_code);
            if ( $exit_code !== 0 )
            {
                throw new Exception();
            }
        }
    }
}