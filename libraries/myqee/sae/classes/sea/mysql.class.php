<?php
class sae_mysql
{
    function __construct( $host , $port , $accesskey , $secretkey , $appname , $do_replication )
    {
        $this->host = ltrim($host, 'ms');
        $this->port = $port;
        $this->accesskey = $accesskey;
        $this->secretkey = $secretkey;
        $this->appname =  'app_' . $appname;
        $this->do_replication = $do_replication;
    }

    private function connect( $is_master = true )
    {
        if( $is_master ) $host = 'm' . $this->host;
        else $host = 's' . $this->host;

        if( !$db = mysql_connect( $host . ':' . $this->port , $this->accesskey , $this->secretkey ) )
        {
            die('can\'t connect to mysql ' . $this->host . ':' . $this->port );
        }

        mysql_select_db( $this->appname , $db );

        return $db;
    }

    private function db_any()
    {

    }

    private function db_read()
    {
        if( isset( $this->db_read ) )
        {
            mysql_ping( $this->db_read );
            return $this->db_read;
        }
        else
        {
            if( !$this->do_replication ) return $this->db_write();
            else
            {
                $this->db_read = $this->connect( false );
                return $this->db_read;
            }
        }
    }

    private function db_write()
    {
        if( isset( $this->db_write ) )
        {
            mysql_ping( $this->db_write );
            return $this->db_write;
        }
        else
        {
            $this->db_write = $this->connect( true );
            return $this->db_write;
        }
    }

    public function save_error()
    {
        $GLOBALS['SAE_LAST_ERROR'] = mysql_error();
        $GLOBALS['SAE_LAST_ERRNO'] = mysql_errno();
    }


    public function run_sql( $sql )
    {
        $ret = mysql_query( $sql , $this->db_write() );
        $this->save_error();
        return $ret;
    }

    public function get_data( $sql )
    {
        $GLOBALS['SAE_LAST_SQL'] = $sql;
        $data = Array();
        $i = 0;
        $result = mysql_query( $sql , $this->do_replication ? $this->db_read() : $this->db_write()  );

        $this->save_error();

        while( $Array = mysql_fetch_array($result, MYSQL_ASSOC ) )
        {
            $data[$i++] = $Array;
        }

        /*
        if( mysql_errno() != 0 )
            echo mysql_error() .' ' . $sql;
        */

        mysql_free_result($result);

        if( count( $data ) > 0 )
            return $data;
        else
            return false;
    }

    public function get_line( $sql )
    {
        $data = $this->get_data( $sql );
        return @reset($data);
    }

    public function get_var( $sql )
    {
        $data = $this->get_line( $sql );
        return $data[ @reset(@array_keys( $data )) ];
    }

    public function last_id()
    {
        $result = mysql_query( "SELECT LAST_INSERT_ID()" , $this->db_write() );
        return reset( mysql_fetch_array( $result, MYSQL_ASSOC ) );
    }

    public function close_db()
    {
        if( isset( $this->db_read ) )
            @mysql_close( $this->db_read );

        if( isset( $this->db_write ) )
            @mysql_close( $this->db_write );

    }

    public function escape( $str )
    {
        if( isset($this->db_read)) $db = $this->db_read ;
        elseif( isset($this->db_write) )    $db = $this->write;
        else $db = $this->db_read();

        return mysql_real_escape_string( $str , $db );
    }

    public function errno()
    {
        return     $GLOBALS['SAE_LAST_ERRNO'];
    }

    public function error()
    {
        return $GLOBALS['SAE_LAST_ERROR'];
    }
}
