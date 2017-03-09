<?php
/**
 * 分页类
 * 可以有编辑输出样式
 * getCompletePageArray()格式化后的分页数据   foreach 处理成HTML即可
 * @author luchunyu<luchunyu@huiyiabc.com>
 * 
 */
class Page {
    private static $INSTANCES = array();
    private $each_count; //每页显示的条目数  
    private $total; //总条目数  
    private $page_now; //当前被选中的页  
    private $page_count; //一共几页
    private $sub_pages; //每次显示的页数  
    private $page_link; //每个分页的链接 
    private $page_link_suffixl;//链接后缀
    private $pageOBJ = array();  //分页结构对象

    public function __construct($totla, $each_count, $page, $sub_pages='', $page_link='',$page_link_suffix='') {
        $this->each_count = intval($each_count);
        $this->total = intval($totla);
        $this->page_now = intval($page) ? intval($page) : 1;
        $this->page_count = ceil($totla / $each_count);
        $this->sub_pages = intval($sub_pages) ? intval($sub_pages) : 5;
        $this->page_link = $page_link ? $page_link : $_SERVER['PHP_SELF'] . "?page=";
        $this->page_link_suffixl=$page_link_suffix;
    }
    public static function getInstance($conf,$name = 'default') {
//        if (array_key_exists($name, self::$INSTANCES)) {
//            return self::$INSTANCES[$name];
//        }
        if (!$conf) {
            return null;
        }
        $inst = new Page($conf['totla'], $conf['each_count'], $conf['page'], $conf['sub_pages'], $conf['page_link'],$conf['page_link_suffix']);
        
//        self::$INSTANCES[$name] = $inst;
        return $inst;
    }

    /**
     * 获取分页相关信息
     * @return array
     */
    public function getparam() {
        return array(
            'start' => ($this->page_count - 1) * $each_count,
            'each_count' => $this->each_count,
            'page' => $this->page,
            'total' => $this->total,
            'page_count' => $this->page_count);
    }

    /**
     * 初始化分页结构
     * @return array
     */
    private function initArray() {
        $local = ceil($this->sub_pages / 2);
        if ($this->page_count <= $this->sub_pages) {
            $left = $this->page_now - 1;
            $right = $this->page_count - $left - 1;
        } elseif (($this->page_count - $this->page_now) >= $local && $this->page_now >= $local) {
            $right = $this->sub_pages - $local;
            $left = $this->sub_pages - $right - 1;
        } elseif (($this->page_count - $this->page_now) < $local && $this->page_now >= $local) {
            $right = $this->page_count - $this->page_now;
            $left = $this->sub_pages - $right - 1;
        } elseif (($this->page_count - $this->page_now) >= $local && $this->page_now < $local) {
            $left = $this->page_now - 1;
            $right = $this->sub_pages - $left - 1;
        }
        $init_array = range($this->page_now - $left, $this->page_now + $right);
        return $init_array;
    }

    /**
     * 将初始化分页格式化为基数数据
     * @param 初始化分页数组 $init_array
     */
    private function getPageArray($init_array) {
        $this->pageOBJ = array();
        foreach ($init_array as $value) {
            $this->pageOBJ[$value] = array('key' => $value, 'url' => $this->page_link . $value . $this->page_link_suffixl, 'page' => $value);
            if ($value == $this->page_now) {
                $this->pageOBJ[$value]['state'] = 1;
            }
        }
        return $this->pageOBJ;
    }

    /**
     * 根据使用情况选择是否需要   首页   上一页    分页   下一页   末页   
     * @param type $page_type  0:默认基数数据    1:上一页，下一页   2:首页，末页   3:首页,上一页,下一页,末页 
     */
    public function getCompletePageArray($page_type = 0) {
        $this->getPageArray($this->initArray());

        if ($page_type == 1 || $page_type == 3) {

            if ($this->page_now > 1) {
                array_unshift($this->pageOBJ, array('key' => 'previous', 'url' => $this->page_link . ($this->page_now - 1)  . $this->page_link_suffixl, 'page' => $this->page_now - 1));
            }

            if ($this->page_count - $this->page_now >= 1) {
                array_push($this->pageOBJ, array('key' => 'next', 'url' => $this->page_link . ($this->page_now + 1) . $this->page_link_suffixl, 'page' => $this->page_now + 1));
            }
        }
        if ($page_type == 2 || $page_type == 3) {
            if ($this->page_now > $local && $this->page_now >1) {
                array_unshift($this->pageOBJ, array('key' => 'start', 'url' => $this->page_link . 1 . $this->page_link_suffixl, 'page' => 1));
            }
            if ($this->page_count - $this->page_now > $local) {
                array_push($this->pageOBJ, array('key' => 'stop', 'url' => $this->page_link . $this->page_count . $this->page_link_suffixl, 'page' => $this->page_count));
            }
        }
        return $this->pageOBJ;
    }

    /**
     * 分页默认样式代码   bootstrap
     * @param type $url
     * @return string
     */
    public function getDefaultHtml($url = '') {
        $this->getCompletePageArray(3);
        $html = '<nav style="text-align:center" ><ul class="pagination pager">';
        foreach ($this->pageOBJ as $key => $value) {
            $state = isset($value['state']) ? ' class="active" ' : '';

            if ($value['key'] == 'start') {
                $html .= "<li $state><a href='{$value['url']}'><span $state aria-hidden='true'>第{$value['page']}页</span></a></li>";
            } elseif ($value['key'] == 'stop') {
                $html .= "<li $state><a href='{$value['url']}'><span $state aria-hidden='true'>共{$value['page']}页</span></a></li>";
            } elseif ($value['key'] == 'previous') {
                $html .= "<li $state><a href='{$value['url']}'><span $state aria-hidden='true'>&laquo;</span></a></li>";
            } elseif ($value['key'] == 'next') {
                $html .= "<li $state><a href='{$value['url']}'><span $state aria-hidden='true'>&raquo;</span></a></li>";
            } else {
                $html .= "<li $state><a href='{$value['url']}'><span $state aria-hidden='true'>{$value['page']}</span></a></li>";
            }
        }
        $html.='</ul></nav>';
        return $html;
    }

    function __destruct() {
        unset($totla);
        unset($each_count);
        unset($page);
        unset($sub_pages);
        unset($page_link);
        unset($init_array);
        unset($html);
    }

}
