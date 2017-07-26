<?php
/**
 * FW 基类：model 
 * 提供model使用的插件功能，被继承后可直接使用 (DB)
 *
 * @category   FW
 * @package  framework
 * @subpackage  lib/model
 * @author    陆春宇
 */
abstract class Model extends FWLibBase{
    private $DB;
    protected $_PACKAGE;

    /**
     * 构造方法， 注册DB
     */
    protected function __construct($REG_PACKAGE=array()) {
        parent::__construct();
        $this->DB = $this->SET_PACKAGE('DB');
        
        if(is_array($REG_PACKAGE)){
            if(!empty($REG_PACKAGE['DB'])){
                unset($REG_PACKAGE['DB']);
            }
            foreach ($REG_PACKAGE as $key => $value) {
                $this->_PACKAGE[$key] = $this->SET_PACKAGE($key);
            }
        }
        
    }

    /** 事务函数 * */
    protected function begin() {
        return $this->DB->begin();
    }

    protected function commit() {
        return $this->DB->commit();
    }

    protected function rollBack() {
        return $this->DB->rollBack();
    }

    protected function exec($sql, $arr = NULL) {
        return $this->DB->exec($sql, $arr);
    }

    protected function query($sql, $arr = NULL) {
        return $this->DB->query($sql, $arr);
    }

    /**
     * 获取影响的行数
     * @return int
     */
    protected function affectedRows() {
        return $this->DB->affectedRows();
    }

    /**
     * 获取最后加的ID
     * @return int
     */
    protected function lastInsertId() {
        return $this->DB->lastInsertId();
    }

    protected function insert($table, $vals) {
        $this->DB->insert($table, $vals);
        return $this->affectedRows();
    }

    protected function delete($table, $where, $arr = NULL) {
        $this->DB->delete($table, $where, $arr);
        return $this->affectedRows();
    }

    protected function update($table, $vals, $where, $arr = NULL) {
        return $this->DB->update($table, $vals, $where, $arr);
    }
    protected function update_plus($table, $vals, $where, $arr = NULL) {
        return $this->DB->update_plus($table, $vals, $where, $arr);
    }

    protected function fetchOne($sql, $arr = NULL) {
        return $this->DB->fetchOne($sql, $arr);
    }

    protected function fetchAll($sql, $arr = NULL) {
        return $this->DB->fetchAll($sql, $arr);
    }
    
    
    /**
     * 获取表的字段
     * @param  string $table_name
     * @return array
     */
    protected function get_table_fields($table_name){
        $sql = " desc " . $table_name;
        $res = $this->query($sql);
        while ($arr = mysql_fetch_assoc($res)) 
        {
            $table_fields[] = $arr['Field'];
        }
        return $table_fields;
    }
    
    /**
     * 过滤掉不存在的字段
     * @param  array $data
     * @return array
     */
    protected function filter_field($data,$table_name)
    {
       foreach($data as $k=>$v)
       {
           if(!in_array($k, $this->get_table_fields($table_name)))
           {
               unset($data[$k]);
           }
       }
       return $data;
    }

	protected function fetchCnt($sql, $arr = NULL) {
        return $this->DB->fetchCnt($sql, $arr);
    }
    
    /**
     * 添加分类详细信息
     * @param array $info  分类信息
     * @return array 分类信息数组
     */
    public function _insert($info){
        $state = $this->insert($this->table, $info);
        if($state){
            return $this->lastInsertId();
        }
    }
    /**
     * 删除某个分类（二期，包含自定义字段）
     *     删除与分类相关的关系（产品，标签，分类等）
     * @param int $id  分类ID
     * @return array 分类信息数组
     */
    public function _delete($condition){
        $condition = $this->format_condition($condition);
        $condition = !empty($condition) ? " WHERE {$condition} " : '';
        $sql = "DELETE FROM `$this->table`";
        $sql .= "{$condition}";
        return $this->exec($sql);
    }
    /**
     * 获取分类详细信息
     * @param int $id  分类ID
     * @param array $info  分类信息
     * @return array 分类信息数组
     */
    public function _update($condition,$info){
        
        $condition = $this->format_condition($condition);
        return $this->update($this->table, $info, $condition);
    }
    /**
     * 获取分类详细信息
     * @param int $id  分类ID
     * @param array $info  分类信息
     * @return array 分类信息数组
     */
    public function _update_plus($condition,$info){
        
        $condition = $this->format_condition($condition);
        return $this->update_plus($this->table, $info, $condition);
    }
    /**
     * 获取分类详细信息
     * @param int $id  分类ID
     * @return array 分类信息数组
     */
    public function _selectOnce($condition){
        $condition = $this->format_condition($condition);
        $condition = !empty($condition) ? " WHERE {$condition} " : '';
        $sql = "SELECT * FROM `$this->table` ";
        $sql .= "{$condition}";
        return $this->fetchOne($sql);
    }
    /**
     * 获取分类列表
     * @param int $start 开始位置
     * @param int $limit 显示条数
     * @param int $state  状态值，0未发布， 1已发布
     * @return array 分类信息数组
     */
    public function _selectList($start=0,$limit=20,$condition='',$order=''){
        $sql = "SELECT * FROM `$this->table` ";
        $condition = $this->format_condition($condition);
        $condition = !empty($condition) ? " WHERE {$condition} " : '';
        
        $sql .=" $condition ";
        $sql .= !empty($order) ? " {$order} " : '';
        $sql .= $limit?"LIMIT $start , $limit":'';
        
        return $this->fetchAll($sql);
    }
    /**
     * 获取分类数量
     * @param int $state  状态值，0未发布， 1已发布
     * @return array 分类信息数组
     */
    public function _count($condition=''){
        $sql = "SELECT COUNT(*) AS num FROM `$this->table` ";
        $condition = $this->format_condition($condition);
        $condition = !empty($condition) ? " WHERE {$condition} " : '';
        $sql .= " {$condition} ";
        return $this->fetchOne($sql);
    }
    /**
     * 根据ID获取分类
     * @param string $ids ID列表  例：1,2,3
     * @param int $start 开始位置
     * @param int $limit 显示条数
     * @param int $state  状态值，0未发布， 1已发布
     * @return array 分类信息数组
     */
    public function _selectById($ids,$start=0,$limit=20,$condition=''){
        $sql = "SELECT * FROM `$this->table` WHERE FIND_IN_SET(id,'$ids') AND id IN ($ids) ";
        $condition = $this->format_condition($condition);
        $sql .=" AND $condition ";
        $sql .= $limit?"LIMIT $start , $limit":'';
        return $this->fetchAll($sql);
    }
    /**
     * 格式化条件
     * @param array|string $data
     */
    public function format_condition($data)
    {
        $condition = '';
        if(is_array($data) && count($data) > 0)
        {
            foreach($data as $key=>$value)
            {
                $condition .= "AND `{$key}`='{$value}' ";
            }

            $condition = ltrim($condition, 'AND');
        }
        elseif(is_array($data) && count($data) == 0)
        {
           
            $condition = '';
        }else{
            $condition=$data;
        }
        return $condition;

        
    }
}
