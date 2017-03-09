<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File: function.huiyibang_jianli.php
 * Type: function
 * Name: huiyibang_jianli
 * Purpose: outputs a random magic answer
 * Author:  黄弘
 * -------------------------------------------------------------
 */

function smarty_function_huiyibang_jianli($params, &$smarty) {
    global $country, $province, $cities, $area;
    if (!$country) {
        require APP_PATH . '/common/huiyiabc.enum.php';
    }
    if (!$area) {
        $area = require APP_PATH . '/common/huiyiabc.area.php';
    }

    $html = drawBlocks($params['config'], $params['info'], $params['top'], $params['sub']);

    //$smarty->assign("country", json_encode($country));
    //$smarty->assign("province", json_encode($province));
    //$smarty->assign("cities", json_encode($cities));
    $countryS = json_encode($country);
    $provinceS = json_encode($province);
    $citiesS = json_encode($cities);
    $areaS = json_encode($area);
    return $html . <<<EOF
<script type="text/javascript">
    var COUNTRY = {$countryS};
    var PROVINCE = {$provinceS};
    var CITIES = {$citiesS};
    var AREA = {$areaS};
</script>
EOF;
}

function drawBlocks($blockList, &$data, $top, $sub, $path = array()) {
    $isTop = count($path) == 0;
    $isSub = count($path) == 1;
    $a = array();
    $i = 0;
    foreach ($blockList as $block) {
        // 显示隐藏的特殊罗辑
        if ($isTop && isset($top) && $top != $i) {
            $i ++;
            continue;
        }
        if ($isSub && isset($sub) && $sub != $i) {
            $i ++;
            continue;
        }
        if ($block['field_list']) {
            if ($block['type'] == 1) {
                $a[] = drawList($block['field_list'], $data[$block['block_id']], $block);
            } else {
                $a[] = drawDivs($block['field_list'], $data[0], $block);
            }
        } elseif ($block['block_list']) {
            $path2 = array_merge($path, array($block['name']));
            $a[] = drawBlocks($block['block_list'], $data, $top, $sub, $path2);
        }
        $i ++;
    }
    return implode("\r\n", $a);
}

function drawDivs($fieldList, &$data, $block) {
    if (in_array($fieldList[0]['type'], array('photo', 'file'))) {
        return drawImgs($fieldList, $data, $block);
    }

    $a = array();
    $a[] = '<div class="user_regtitlebg">';
    $a[] = '<a name="b'.$block['block_id'].'"/>';
    $a[] = <<<EOF
<div class="user_regtitle">
    {$block['name']}
    <a href="javascript:" class="user_bj">编辑</a>
    <span class="user_qd" style="display: none">
        <a href="javascript:;" class="user_qd_btn">保存</a>
        <a href="javascript:;" class="user_qx_btn">取消</a>
    </span>
</div>
EOF;
    $a2 = array();
    $a2[] = '<form class="item-form" style="display: none;" action="setjianli" method="POST">';
    $a2[] = '<input type="submit" style="visibility: hidden; width: 0; height: 0; border: 0; margin: 0; padding: 0;"/>';
    $a2[] = '<input type="hidden" name="profile_id" value="' . $block['profile_id'] . '"/>';
    $a2[] = '<table class="ftb2 mt20" style="word-break: break-all;">';
    $a1 = array();
    $a1[] = '<table class="ftb2 mt20" style="word-break: break-all;">';

    $i = 0;
    $j = 0;
    foreach ($fieldList as $field) {
        $name = $field['name'];
        $text = $data[$field['field_name']];
        $nama = $field['required'] ? '<span class="cc00">*</span> ' . $name : $name;
        if ($text) { $j = 1; }
        $wdgt = CanhuiAction::makeFieldWdgtForJianli($field, $text);
        $text = CanhuiAction::convFieldText($field, $text);

        if ($i % 2 == 0) {
            $a1[] = '<tr>';
            $a2[] = '<tr>';
        }
        if (in_array($field['type'], array('textView', 'multiSelect', 'selectArea', 'selectCity', 'selectProvince'))) {
            if ($i % 2 == 1) {
                $a1[] = '<td colspan="2" style="width: 338px;"></td></tr>';
                $a2[] = '<td colspan="2" style="width: 338px;"></td></tr>';
                $i ++;
            }
            $i ++;
            $a1[] = '<td class="tar" style="width: 135px">' . $name . '：</td>';
            $a1[] = '<td colspan="3" style="width: 541px" class="span3">' . $text . '</td>';
            $a2[] = '<td class="tar" style="width: 135px">' . $nama . '：</td>';
            $a2[] = '<td colspan="3" style="width: 541px" class="span3">' . $wdgt . '</td>';
        } else {
            $a1[] = '<td class="tar" style="width: 135px">' . $name . '：</td>';
            $a1[] = '<td style="width: 194px">' . $text . '</td>';
            $a2[] = '<td class="tar" style="width: 135px">' . $nama . '：</td>';
            $a2[] = '<td style="width: 194px">' . $wdgt . '</td>';
        }
        if ($i % 2 == 1) {
            $a1[] = '</tr>';
        }
        $i ++;
    }
    if ($i % 2 == 1) {
        $a1[] = '<td colspan="2" style="width: 338px;"></td></tr>';
        $a2[] = '<td colspan="2" style="width: 338px;"></td></tr>';
    }

    $a1[] = '</table>';
    $a2[] = '</table>';
    $a2[] = '</form>';
    $a[] = implode("\r\n", $a1);
    $a[] = implode("\r\n", $a2);
    $a[] = '</div>';
    
    // 未设置值则自动打开编辑窗口
    if (!$j) {
        $a[] = <<<EOF
<script type="text/javascript">console.log("xxx");
    (function($) {
        var that = $("[name=b{$block['block_id']}]").next("a");
        var tabs = that.closest(".user_regtitlebg").children("table,form");
        that.hide();
        that.next().show();
        that.next().children().eq(0).css("float", "right");
        that.next().children().eq(1).hide();
        tabs.eq(0).hide();
        tabs.eq(1).show();
    })(jQuery);
</script>
EOF;
    }
    
    return implode("\r\n", $a);
}

function drawImgs($fieldList, &$data, $block) {
    $a = array();
    $a[] = '<div class="user_regtitlebg">';
    $a[] = '<a name="b'.$block['block_id'].'"/>';
    $a[] = <<<EOF
<div class="user_regtitle">
    {$block['name']}
    <a href="javascript:" class="user_bj">编辑</a>
    <span class="user_qd" style="display: none">
        <a href="javascript:;" class="user_qd_btn" style="visibility: hidden;">确定</a>
        <a href="javascript:;" class="user_qx_btn" style="visibility: hidden;">查看</a>
    </span>
</div>
EOF;
    $a2 = array();
    $a2[] = '<iframe name="upload-' . $block['block_id'] . '" class="upload-frame" style="visibility: hidden; width: 0; height: 0; border: 0; margin: 0; padding: 0;"></iframe>';
    $a2[] = '<form class="upload-form" style="display: none;" action="setjianli" method="POST" enctype="multipart/form-data" target="upload-' . $block['block_id'] . '">';
    $a2[] = '<input type="submit" style="visibility: hidden; width: 0; height: 0; border: 0; margin: 0; padding: 0;"/>';
    $a2[] = '<input type="hidden" name="profile_id" value="' . $block['profile_id'] . '"/>';
    $a2[] = '<p>注：图片大小不能超过 2M, 请上传 jpg 格式图片.</p>';
    $a2[] = '<table class="ftb2 mt20 upload-list" style="word-break: break-all;">';
    $a1 = array();
    $a1[] = '<table class="ftb2 mt20 upload-list" style="word-break: break-all;">';

    $i = 0;
    $j = 0;
    foreach ($fieldList as $field) {
        $name = $field['name'];
        $text = $data[$field['field_name']];
        $nama = $field['required'] ? '<span class="cc00">*</span> ' . $name : $name;
        if ($text) { $j = 1; }
        //$wdgt = CanhuiAction::makeFieldWdgtForJianli($field, $text);
        $text = CanhuiAction::convFieldText($field, $text);

        $a1[] = '<tr>';
        $a1[] = '<td class="tar" style="width: 160px">' . $name . '：</td>';
        $a1[] = '<td>' . $text . '</td>';
        $a1[] = '</tr>';
        
        
        $a2[] = '<tr>';
        $a2[] = '<td class="tar" style="width: 160px">' . $nama . '：</td>';
        $a2[] = '<td>' . $text . '</td>';
        $a2[] = '<td style="width: 80px;"><a href="javascript:;" class="upload-btn" data-name="'.$field['field_name'].'">点此上传</a></td>';
        $a2[] = '</tr>';
        
        $i ++;
    }

    $a1[] = '</table>';
    $a2[] = '</table>';
    $a2[] = '</form>';
    $a[] = implode("\r\n", $a1);
    $a[] = implode("\r\n", $a2);
    $a[] = '</div>';
    
    // 未设置值则自动打开编辑窗口
    //if (!$j) {
        $a[] = "<script type=\"text/javascript\">$(function(){ $(\"[name=b{$block['block_id']}]\").next().click(); });</script>";
    //}
    
    return implode("\r\n", $a);
}

function drawList($fieldList, &$list, $block) {
    $a = array();
    $a[] = '<div class="user_regtitlebg">';
    $a[] = <<<EOF
<div class="user_regtitle">
    {$block['name']}
</div>
EOF;

    $a[] = '<table class="tbl mt20" style="table-layout: fixed; width:95%;">';
    $a[] = '<thead>';
    
    // 留交会定制（这代码很贰？木办法啦，甲方要求）
    if ($block['name'] == '教育经历') {
        $a[] = '<tr><td colspan="'.(count($fieldList) + 1).'" style="text-align: center;">请从最高学历填起 至本科学历</td></tr>';
    } elseif ($block['name'] == '工作经历') {
        $a[] = '<tr><td colspan="'.(count($fieldList) + 1).'" style="text-align: center;">请填写最近五个工作单位</td></tr>';
    }
    
    $a[] = '<tr class="tbg">';
    $b = array();
    foreach ($fieldList as $field) {
        $a[] = '<td>' . $field['name'] . '</td>';

        $wdgt = CanhuiAction::makeFieldWdgtForJianli($field, '');
        $b[] = '<tr>';
        $b[] = '<td class="form-label">' . ($field['required'] ? '<span class="cc00">*</span>' : '') . $field['name'] . ':</td>';
        $b[] = '<td class="form-input">' . $wdgt . '</td>';
        $b[] = '</tr>';
    }
    $a[] = '<td style="width: 65px;">操作</td>';
    $a[] = '</tr>';
    $a[] = '</thead>';
    $a[] = '<tbody>';

    foreach ($list as $data) {
        $data['profile_id'] = $block['profile_id'];
        $data['block_id'] = $block['block_id'];
        $data['list_id'] = $data['id'];
        $a[] = '<tr data-data="' . htmlspecialchars(json_encode($data)) . '">';
        foreach ($fieldList as $field) {
            $text = $data[$field['field_name']];
            $text = CanhuiAction::convFieldText($field, $text);

            $a[] = '<td>' . $text . '</td>';
        }
        $a[] = <<<EOF
<td>
    <a href="javascript:;" class="user_more user_xg_btn">修改</a>
    <a href="javascript:;" class="user_more user_sc_btn">删除</a>
</td>
EOF;
        $a[] = '</tr>';
    }

    $a[] = '</tbody>';
    $a[] = '<tfoot>';
    $a[] = '<tr><td colspan="' . (count($fieldList) + 1) . '">';
    $a[] = '<div class="add"><a href="javascript:void(0);" class="btns user_tj_btn"><span>添加' . $block['name'] . '</span></a></div>';
    $a[] = '</td></tr>';
    $a[] = '</tfoot>';
    $a[] = '</table>';

    // 添加表单
    $b = implode("\r\n", $b);
    $a[] = <<<EOF
<div class="overlay">
    <h2>添加{$block['name']} <a href="javascript:;" class="close">&times;</a></h2>
    <form class="list-form" action="setjianli" method="POST">
        <input type="hidden" name="profile_id" value="{$block['profile_id']}"/>
        <input type="hidden" name="block_id" value="{$block['block_id']}"/>
        <input type="hidden" name="list_id" value=""/>
        <table><tbody>{$b}</tbody></table>
        <div class="form-btns">
            <button type="submit" class="submit">提交</button>
            <a href="javascript:;" class="cancel">取消</a>
        </div>
    </form>
</div>
EOF;

    $a[] = '</div>';
    return implode("\r\n", $a);
}
