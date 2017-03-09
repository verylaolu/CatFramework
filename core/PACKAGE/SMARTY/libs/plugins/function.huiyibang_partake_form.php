<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File: function.huiyibang_partake_form.php
 * Type: function
 * Name: huiyibang_partake_form
 * Purpose: outputs a random magic answer
 * -------------------------------------------------------------
 */

function smarty_function_huiyibang_partake_form($params, &$smarty) {
    global $country, $province, $cities, $area;
    if (!$country) {
        require APP_PATH . '/common/huiyiabc.enum.php';
    }
    if (!$area) {
        $area = require APP_PATH . '/common/huiyiabc.area.php';
    }
    $html = drawBlockTabs($params['block_list']);
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

function drawBlockTabs($blockList) {
    $views = array();
    $i = 0;
    foreach ($blockList as $block) {
        if ($i > 0) {
            $display .= ' style="display:none;"';
        }

        $views[] = '<div class="jobapplyContent" ' . $display . '>';//
        $views[] = '<form onsubmit="return reg(this);" class="item-form" method="post" action="/login/doregister3?profile_id='.$block['block_list'][0]['profile_id'].'">
                <b id="err" style="color:red;font-weight:bold;font-size:20px;margin-left: 20px;"></b>';
        if ($block['block_list']) {
            $views[] = drawBlockList($block['block_list'][0]['block_list'], array($block['name']));
        }
        
        //20140922 程宏达  去掉直接跳过入口
        $views[] = '<input style="margin-left:255px;margin-top:10px;font-size:20px;cursor:pointer;" class="r_qd"  type="submit" value="保存资料">' ;//<a href="/login/register4?user_type='.$_SESSION['user_info']['user_type'].'" class="r_fh" style="margin-left:435px;display:block;margin-top: -34px;font-size:16px;">直接跳过 >></a>'
        //<input type="button" class="r_fh"  value="直接跳过 >>" style="margin-left:30px;">';
        $views[] = '</form>';
        $views[] = "</div>";
        $i++;
    }
    return implode("\r\n", $views);
}

function drawBlockList($blockList, $blockPath = array()) {
    $views = array();
    foreach ($blockList as $k => $block) {
        if($k > 0){//web页上补全资料时 报名块 只显示基本资料
            continue;
        }
        
        
        if ($block['type'] == 1) {
            continue;
        }
        if ($block['block_list']) {
            $blockPath2 = array_merge($blockPath, array($block['name']));
            $views[] = drawBlockList($block['block_list'], $blockPath2);
        } else {
            $blockPath2 = array_merge($blockPath, array($block['name']));
            $views[] = drawFieldList($block['field_list'], $block['block_id'], $block['type'], $blockPath2);
        }
    }
    return implode("\r\n", $views);
}

function drawFieldList($fieldList, $blockId, $blockType, $blockPath) {
    global $country, $province, $cities, $area;
    //var_dump($cities);exit;
    
    $views = array();
    $views[] = '<div class="row">';
    $views[] = "<h3 style='padding:10px;'>" . $blockPath[count($blockPath) - 1] . "</h3>";
    $fn = $blockType == 0 ? 'item' : 'list';
    $views[] = "<div style='border-top:1px solid #bebebe;width:100%;'></div>";
    $views[] = '<table style="width: 100%;" cellspacing="0" cellpadding="10">';
    $i = 0;
    foreach ($fieldList as $field) {
        if (in_array($field['type'], array('multiSelect', 'textView', 'selectCity','selectArea'))) {
            $span = 2;
        } else {
            $span = 1;
        }
        // 占2个单元格的给上一个封尾
        if ($span == 2 && $i % 2 == 1) {
            $views[] = '<td colspan="2"></td></tr>';
            $i++;
        }

        if ($i % 2 == 0) {
            $views[] = '<tr>';
        }
        $nama = $field['required'] ? '<span style="color:red;">*</span> ' . $field['name'] : $field['name'];
        $views[] = '<td class="label" style="background-color: #f5f5f5;">';
        $views[] = $nama.'： ';
        $views[] = '</td>';

        if ($span == 2) {
            $views[] = '<td class="" style="padding: 3px;border-right:medium none;" colspan="3">';
            $i ++;
        } else {
            if($i % 2 == 1){
                $views[] = '<td class="" style="padding: 3px;border-right:medium none;">';
            }else{
                $views[] = '<td class="" style="padding: 3px;">';
            }
        }

        
        if ($field['required']) {
            if ($field['type'] == 'multiSelect' || $field['type'] == 'singleSelect') {
                $rq = 'requires="requires"';
            } else {
                $rq = 'required="required"';
            }
        } else {
            $rq = '';
        }
        
        
        
        switch ($field['type']) {
           // case 'textField':
               // if($field['extr'] == 'email'){
               //     $views[] = '<input type="email" class="inputtext" name="' . $fn . '[' . $field['field_name'] . ']" value="" email />';
               // }else{
                   // $views[] = '<input type="text" class="inputtext" name="' . $fn . '[' . $field['field_name'] . ']" value="" '.$rq.' />';
               // }
               // break;
            case 'textView':
                $views[] = '<textarea name="' . $fn . '[' . $field['field_name'] . ']" cols="70" rows="4" class="textarea" onKeyUp="textAreaSlice(this,200)" '.$rq.'></textarea>';
                break;
            case 'singleSelect':
                $option = '';
                //$option .= '<option value="">--请选择--</option>';
//                if (count($field['value_list']) > 3) {
                    foreach ($field['value_list'] as $v) {
                        $option .= '<option value="' . $v['value'] . '">' . $v['name'] . '</option>';
                    }
                    $views[] = '<select name="' . $fn . '[' . $field['field_name'] . ']" '.$rq.' >' . $option . '</select>';
//                } else {
//                    $option .= '';
//                    foreach ($field['value_list'] as $k => $v) {
//                        if ($k == 0) {
//                            $check = ' checked = "checked" ';
//                        } else {
//                            $check = '';
//                        }
//                        $option .= '<input type="radio" '.$rq.'   name="' . $fn . '[' . $field['field_name'] . ']" value="' . $v['value'] . '" ' . $check . ' /> ' . $v['name'] . ' ';
//                    }
//                    $option .= '';
//                    $views[] = $option;
//                }
                break;
            case 'multiSelect':

                $option = '';
                $option .= '';
                foreach ($field['value_list'] as $k => $v) {
                    $option .= '<label  style="width: 210px;" class="l"><input type="checkbox" '.$rq.'  name="' . $fn . '[' . $field['field_name'] . ']" value="' . $v['value'] . '" ' . $check . ' /> ' . $v['name'] . ' </label>';
                }
                $option .= '<span style="clear: both;"></span>';
                $views[] = $option;
                break;
            case 'popupYM':
                $views[] = '<input type="text"  '.$rq.'  name="' . $fn . '[' . $field['field_name'] . ']" value="" class="inputtext hasDatepicker" onfocus="WdatePicker({lang:\'zh-cn\',skin:\'whyGreen\',minDate:\'1900-01-01\',dateFmt:\'yyyy-MM\'})"/>';
                break;
            case 'popupDate':
                $views[] = '<input type="text" '.$rq.'  name="' . $fn . '[' . $field['field_name'] . ']" value="" class="inputtext hasDatepicker" onfocus="WdatePicker({lang:\'zh-cn\',skin:\'whyGreen\',minDate:\'1900-01-01\',dateFmt:\'yyyy-MM-dd\'})"/>';
                break;
            case 'popupTime':
                $views[] = '<input type="text" '.$rq.'  name="' . $fn . '[' . $field['field_name'] . ']" value="" class="inputtext hasDatepicker" onfocus="WdatePicker({lang:\'zh-cn\',skin:\'whyGreen\',minDate:\'1900-01-01\',dateFmt:\'yyyy-MM-dd HH:mm\'})"/>';
                break;
            case 'selectCountry':
                $views[] = '<select name="' . $fn . '[' . $field['field_name'] . ']" class="country" '.$rq.' >';
                foreach ($country as $c => $nc) {
                        $views[] = '<option value="' . $c . '">' . $nc . '</option>';
                }
                $views[] = '</select>';
                break;
            case 'selectProvince':
                //$views[] = '<select class="country" '.$rq.' ></select>';
                $views[] = '<select name="' . $fn . '[' . $field['field_name'] . ']" class="province" '.$rq.' >';
                foreach ($province as $x => $y) {
                    foreach ($y as $p => $np) {
                        $views[] = '<option value="' . $p . '">' . $np . '</option>';
                    }
                }
                $views[] = '</select>';
                break;
            case 'selectCity':
                //$views[] = '<select class="country" '.$rq.' ></select>';
                //$views[] = '<select class="province" '.$rq.' ></select>';
                //$views[] = '<select name="' . $fn . '[' . $field['field_name'] . ']" class="city" '.$rq.' ></select>';
                $views[] = '<select name="province4' . $fn . '" class="province">';
                //$b[] = '<option value="">--请选择--</option>';
                foreach ($province as $x => $y) {
                    foreach ($y as $v => $n) {
                        $views[] = '<option value="' . $v . '">' . $n . '</option>';
                    }
                }
                $views[] = '</select>';
                $views[] = '<select name="' . $fn . '" ' . $rq . ' class="city">';
                //$c[] = '<option value="">--请选择--</option>';
                foreach ($cities as $x => $y) {
                    foreach ($y as $v => $n) {
                        $views[] = '<option value="' . $v . '">' . $n . '</option>';
                    }
                }
                $views[] = '</select>';
                break;
            case 'selectArea':
//                $value = '';
//                $x = explode(',', $value);
//                if (!$area[$x[0]]) {
//                    $x[0] = '';
//                }
//                if (!$x[0]) {
                    $x[0] = array_keys($area);
                    $x[0] = $x[0][0];
//                }
                if (!$area[$x[0]]['list'][$x[1]]) {
                    $x[1] = '';
                }
                if (!$x[1]) {
                    $x[1] = array_keys($area[$x[0]]['list']);
                    $x[1] = $x[1][0];
                }
                $views[] = '<select name="' . $fn . '[' . $field['field_name'] . '][]" ' . $rq . ' class="area_0">';
                foreach ($area as $v => $b) {
                    $views[] = '<option value="' . $b['id'] . '">' . $b['name'] . '</option>';
                }
                $views[] = '</select>';
                $views[] = '<select name="' . $fn . '[' . $field['field_name'] . '][]" ' . $rq . ' class="area_1">';
                foreach ($area[$x[0]]['list'] as $v => $b) {
                    $views[] = '<option value="' . $b['id'] . '">' . $b['name'] . '</option>';
                }
                $views[] = '</select>';
                //$views[] = '<select name="' . $fn . '[' . $field['field_name'] . '][]" ' . $rq . ' class="area_2" '.(!$area[$x[0]]['list'][$x[1]]['list']?'style="display:none"':'').'>';
                $views[] = '<select name="' . $fn . '[' . $field['field_name'] . '][]" ' . $rq . ' class="area_2" style="display:none;">';
                
                foreach ($area[$x[0]]['list'][$x[1]]['list'] as $v => $b) {
                    $views[] = '<option value="' . $b['id'] . '">' . $b['name'] . '</option>';
                }
                $views[] = '</select>';
                break;
            case 'photo':
                $views[] = '<input type="file" name="' . $fn . '[' . $field['field_name'] . ']"/>';
                break;
            default:
                if ($field['type'] == 'textField'){
                    if(in_array($field['extr'], array('number', 'email', 'url'))){
                        if($field['extr'] == 'email'){
                            $v_email = ' value="'.$_SESSION['user_info']['email'].'" ';
                        }else{
                            $v_email = ' value="" ';
                        }
                        $views[] = '<input type="' . $field['extr'] . '" name="' . $fn . '[' . $field['field_name'] . ']" '.$v_email.' class="inputtext" ' . $rq . '/>';
                        break;
                    }elseif($field['extr'] == 'popupYM'){
                        $views[] = '<input type="' . $field['extr'] . '"  '.$rq.'  name="' . $fn . '[' . $field['field_name'] . ']" value="" class="inputtext hasDatepicker" onfocus="WdatePicker({lang:\'zh-cn\',skin:\'whyGreen\',minDate:\'1900-01-01\',dateFmt:\'yyyy-MM\'})"/>';
                        break;
                    }elseif($field['extr'] == 'popupDate'){
                        $views[] = '<input type="' . $field['extr'] . '"  '.$rq.'  name="' . $fn . '[' . $field['field_name'] . ']" value="" class="inputtext hasDatepicker" onfocus="WdatePicker({lang:\'zh-cn\',skin:\'whyGreen\',minDate:\'1900-01-01\',dateFmt:\'yyyy-MM-dd\'})"/>';
                        break;
                    }elseif($field['extr'] == 'popupTime'){
                        $views[] = '<input type="' . $field['extr'] . '"  '.$rq.'  name="' . $fn . '[' . $field['field_name'] . ']" value="" class="inputtext hasDatepicker" onfocus="WdatePicker({lang:\'zh-cn\',skin:\'whyGreen\',minDate:\'1900-01-01\',dateFmt:\'yyyy-MM-dd HH:mm\'})"/>';
                        break;
                    }else{
                        if($field['field_name'] == 'f1' && !empty($_SESSION['user_info']['name'])){
                            $v_name = ' value="'.$_SESSION['user_info']['name'].'" ';
                        }else{
                            $v_name = ' value="" ';
                        }
                        $views[] = '<input type="text" name="' . $fn . '[' . $field['field_name'] . ']" '.$v_name.' class="inputtext" ' . $rq . '/>';
                        break;
                    }
                }
//                if ($field['type'] == 'textField' && in_array($field['extr'], array('number', 'email', 'url'))) {
//                    $ft = $field['extr'];
//                } else {
//                    $ft = 'text';
//                }
//                $views[] = '<input type="' . $ft . '" name="' . $fn . '[' . $field['field_name'] . ']" value="" class="inputtext" ' . $rq . '/>';
//                break;
        }

        $views[] = '</td>';

        if ($i % 2 == 1) {
            $views[] = '</tr>';
        }

        $i++;
    }
    $views[] = '</table>';
    $views[] = "</div>";

    return implode("\r\n", $views);
}
