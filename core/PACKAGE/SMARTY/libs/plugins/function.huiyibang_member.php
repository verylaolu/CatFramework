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

function smarty_function_huiyibang_member($params, &$smarty) {
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
    var UID = {$_REQUEST['user_id']};
    $('.form_ym').datetimepicker({
        language: 'zh',
        autoclose: 1,
        weekStart: 1,
        todayBtn: 1,
        todayHighlight: 1,
        forceParse: 0,
        startView: 3,
        minView: 3
    });
    $('.form_date').datetimepicker({
        language: 'zh',
        autoclose: 1,
        weekStart: 1,
        todayBtn: 1,
        todayHighlight: 1,
        forceParse: 0,
        startView: 2,
        minView: 2
    });
    $('.form_time').datetimepicker({
        language: 'zh',
        autoclose: 1,
        weekStart: 1,
        todayBtn: 1,
        todayHighlight: 1,
        forceParse: 0,
        startView: 1,
        minView: 0,
        maxView: 1
    });
    $('.form_datetime').datetimepicker({
        language: 'zh',
        autoclose: 1,
        weekStart: 1,
        todayBtn: 1,
        todayHighlight: 1,
        forceParse: 0,
        startView: 2,
        showMeridian: 1
    });
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
        $mf = ' enctype="multipart/form-data" target="upload-' . $block['block_id'] . '"';
    } else {
        $mf = '';
    }

    $a = array();
    $a[] = <<<EOF
<div class="user_bk" id="b{$block['block_id']}">
<ul class="nav nav-tabs shen-ntab p-d" role="tablist">
    <li class="active"><a href="#home" role="tab" data-toggle="tab">{$block['name']}</a></li>
    <li class="tab-btn">
        <button type="button" class="btn btn-sm btn-info user_bj"><span class="glyphicon glyphicon-cog"></span> 编辑 </button>
        <span class="user_qd btn-group" style="display: none">
EOF;
    if ($mf) {
        $a[] = '<button type="button" class="btn btn-sm btn-info user_qx_btn"><span class="glyphicon glyphicon-eye-open"></span> 查看 </button>';
    } else {
        $a[] = '<button type="button" class="btn btn-sm btn-info user_qd_btn"><span class="glyphicon glyphicon-ok"></span> 确定 </button>';
        $a[] = '<button type="button" class="btn btn-sm btn-danger user_qx_btn"><span class="glyphicon glyphicon-remove"></span> 取消 </button>';
    }
    $a[] = <<<EOF
        </span>
    </li>
</ul>
EOF;
    $a1 = array();
    $a2 = array();
    if ($mf) {
        $a2[] = '<iframe name="upload-' . $block['block_id'] . '" class="upload-frame" style="visibility: hidden; width: 0; height: 0; border: 0; margin: 0; padding: 0; display: block;"></iframe>';
    }
    $a2[] = '<form class="' . ($mf ? 'upload-form' : 'item-form') . '" style="display: none;" action="setmemberinfo?user_id=' . $_REQUEST['user_id'] . '" method="POST" ' . $mf . '>';
    $a2[] = '<div class="vh">';
    $a2[] = '<input type="hidden" name="profile_id" value="' . $block['profile_id'] . '"/>';
    $a2[] = '<input type="submit"/>';
    $a2[] = '</div>';
    $a2[] = '<table class="table table-bordered shenh-table ' . ($mf ? 'upload-list' : 'item-list') . '" style="word-break: break-all;">';
    $a2[] = '<tbody><tr class="user_bt"><td class="active"></td><td></td><td class="active"></td><td></td></tr>';
    $a1[] = '<table class="table table-bordered shenh-table ' . ($mf ? 'upload-list' : 'item-list') . '" style="word-break: break-all;">';
    $a1[] = '<tbody><tr class="user_bt"><td class="active"></td><td></td><td class="active"></td><td></td></tr>';

    $i = 0;
    foreach ($fieldList as $field) {
        $name = $field['name'];
        $text = $data[$field['field_name']];
        $nama = $field['required'] ? '<span class="cc00">*</span> ' . $name : $name;
        $wdgt = CanhuiAction::makeFieldWdgtForMember($field, $text);
        $text = CanhuiAction::convFieldText($field, $text);
        
        if (!$mf) {
            $text = '<p class="form-control-static">'.$text.'</p>';
        }

        if ($i % 2 == 0) {
            $a1[] = '<tr>';
            $a2[] = '<tr>';
        }
        if (in_array($field['type'], array('textView', 'multiSelect', 'selectArea', 'selectCity', 'selectProvince'))) {
            if ($i % 2 == 1) {
                $a1[] = '<td colspan="2"></td></tr>';
                $a2[] = '<td colspan="2" style="width: 338px;"></td></tr>';
                $i ++;
            }
            $i ++;
            $a1[] = '<td class="active">' . $name . '：</td>';
            $a1[] = '<td colspan="3" class="span3 form-group">' . $text . '</td>';
            $a2[] = '<td class="active">' . $nama . '：</td>';
            $a2[] = '<td colspan="3" class="span3 form-group">' . $wdgt . '</td>';
        } else {
            $a1[] = '<td class="active">' . $name . '：</td>';
            $a1[] = '<td class="form-group">' . $text . '</td>';
            $a2[] = '<td class="active">' . $nama . '：</td>';
            $a2[] = '<td class="form-group">' . $wdgt . '</td>';
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

    $a1[] = '</tbody></table>';
    $a2[] = '</tbody></table>';
    $a2[] = '</form>';
    $a[] = implode("\r\n", $a1);
    $a[] = implode("\r\n", $a2);
    $a[] = '</div>';
    return implode("\r\n", $a);
}

function drawList($fieldList, &$list, $block) {
    $a = array();
    $a[] = <<<EOF
<div class="user_bk" id="b{$block['block_id']}">
<ul class="nav nav-tabs shen-ntab p-d" role="tablist">
    <li class="active"><a href="#home" role="tab" data-toggle="tab">{$block['name']}</a></li>
    <li class="tab-btn">
        <button type="button" class="btn btn-sm btn-info user_tj_btn"><span class="glyphicon glyphicon-plus"></span> 添加 </button>
    </li>
</ul>
EOF;

    $a[] = '<table class="table table-hover table-striped" style="table-layout: fixed;">';
    $a[] = '<thead>';
    $a[] = '<tr>';
    $b = array();
    foreach ($fieldList as $field) {
        $a[] = '<th>' . $field['name'] . '</th>';

        $wdgt = CanhuiAction::makeFieldWdgtForMember($field, '');
        $b[] = '<div class="form-group">';
        $b[] = '<label class="col-sm-4 control-label">' . ($field['required'] ? '<span class="cc00">*</span>' : '') . $field['name'] . ':</label>';
        $b[] = '<div class="col-sm-8">' . $wdgt . '</div>';
        $b[] = '</div>';
    }
    $a[] = '<th style="width: 80px;">操作</th>';
    $a[] = '</tr>';
    $a[] = '</thead>';
    $a[] = '<tbody>';

    foreach ($list as $data) {
        $data['profile_id'] = $block['profile_id'];
        $data['block_id'] = $block['block_id'];
        $data['list_id'] = $data['id'];

        // 转换数据类型
        $data2 = $data;
        foreach ($fieldList as $field) {
            switch ($field['type']) {
                case 'popupYM':
                    $data2[$field['field_name']] = date('Y-m', $data2[$field['field_name']]);
                    break;
                case 'popupDate':
                    $data2[$field['field_name']] = date('Y-m-d', $data2[$field['field_name']]);
                    break;
                case 'popupTime':
                    $data2[$field['field_name']] = date('Y-m-d H:i', $data2[$field['field_name']]);
                    break;
            }
        }

        $a[] = '<tr data-data="' . htmlspecialchars(json_encode($data2)) . '">';
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
    $a[] = '<!--tr><td colspan="' . (count($fieldList) + 1) . '">';
    $a[] = '<div class="add"><a href="javascript:void(0);" class="btns user_tj_btn"><span>添加' . $block['name'] . '</span></a></div>';
    $a[] = '</td></tr-->';
    $a[] = '</tfoot>';
    $a[] = '</table>';

    // 添加表单
    $b = implode("\r\n", $b);
    $a[] = <<<EOF
<div class="modal fade">
    <form class="list-form form-horizontal" action="setmemberinfo?user_id={$_REQUEST['user_id']}" method="POST">
    <input type="hidden" name="profile_id" value="{$block['profile_id']}"/>
    <input type="hidden" name="block_id" value="{$block['block_id']}"/>
    <input type="hidden" name="list_id" value=""/>
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title">{$block['name']}</h4>
      </div>
      <div class="modal-body">
        {$b}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        <button type="submit" class="btn btn-primary">保存设置</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
    </form>
</div><!-- /.modal -->
EOF;

    $a[] = '</div>';
    return implode("\r\n", $a);
}
