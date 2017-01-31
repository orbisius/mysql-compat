<?php

/**
 * This library defines the most important functions from the old mysql_* library which was removed in php 5.6+ 
 * The new functions are recreated using mysqli extension
 * 
 * @author Svetoslav (Slavi) Marinov | http://orbisius.com
 */
class app_orbisius_mysql_compat {
    public static $dbh = null;
    
    /**
     * app_orbisius_mysql_compat::convert_type2text();
     * 
     * @staticvar type $types
     * @param type $type_id
     * @return str
     * @see andre at koethur dot de http://php.net/manual/en/mysqli-result.fetch-field-direct.php
     */
    public static function convert_type2text($type_id) {
        static $types;

        if (!isset($types))
        {
            $types = array();
            $constants = get_defined_constants(true);
            foreach ($constants['mysqli'] as $c => $n) {
                if (preg_match('/^MYSQLI_TYPE_(.*)/', $c, $m)) {
                    $types[$n] = $m[1];
                }
            }
        }

        return array_key_exists($type_id, $types)? $types[$type_id] : NULL;
    }

    /**
     * app_orbisius_mysql_compat::convert_flags2text();
     * 
     * @staticvar type $flags
     * @param type $flags_num
     * @return str
     */
    public static function convert_flags2text($flags_num) {
        static $flags;

        if (!isset($flags)) {
            $flags = array();
            $constants = get_defined_constants(true);
            
            foreach ($constants['mysqli'] as $c => $n) {
                if (preg_match('/MYSQLI_(.*)_FLAG$/', $c, $m)) {
                    if (!array_key_exists($n, $flags)) { 
                        $flags[$n] = $m[1];
                    }
                }
            }
        }

        $result = array();
        
        foreach ($flags as $n => $t) {
            if ($flags_num & $n) {
                $result[] = $t;
            }
        }
        
        return implode(' ', $result);
    }
}

/* 
 * This defines functions that are depricated since php 5.6+
 * @author Svetoslav Marinov - orbisius.com
 */
// compat
if ( ! function_exists( 'mysql_connect' ) ) {
    function mysql_connect( $host = null, $user = null, $pass = null, $database = null, $port = null, $socket = null ) {
        static $x = null;
        
        /*array(
            $host => $host,
            $user => $user, 
            $pass => $pass, 
            $database => $database, 
            $port => $port,
            $socket => $socket,
        );*/
        
        if ( ! function_exists( 'mysqli_connect' ) ) {
            trigger_error( "mysqli extension not enabled. "
                    . "Can't do compatibility for old mysql functions without it.", E_USER_ERROR);
        }
        
        $port = empty( $port ) ? 3306 : (int) $port;        
        $sock = mysqli_connect( $host, $user, $pass, $database, $port, $socket );
        
        if ( mysqli_connect_errno() ) {
            printf( "Connect failed: %s\n", mysqli_connect_error() );
            exit();
        }
        
        app_orbisius_mysql_compat::$dbh = $sock;
        return $sock;
    }
}

if ( ! function_exists( 'mysql_pconnect' ) ) {
    function mysql_pconnect( $host = null, $user = null, $pass = null, $database = null, $port = null, $socket = null ) {
        $sock = mysql_connect( $host, $user, $pass, $database, $port, $socket );
        return $sock;
    }
}

if ( ! function_exists( 'mysql_select_db' ) ) {
    function mysql_select_db( $db_name = null, $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $db_selected = false;
        
        if ( $dbh ) {
            $db_selected = mysqli_select_db( $dbh, $db_name );
        }
        
        return $db_selected;
    }
}

if ( ! function_exists( 'mysql_query' ) ) {
    function mysql_query( $query = null, $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = false;
        
        if ( $dbh ) {
            $res = mysqli_query( $dbh, $query );
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysqli_errno' ) ) {
    function mysqli_errno( $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = false;
        
        if ( $dbh ) {
            $res = mysqli_errno( $dbh, $query );
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_error' ) ) {
    function mysql_error( $query = null, $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = false;
        
        if ( $dbh ) {
            $res = mysqli_error( $dbh );
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_num_rows' ) ) {
    function mysql_num_rows( $result = null, $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = 0;
        
        if ( is_object( $result ) ) {
            $res = $result->num_rows;
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_real_escape_string' ) ) {
    function mysql_real_escape_string( $string = null, $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = '';
        
        if ( $dbh ) {
            $res = mysqli_real_escape_string( $dbh, $string );
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_fetch_row' ) ) {
    function mysql_fetch_row( $result = null, $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = '';
        
        if ( $dbh ) {
            $res = $result ? mysqli_fetch_array( $result ) : false;
            
            if ( ! $result ) {
                echo "SQL Error: " . mysql_error($dbh);
            }
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_fetch_array' ) ) {
    function mysql_fetch_array( $result = null, $ret_type = null ) {
        $res = $result ? mysqli_fetch_array( $result ) : false;

        if ( ! $result ) {
            echo "SQL Error: " . mysql_error();
        }

        return $res;
    }
}

if ( ! function_exists( 'mysql_result' ) ) {
    /**
     * 
     * @param type $res
     * @param type $row
     * @param type $field
     * @return array
     * @see tuxedobob http://php.net/manual/en/class.mysqli-result.php
     */
    function mysql_result($res, $row, $field=0) {
        if ( $res ) {
            $res->data_seek($row);
            $datarow = $res->fetch_array();
            return $datarow[$field];
        }
        
        return false;
    } 

    /**
     * 
     * @param type $result
     * @param type $row
     * @param type $field
     * @return boolean
     * @author http://php.net/manual/en/class.mysqli-result.php
     */
    function mysqli_result00($result,$row,$field=0) {
        if ($result===false) return false;
        if ($row>=mysqli_num_rows($result)) return false;
        if (is_string($field) && !(strpos($field,".")===false)) {
            $t_field=explode(".",$field);
            $field=-1;
            $t_fields=mysqli_fetch_fields($result);
            for ($id=0;$id<mysqli_num_fields($result);$id++) {
                if ($t_fields[$id]->table==$t_field[0] && $t_fields[$id]->name==$t_field[1]) {
                    $field=$id;
                    break;
                }
            }
            if ($field==-1) return false;
        }
        mysqli_data_seek($result,$row);
        $line = mysqli_fetch_array($result);
        return isset($line[$field])?$line[$field]:false;
    }
}

if ( ! function_exists( 'mysql_drop_db' ) ) {
    function mysql_drop_db( $db_name = null, $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = '';
        
        if ( $dbh ) {
            $dbh->query("DROP TABLE " . mysql_real_escape_string( $db_name ) );
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_affected_rows' ) ) {
    function mysql_affected_rows( $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = 0;
        
        if ( $dbh ) {
            $res = mysqli_affected_rows( $dbh );
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_list_dbs' ) ) {
    /**
     * 
     * @param type $db_name
     * @param type $link_identifier
     * @return type
     * @see http://stackoverflow.com/questions/4703111/list-all-tables-in-a-database-with-mysqli
     */
    function mysql_list_dbs( $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = 0;
        
        if ( $dbh ) {
            $sql = "SHOW DATABASES";
            $res = mysqli_query( $dbh, $sql );
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_list_tables' ) ) {
    /**
     * 
     * @param type $db_name
     * @param type $link_identifier
     * @return type
     * @see http://stackoverflow.com/questions/4703111/list-all-tables-in-a-database-with-mysqli
     */
    function mysql_list_tables( $db_name = null, $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = 0;
        
        if ( $dbh ) {
            $sql = "SHOW TABLES"; // tables from current db
            
            if ( ! empty( $db_name ) ) {
                $db_name_esc = mysql_real_escape_string( $db_name );
                $sql = "SELECT table_name FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$db_name_esc'";
            }
            
            $tableList = array();
            $res = mysqli_query( $dbh, $sql );
            
            // we return $res because the other piece of code expects a resources that it can iterate over.
            /*while ($cRow = mysqli_fetch_array($res))
            {
              $tableList[] = $cRow[0];
            }
            
            return $tableList;*/
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_free_result' ) ) {
    function mysql_free_result( $result, $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = 0;
        
        if ( $dbh ) {
            $res = mysqli_free_result( $result );
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_fetch_object' ) ) {
    function mysql_fetch_object( $result, $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = null;
        
        if ( $dbh ) {
            $res = mysqli_fetch_object( $result );
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_insert_id' ) ) {
    function mysql_insert_id( $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = null;
        
        if ( $dbh ) {
            $res = mysqli_insert_id( $dbh );
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_num_fields' ) ) {
    function mysql_num_fields( $result ) {
        $res = mysqli_num_fields( $result );
        return $res;
    }
}

if ( ! function_exists( 'mysql_field_name' ) ) {
    function mysql_field_name( $result, $field_offset = 0 ) {
        $res = mysqli_fetch_field_direct( $result, $field_offset );
        return empty( $res->name ) ? '' : $res->name;
    }
}

if ( ! function_exists( 'mysql_fetch_field' ) ) {
    function mysql_fetch_field( $result, $field_offset = 0 ) {
        $res = mysqli_fetch_field_direct( $result, $field_offset );
        return $res;
    }
}

if ( ! function_exists( 'mysql_field_type' ) ) {
    function mysql_field_type( $result, $field_offset = 0 ) {
        $res = mysqli_fetch_field_direct( $result, $field_offset );
        return empty( $res->type ) ? '' : $res->type;
    }
}

if ( ! function_exists( 'mysql_field_flags' ) ) {
    function mysql_field_flags( $result, $field_offset = 0 ) {
        $res = mysqli_fetch_field_direct( $result, $field_offset );
        // mysqli returns int consts
        return empty( $res->flags ) ? array() : app_orbisius_mysql_compat::convert_flags2text( $res->flags );
    }
}

if ( ! function_exists( 'mysql_tablename' ) ) {
    function mysql_tablename( $result, $field_offset = 0 ) {
        $res = mysql_result( $result, $field_offset );
        return $res;
    }
}

if ( ! function_exists( 'mysql_db_query' ) ) {
    /**
     * Select a db and do a query.
     * @param type $database_name
     * @param type $query
     * @param type $link_identifier
     * @return mixed
     */
    function mysql_db_query( $database_name, $query, $link_identifier = null ) {
        mysql_select_db( $database_name, $link_identifier );
        $res = mysql_query( $query, $link_identifier ); 
        return $res;
    }
}

if ( ! function_exists( 'mysql_field_len' ) ) {
    function mysql_field_len( $result, $field_offset = 0 ) {
        $res = mysqli_fetch_field_direct( $result, $field_offset );
        return empty( $res->length ) ? 0 : $res->length;
    }
}

if ( ! function_exists( 'mysql_create_db' ) ) {
    function mysql_create_db( $database_name, $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = null;
        
        if ( $dbh ) {
            $sql = "CREATE DATABASE " . mysql_real_escape_string( $database_name );
            $res = $dbh->query($sql);
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_data_seek' ) ) {
    function mysql_data_seek( $result, $field_offset = null ) {
        $res = mysqli_data_seek( $result, $field_offset );
        return $res;
    }
}

if ( ! function_exists( 'mysql_list_fields' ) ) {
    function mysql_list_fields( $db_name, $table, $link_identifier = null ) {
        $dbh = is_null( $link_identifier )
                ? app_orbisius_mysql_compat::$dbh
                : $link_identifier;
        
        $res = null;
        
        if ( $dbh ) { 
            /*
             * @see https://openclassrooms.com/forum/sujet/equivalent-mysqli-de-mysql-list-fields
            SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = 'tbl_name' [AND table_schema = 'db_name'] [AND column_name LIKE 'wild'] SHOW COLUMNS FROM tbl_name [FROM db_name] [LIKE wild]
            */
            
            $db_name_esc = mysql_real_escape_string( $db_name );
            $table_esc = mysql_real_escape_string( $table );
            
            $sql = "SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT FROM INFORMATION_SCHEMA.COLUMNS"
                    . " WHERE table_name = '$table_esc' AND table_schema = '$db_name_esc'";
            
            $res = mysqli_query( $dbh, $sql );
        }
        
        return $res;
    }
}

if ( ! function_exists( 'mysql_field_table' ) ) {
    function mysql_field_table( $result, $field_offset = 0 ) {
        $res = mysqli_fetch_field_direct( $result, $field_offset );
        return empty( $res->table ) ? '' : $res->table;
    }
}

if ( ! function_exists( 'mysql_close' ) ) {
    function mysql_close( $link_identifier ) {
        $dbh = is_null( $link_identifier )
            ? app_orbisius_mysql_compat::$dbh
            : $link_identifier;
        mysqli_close($dbh);
    }
}
