<?php

class Mysql {

    private static $INSTANCES = array();
    public $rdbh;
    public $wdbh;
    public $W_debug ; //写SQL打印开关
    public $R_debug ; //读SQL打印开关
    private $LOG;      //日志类
    private $conn;

    public function __construct($rconn, $wconn = NULL,$LOG=null) {
        if(get_extension_funcs("mysqli")){
            $this->conn='mysqli';
        }
        // 读连接
        if (is_array($rconn)) {
            $this->R_debug = $rconn['DEBUG'];
            $this->rdbh = self::getConnInst($rconn);
        } else {
            $this->rdbh = $rconn;
        }

        // 读写不分离
        if (!$wconn['DB']) {
            $wconn = $this->rdbh;
        }

        // 写连接
        if (is_array($wconn)) {
            $this->W_debug = $wconn['DEBUG'];
            $this->wdbh = self::getConnInst($wconn);
        } else {
            $this->wdbh = $wconn;
        }
        $this->LOG = $LOG;
            
    }

    /**
     * 获取单例
     * @param string $name 配置名
     * @return Mysql
     */
    public static function getInstance($conf, $name = 'default',$LogOBJ='') {
        if (array_key_exists($name, self::$INSTANCES)) {
            return self::$INSTANCES[$name];
        }

        if (!$conf) {
            return null;
        }

        foreach ($conf as $key => $value) {
            $info = explode(':', $value);
            $rcon[$key] = $info[0];
            $wcon[$key] = count($info) > 1 ? $info[1] : $info[0];
        }

        $inst = new Mysql($rcon, $wcon,$LogOBJ);
        
        self::$INSTANCES[$name] = $inst;
        return $inst;
    }

    /**
     * 获取连接
     * @param array $conf
     * @return resource
     */
    private static function getConnInst($conf) {
        if(get_extension_funcs("mysqli")){
            
            $dbh = mysqli_connect($conf['DB_HOST'], $conf['DB_USER'], $conf['DB_PWD'],$conf['DB_NAME']);
            if ($dbh === false) {
                throw new FWException(mysqli_error(), mysqli_errno());
            }
            mysqli_query($dbh,'SET AUTO_COMMIT=1' );
            mysqli_query($dbh,'SET NAMES UTF8');
            return $dbh;
        }else{
            $dbh = mysql_pconnect($conf['DB_HOST'], $conf['DB_USER'], $conf['DB_PWD']);
            if ($dbh === false) {
                throw new FWException(mysql_error(), mysql_errno());
            }
            if ($conf['DB_NAME'] && mysql_select_db($conf['DB_NAME'], $dbh) != true) {
                throw new FWException(mysql_error(), mysql_errno());
            }
            mysql_query('SET AUTO_COMMIT=1', $dbh);
            mysql_query('SET NAMES UTF8', $dbh);
            return $dbh;
        }

        
    }
    /**
     * 查询SQL
     * 举例: query("SELECT * FROM x WHERE a=? AND b<? AND c IN (?)", array('1', 2, array(3, 4)))
     * @param string $sql 查询的 SQL 语句
     * @param array  $arr 查询的参数
     * @return resource 可用 fetch 迭代获取数据
     */
    public function query($sql, $arr = NULL) {
        if ($arr !== NULL) {
            $sql = $this->setVals($sql, $arr);
        }
        //判断读SQLdebug  打印SQL 
        If($this->R_debug){
            echo '<!-- sql:'.$sql." -->\r\n";
        }
        //判断日志是否开启记录SQL日志
        if($this->LOG){
            $this->LOG->setsqllog($sql);
        }
        if($this->conn=='mysqli'){
            $sth = mysqli_query( $this->rdbh,$sql);
            if ($sth === false && mysqli_errno($this->rdbh)) {
                throw new FWException(mysqli_error($this->rdbh), mysqli_errno($this->rdbh));
            }
        }else{
            $sth = mysql_query($sql, $this->rdbh);
            if ($sth === false && mysql_errno($this->rdbh)) {
                throw new FWException(mysql_error($this->rdbh), mysql_errno($this->rdbh));
            }
        }
        
        return $sth;
    }

    /**
     * 获取全部查询的数据
     * @param string $sql 同 query
     * @param array  $arr 同 query
     * @return array
     */
    public function fetchAll($sql, $arr = NULL) {
        $sth = $this->query($sql, $arr);

        $rows = array();
        if($this->conn=='mysqli'){
            while ($row = mysqli_fetch_assoc($sth)) {
                $rows[] = $row;
            }
            mysqli_free_result($sth);
        }else{
            while ($row = mysql_fetch_assoc($sth)) {
                $rows[] = $row;
            }
            mysql_free_result($sth);
        }
        
        
        return $rows;
    }

    /**
     * 获取一行查询的数据
     * @param stirng $sql 同 query
     * @param array  $arr 同 query
     * @return array
     */
    public function fetchOne($sql, $arr = NULL) {
        if (preg_match('/\s+LIMIT\s+.+?(,.+?)?\s*$/i', $sql)) {
            $sql = preg_replace('/\s+LIMIT\s+.+?(,.+?)?\s*$/i', ' LIMIT 1', $sql, 1);
        } else {
            $sql .= " LIMIT 1";
        }

        $rows = $this->fetchAll($sql, $arr);
        return $rows ? $rows[0] : array();
    }

    /**
     * 获取该查询可返回的总行数
     * @param string $sql 同 query
     * @param array  $arr 同 query
     * @return int
     */
    public function fetchCnt($sql, $arr = NULL) {
        $row = $this->fetchOne('SELECT count(*) AS __cnt__ FROM (' . $sql . ') AS __sql__', $arr);
        return $row['__cnt__'];
    }

    /**
     * 执行SQL
     * 举例: query("UPDATE x SET a=?, b=?, c=? WHERE a=?", array('1', 2, 3, 4))
     * @param string $sql
     * @param array  $arr
     * @return boolean
     */
    public function exec($sql, $arr = NULL) {
        if ($arr !== NULL) {
            $sql = $this->setVals($sql, $arr);
        }
        
        //判断写SQLdebug  打印SQL 
        If($this->W_debug){
            echo '<!-- sql:'.$sql." -->\r\n";
        }
        //判断日志是否开启记录SQL日志
        if($this->LOG){
            $this->LOG->setsqllog($sql);
        }
        if($this->conn=='mysqli'){
            $sth = mysqli_query( $this->wdbh,$sql);
            if ($sth === false && mysqli_errno($this->wdbh)) {
                throw new FWException(mysqli_error($this->wdbh), mysqli_errno($this->wdbh));
            }
        }else{
            $sth = mysql_query($sql, $this->wdbh);
            if ($sth === false && mysql_errno($this->wdbh)) {
                throw new FWException(mysql_error($this->wdbh), mysql_errno($this->wdbh));
            }
        }
        return $sth;
    }

    /**
     * 插入数据
     * @param string $table 待插入的表名
     * @param array  $vals  待插入的数据
     * @reutrn boolean
     */
    public function insert($table, $vals) {
        $sql_vals = $this->makeVals($vals);
        $sql = "INSERT INTO `$table` $sql_vals";

        return $this->exec($sql);
    }

    /**
     * 更新数据
     * @param string $table 待更新的表名
     * @param array  $vals  待更新的数据
     * @param string $where 待更新的条件
     * @param array  $arr   更新条件参数
     * @return boolean
     */
    public function update($table, $vals, $where, $arr = NULL) {
        
        $sql_sets = $this->makeSets($vals);
        if(is_array($where)){
            $new_where = '';
            foreach ($where as $key => $value){
               $new_where .= " $key = $value AND"; 
            }
            $where = trim($new_where,'AND');
        }
        $sql_wher = $where ? 'WHERE ' . ($arr ? $this->setVals($where, $arr) : $where) : '';
        $sql = "UPDATE `$table` $sql_sets $sql_wher";
        return $this->exec($sql);
    }
    /**
     * 更新数据
     * @param string $table 待更新的表名
     * @param array  $vals  待更新的数据
     * @param string $where 待更新的条件
     * @param array  $arr   更新条件参数
     * @return boolean
     */
    public function update_plus($table, $vals, $where, $arr = NULL) {
        
        $sql_sets = '';

        foreach ($vals as $f => $v) {
            $sql_sets .= '`' . $f . '`=' .$f.'+'. $this->quote($v) . ',';
        }
        if ($sql_sets) {
            $sql_sets = 'SET ' . substr($sql_sets, 0, -1);
        } else {
            $sql_sets = '';
        }
        if(is_array($where)){
            $new_where = '';
            foreach ($where as $key => $value){
               $new_where .= " $key = $value AND"; 
            }
            $where = trim($new_where,'AND');
        }
        $sql_wher = $where ? 'WHERE ' . ($arr ? $this->setVals($where, $arr) : $where) : '';
        $sql = "UPDATE `$table` $sql_sets $sql_wher";
echo $sql;
        return $this->exec($sql);
    }

    /**
     * 删除数据
     * @param string $table 待更新的表名
     * @param string $where 待删除的条件
     * @param array  $arr   删除条件参数
     * @return boolean
     */
    
    public function delete($table, $where, $arr = NULL) {
        if(is_array($where)){
            $new_where = '';
            foreach ($where as $key => $value){
               $new_where .= " $key = $value AND"; 
            }
            $where = trim($new_where,'AND');
        }
        $sql_wher = $where ? 'WHERE ' . ($arr ? $this->setVals($where, $arr) : $where) : '';
        $sql = "DELETE FROM `$table` $sql_wher";

        return $this->exec($sql);
    }

    /**
     * 获取最后加的ID
     * @return int
     */
    public function lastInsertId() {
         if($this->conn=='mysqli'){
            return mysqli_insert_id($this->wdbh);
         }else{
             return mysql_insert_id($this->wdbh);
         }
    }

    /**
     * 获取影响的行数
     * @return int
     */
    public function affectedRows() {
        if($this->conn=='mysqli'){
            return mysqli_affected_rows($this->wdbh);
        
        }else{
             return mysql_affected_rows($this->wdbh);
        }
    }

    /** 事务函数 * */
    public function begin() {
        $this->exec('BEGIN');
    }

    public function commit() {
        $this->exec('COMMIT');
    }

    public function rollBack() {
        $this->exec('ROLLBACK');
    }

    /** 工具函数 * */

    /**
     * 转义字符串
     * @param string $str
     * @return string
     */
    private function quote($str) {
        if (is_null($str)) {
            return 'NULL';
        } elseif (is_numeric($str))  {
            return $str;
        } elseif (! is_array($str)) {
             if($this->conn=='mysqli'){
                 return '\''.mysqli_real_escape_string($this->rdbh ? $this->rdbh : $this->wdbh,(string) $str).'\'';
             }else{
                 return '\''.mysql_real_escape_string((string) $str, $this->rdbh ? $this->rdbh : $this->wdbh).'\'';
             }
        } else {
            $a = array_unique($str);
            $str = '';
            foreach ($a as $s) {
                $str .= $this->quote($s) . ',';
            }
            if ($str) {
                $str = substr($str, 0, -1);
            } else {
                $str = '\'\'';
            }
            return $str;
        }
    }

    /**
     * 设置参数到 SQL
     * @param string $sql
     * @param array  $rep
     * @return string
     */
    private function setVals($sql, $rep) {
        $sql = preg_replace("/\s+/", ' ', trim($sql));
        if (!is_array($rep)) {
            $rep = array($rep);
        }

        $pos = 0;
        foreach ($rep as $k => $r) {
            $r = $this->quote($r);
            if (is_int($k)) {
                if (false !== ($pos = strpos($sql, '?', $pos))) {
                    $sql = substr($sql, 0, $pos) . $r . substr($sql, $pos + 1);
                }
            } else {
                $sql = preg_replace("/:{$k}( |$)/", "{$r}\\1", $sql);
            }
        }

        return $sql;
    }

    /**
     * 构建 VALUES 语句
     * @param array $array
     * @return string
     */
    private function makeVals($array) {
        $sql_fields = '';
        $sql_values = '';

        if (array_key_exists(0, $array)) {
            $fields = array_keys($array[0]);
            foreach ($fields as $f) {
                $sql_fields .= "`$f`,";
            }
            if ($sql_fields) {
                $sql_fields = '(' . substr($sql_fields, 0, -1) . ')';
            }

            foreach ($array as $array2) {
                $sql_values2 = '';
                foreach ($fields as $f) {
                    $sql_values2 .= $this->quote($array2[$f]) . ',';
                }
                if ($sql_values2) {
                    $sql_values .= '(' . substr($sql_values2, 0, -1) . '),';
                }
            }
            if ($sql_values) {
                $sql_values = substr($sql_values, 0, -1);
            }
        } else {
            foreach ($array as $f => $v) {
                $sql_fields .= "`$f`,";
                $sql_values .= $this->quote((string) $v) . ',';
            }
            if ($sql_fields && $sql_values) {
                $sql_fields = '(' . substr($sql_fields, 0, -1) . ')';
                $sql_values = '(' . substr($sql_values, 0, -1) . ')';
            }
        }

        if ($sql_fields && $sql_values) {
            return $sql_fields . ' VALUES ' . $sql_values;
        } else {
            return '';
        }
    }

    /**
     * 构建 SET 语句
     * @param array $array
     * @return string
     */
    private function makeSets($array) {
        $sql_sets = '';

        foreach ($array as $f => $v) {
            $sql_sets .= '`' . $f . '`=' . $this->quote($v) . ',';
        }

        if ($sql_sets) {
            return 'SET ' . substr($sql_sets, 0, -1);
        } else {
            return '';
        }
    }

   


}

