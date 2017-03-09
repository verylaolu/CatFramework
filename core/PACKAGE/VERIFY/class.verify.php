<?php

/**
 * 验证码类
 *
 */
class verify {

    private $_width     = 100;
    private $_height    = 25;
    private $_code_num  = 4;
    private $_code_key  = '';
    private $_font_size = 20;
    public $SESSION;
    public $img_type    = 'jpg';

    public function __construct($SESSION,$code_key = 'verity', $width = 100, $height = 25, $code_num = 4, $font_size = 30) {
        
        $this->SESSION = $SESSION;
        $this->_code_key = $code_key;
        $this->_width     = $width;
        $this->_height    = $height;
        $this->_code_num  = $code_num;
        $this->_font_size = $font_size;
    }

    /**
     * 随进获取验证码
     */
    public function get_code() {
        $code_type = mt_rand(1, 3);
        switch ($code_type) {
            case 1 :
                $this->get_math_code();
                break;
            case 2 :
                $this->get_num_code();
                break;
            case 3 :
                $this->get_char_code();
                break;
        }
    }

    /**
     * 获取数学验证码
     */
    public function get_math_code() {
        $im = imagecreate($this->_width, $this->_height);

        //imagecolorallocate($im, 14, 114, 180); // background color
//    	$red = imagecolorallocate($im, 173,175,202);
        $black   = $this->get_color($im);
        $white   = $this->get_color($im);
        $bgcolor = $this->get_color($im);
        $num1    = mt_rand(1, 20);
        $num2    = mt_rand(1, 20);

        //计算结果
        $ari_type = mt_rand(1, 3);
        switch ($ari_type) {
            case 1:
                $arithmetic  = '+';
                $code_result = $num1 + $num2;
                break;
            case 2:
                $arithmetic  = '-';
                if ($num1 < $num2) {
                    $test_num = $num1;
                    $num1     = $num2;
                    $num2     = $test_num;
                }
                $code_result = $num1 - $num2;
                break;
            case 3:
                $arithmetic  = 'x';
                $num1        = mt_rand(1, 9);
                $num2        = mt_rand(1, 9);
                $code_result = $num1 * $num2;
                break;
        }

        //存储数据
        $this->SESSION->SET_SESSION($this->_code_key, $code_result, 1800);
//    	$gray = imagecolorallocate($im, 118, 151, 199);
        $black = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));

        //画背景
        imagefilledrectangle($im, 0, 0, 0, 0, $bgcolor);
        //在画布上随机生成大量点，起干扰作用;
        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($im, rand(0, $this->_width), rand(0, $this->_height), $this->get_color($im));
        }
        $x = ceil(($this->_width - 5) / 7);
        $x = rand($x-5, $x+5);
        $y = $this->_font_size+($this->_height-$this->_font_size)/2;
        $y = rand($y-5, $y+5);

        imagettftext($im, $this->_font_size, rand(0,25), 5, $y, $this->get_color($im), $this->get_ttf(), $num1);
        imagettftext($im, $this->_font_size, 0, $x * 2, $y, $this->get_color($im), $this->get_ttf(), $arithmetic);
        imagettftext($im, $this->_font_size, rand(0,-25), $x * 3, $y, $this->get_color($im), $this->get_ttf(), $num2);
        imagettftext($im, $this->_font_size, 0, $x * 5, $y, $this->get_color($im), $this->get_ttf(), '=');
        imagettftext($im, $this->_font_size, 0, $x * 6, $y, $this->get_color($im), $this->get_ttf(), '?');

        switch ($this->img_type) {
            case 'png':
                header("Content-type: image/png");
                imagepng($im);
                break;
            case 'jpg':
                header("Content-type: image/jpeg");
                imagejpeg($im);
                break;
        }
        imagedestroy($im);
    }

    /**
     * 数字验证码
     */
    public function get_num_code() {
        $code = "";
        for ($i = 0; $i < $this->_code_num; $i++) {
            $code .= rand(0, 9);
        }
        //4位验证码也可以用rand(1000,9999)直接生成
        //存储数据
        $this->SESSION->SET_SESSION($this->_code_key, $code, 1800);
        //创建图片，定义颜色值
        Header("Content-type: image/PNG");
        $im      = imagecreate($this->_width, $this->_height);
        $black   = $this->get_color($im);
        $gray    = $this->get_color($im);
        $bgcolor = $this->get_color($im);
        $white   = $this->get_color($im);

        imagefill($im, 0, 0, $bgcolor);

        //画边框
        imagerectangle($im, 0, 0, $this->_width - 1, $this->_height - 1, $bgcolor);

        //随机绘制两条虚线，起干扰作用
        $style = array(
            $black,
            $black,
            $black,
            $black,
            $black,
            $gray,
            $gray,
            $gray,
            $gray,
            $gray
        );
        imagesetstyle($im, $style);
        $y1    = rand(0, $this->_height);
        $y2    = rand(0, $this->_height);
        $y3    = rand(0, $this->_height);
        $y4    = rand(0, $this->_height);
        imageline($im, 0, $y1, $this->_width, $y3, IMG_COLOR_STYLED);
        imageline($im, 0, $y2, $this->_width, $y4, IMG_COLOR_STYLED);

        //在画布上随机生成大量黑点，起干扰作用;
        for ($i = 0; $i < 80; $i++) {
            imagesetpixel($im, rand(0, $this->_width), rand(0, $this->_height), $black);
        }
        //将数字随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
//        $strx = rand(5, 9);
        $x = ceil(($this->_width - 5) / $this->_code_num);
//        $x = rand($x-5, $x+5);
        $y = $this->_font_size+($this->_height-$this->_font_size)/2;
        $y = rand($y-5, $y+5);
        for ($i = 0; $i < $this->_code_num; $i++) {
            $strpos = rand(1, 6);
            
            imagettftext($im, $this->_font_size, mt_rand(0, 20), $x*$i+5, $y, $this->get_color($im), $this->get_ttf(), substr($code, $i, 1));
            $strx += rand(13, 20);
        }
        switch ($this->img_type) {
            case 'png':
                header("Content-type: image/png");
                imagepng($im);
                break;
            case 'jpg':
                header("Content-type: image/jpeg");
                imagejpeg($im);
                break;
        }
        imagedestroy($im);
    }

    /**
     * 生成字符验证码
     */
    public function get_char_code() {
        // 去掉了 0 1 O l 等
        $str  = "23456789abcdefghijkmnpqrstuvwxyz";
        $code = '';
        for ($i = 0; $i < $this->_code_num; $i++) {
            $code .= $str[mt_rand(0, strlen($str) - 1)];
        }

        //存储数据
        $this->SESSION->SET_SESSION($this->_code_key, $code, 1800);
        //创建图片，定义颜色值
        Header("Content-type: image/PNG");
        $im      = imagecreate($this->_width, $this->_height);
        $black   = $this->get_color($im);
//        $gray = imagecolorallocate($im, 118, 151, 199);
        $white   = $this->get_color($im);
        $bgcolor = $this->get_color($im);
        //画背景
        imagefilledrectangle($im, 0, 0, $this->_width, $this->_height, $bgcolor);
        //画边框
        imagerectangle($im, 0, 0, $this->_width - 1, $this->_height - 1, $bgcolor);
        //imagefill($im, 0, 0, $bgcolor);    
        //在画布上随机生成大量点，起干扰作用;
        for ($i = 0; $i < 280; $i++) {
            imagesetpixel($im, rand(0, $this->_width), rand(0, $this->_height), $black);
        }
        //将字符随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
   
        $x = ceil(($this->_width - 5) / $this->_code_num);
//        $x = rand($x-5, $x+5);
        $y = $this->_font_size+($this->_height-$this->_font_size)/2;
        $y = rand($y-5, $y+5);
        for ($i = 0; $i < $this->_code_num; $i++) {
            $strpos = rand(1, 6);
            imagettftext($im, $this->_font_size, mt_rand(0, 20), $x*$i+5, $y, $this->get_color($im), $this->get_ttf(), substr($code, $i, 1));
            $strx += rand(13, 20);
        }
        switch ($this->img_type) {
            case 'png':
                header("Content-type: image/png");
                imagepng($im);
                break;
            case 'jpg':
                header("Content-type: image/jpeg");
                imagejpeg($im);
                break;
        }
        imagedestroy($im);
    }

    /**
     * 检查验证码
     */
    public function check_code($code) {

            $verify_code =     $this->SESSION->GET_SESSION($this->_code_key);
        if ($verify_code && $verify_code == $code) {
            return true;
        } else {
            //删除验证码
            $this->SESSION->DEL_SESSION($this->_code_key);
        }

        return false;
    }

    /**
     * 获取当前用户唯一key
     */
    public function get_cur_user_key() {
//        $cur_user_key = lib_cookie::getcookie('verfiy_code_user');
        if (!$cur_user_key) {
            $cur_user_key = md5(microtime() . rand(1111, 9999));
            $cur_user_key = substr($cur_user_key, 8, 16);
//            lib_cookie::setcookie('verfiy_code_user', $cur_user_key, 0, '/');
        }

        return $cur_user_key;
    }

    public function get_ttf() {
        $font     = array("DejaVuSans-Bold.ttf", "DejaVuSans-BoldOblique.ttf", "DejaVuSans-Oblique.ttf", "DejaVuSans.ttf");
        $font_num = rand(0, 3);
        return dirname(__FILE__) . '/font/' . $font[$font_num];
    }

    public function get_color($im) {
        $color     = array('#FFFFFF', '#FFFFF0', '#FFFFE0', '#FFFF00', '#FFFAFA', '#FFFAF0', '#FFFACD',
            '#FFF8DC', '#FFF68F', '#FFF5EE', '#FFF0F5', '#FFEFDB', '#FFEFD5', '#FFEC8B',
            '#FFEBCD', '#FFE7BA', '#FFE4E1', '#FFE4C4', '#FFE4B5', '#FFE1FF', '#FFDEAD',
            '#FFDAB9', '#FFD700', '#FFD39B', '#FFC1C1', '#FFC125', '#FFC0CB', '#FFBBFF',
            '#FFB90F', '#FFB6C1', '#FFB5C5', '#FFAEB9', '#FFA54F', '#FFA500', '#FFA07A',
            '#FF8C69', '#FF8C00', '#FF83FA', '#FF82AB', '#FF8247', '#FF7F50', '#FF7F24',
            '#FF7F00', '#FF7256', '#FF6EB4', '#FF6A6A', '#FF69B4', '#FF6347', '#FF4500',
            '#FF4040', '#FF3E96', '#FF34B3', '#FF3030', '#FF1493', '#FF00FF', '#FF0000',
            '#FDF5E6', '#FCFCFC', '#FAFAFA', '#FAFAD2', '#FAF0E6', '#FAEBD7', '#FA8072',
            '#F8F8FF', '#F7F7F7', '#F5FFFA', '#F5F5F5', '#F5F5DC', '#F5DEB3', '#F4F4F4',
            '#F4A460', '#F2F2F2', '#F0FFFF', '#F0FFF0', '#F0F8FF', '#F0F0F0', '#F0E68C',
            '#F08080', '#EEEEE0', '#EEEED1', '#EEEE00', '#EEE9E9', '#EEE9BF', '#EEE8CD',
            '#EEE8AA', '#EEE685', '#EEE5DE', '#EEE0E5', '#EEDFCC', '#EEDC82', '#EED8AE',
            '#EED5D2', '#EED5B7', '#EED2EE', '#EECFA1', '#EECBAD', '#EEC900', '#EEC591',
            '#EEB4B4', '#EEB422', '#EEAEEE', '#EEAD0E', '#EEA9B8', '#EEA2AD', '#EE9A49',
            '#EE9A00', '#EE9572', '#EE82EE', '#EE8262', '#EE7AE9', '#EE799F', '#EE7942',
            '#EE7621', '#EE7600', '#EE6AA7', '#EE6A50', '#EE6363', '#EE5C42', '#EE4000',
            '#EE3B3B', '#EE3A8C', '#EE30A7', '#EE2C2C', '#EE1289', '#EE00EE', '#EE0000',
            '#EDEDED', '#EBEBEB', '#EAEAEA', '#E9967A', '#E8E8E8', '#E6E6FA', '#E5E5E5',
            '#E3E3E3', '#E0FFFF', '#E0EEEE', '#E0EEE0', '#E0E0E0', '#E066FF', '#DEDEDE',
            '#DEB887', '#DDA0DD', '#DCDCDC', '#DC143C', '#DBDBDB', '#DB7093', '#DAA520',
            '#DA70D6', '#D9D9D9', '#D8BFD8', '#D6D6D6', '#D4D4D4', '#D3D3D3', '#D2B48C',
            '#D2691E', '#D1EEEE', '#D1D1D1', '#D15FEE', '#D02090', '#CFCFCF', '#CDCDC1',
            '#CDCDB4', '#CDCD00', '#CDC9C9', '#CDC9A5', '#CDC8B1', '#CDC673', '#CDC5BF',
            '#CDC1C5', '#CDC0B0', '#CDBE70', '#CDBA96', '#CDB7B5', '#CDB79E', '#CDB5CD',
            '#CDB38B', '#CDAF95', '#CDAD00', '#CDAA7D', '#CD9B9B', '#CD9B1D', '#CD96CD',
            '#CD950C', '#CD919E', '#CD8C95', '#CD853F', '#CD8500', '#CD8162', '#CD7054',
            '#CD69C9', '#CD6889', '#CD6839', '#CD661D', '#CD6600', '#CD6090', '#CD5C5C',
            '#CD5B45', '#CD5555', '#CD4F39', '#CD3700', '#CD3333', '#CD3278', '#CD2990',
            '#CD2626', '#CD1076', '#CD00CD', '#CD0000', '#CCCCCC', '#CAFF70', '#CAE1FF',
            '#C9C9C9', '#C7C7C7', '#C71585', '#C6E2FF', '#C67171', '#C5C1AA', '#C4C4C4',
            '#C2C2C2', '#C1FFC1', '#C1CDCD', '#C1CDC1', '#C1C1C1', '#C0FF3E', '#BFEFFF',
            '#BFBFBF', '#BF3EFF', '#BEBEBE', '#BDBDBD', '#BDB76B', '#BCEE68', '#BCD2EE',
            '#BC8F8F', '#BBFFFF', '#BABABA', '#BA55D3', '#B9D3EE', '#B8B8B8', '#B8860B',
            '#B7B7B7', '#B5B5B5', '#B4EEB4', '#B4CDCD', '#B452CD', '#B3EE3A', '#B3B3B3',
            '#B2DFEE', '#B23AEE', '#B22222', '#B0E2FF', '#B0E0E6', '#B0C4DE', '#B0B0B0',
            '#B03060', '#AEEEEE', '#ADFF2F', '#ADD8E6', '#ADADAD', '#ABABAB', '#AB82FF',
            '#AAAAAA', '#A9A9A9', '#A8A8A8', '#A6A6A6', '#A52A2A', '#A4D3EE', '#A3A3A3',
            '#A2CD5A', '#A2B5CD', '#A1A1A1', '#A0522D', '#A020F0', '#9FB6CD', '#9F79EE',
            '#9E9E9E', '#9C9C9C', '#9BCD9B', '#9B30FF', '#9AFF9A', '#9ACD32', '#9AC0CD',
            '#9A32CD', '#999999', '#9932CC', '#98FB98', '#98F5FF', '#97FFFF', '#96CDCD',
            '#969696', '#949494', '#9400D3', '#9370DB', '#919191', '#912CEE', '#90EE90',
            '#8FBC8F', '#8F8F8F', '#8EE5EE', '#8E8E8E', '#8E8E38', '#8E388E', '#8DEEEE',
            '#8DB6CD', '#8C8C8C', '#8B8B83', '#8B8B7A', '#8B8B00', '#8B8989', '#8B8970',
            '#8B8878', '#8B8682', '#8B864E', '#8B8386', '#8B8378', '#8B814C', '#8B7E66',
            '#8B7D7B', '#8B7D6B', '#8B7B8B', '#8B795E', '#8B7765', '#8B7500', '#8B7355',
            '#8B6969', '#8B6914', '#8B668B', '#8B6508', '#8B636C', '#8B5F65', '#8B5A2B',
            '#8B5A00', '#8B5742', '#8B4C39', '#8B4789', '#8B475D', '#8B4726', '#8B4513',
            '#8B4500', '#8B3E2F', '#8B3A62', '#8B3A3A', '#8B3626', '#8B2500', '#8B2323',
            '#8B2252', '#8B1C62', '#8B1A1A', '#8B0A50', '#8B008B', '#8B0000', '#8A8A8A',
            '#8A2BE2', '#8968CD', '#87CEFF', '#87CEFA', '#87CEEB', '#878787', '#858585',
            '#848484', '#8470FF', '#838B8B', '#838B83', '#836FFF', '#828282', '#7FFFD4',
            '#7FFF00', '#7F7F7F', '#7EC0EE', '#7D9EC0', '#7D7D7D', '#7D26CD', '#7CFC00',
            '#7CCD7C', '#7B68EE', '#7AC5CD', '#7A8B8B', '#7A7A7A', '#7A67EE', '#7A378B',
            '#79CDCD', '#787878', '#778899', '#76EEC6', '#76EE00', '#757575', '#737373',
            '#71C671', '#7171C6', '#708090', '#707070', '#6E8B3D', '#6E7B8B', '#6E6E6E',
            '#6CA6CD', '#6C7B8B', '#6B8E23', '#6B6B6B', '#6A5ACD', '#698B69', '#698B22',
            '#696969', '#6959CD', '#68838B', '#68228B', '#66CDAA', '#66CD00', '#668B8B',
            '#666666', '#6495ED', '#63B8FF', '#636363', '#616161', '#607B8B', '#5F9EA0',
            '#5E5E5E', '#5D478B', '#5CACEE', '#5C5C5C', '#5B5B5B', '#595959', '#575757',
            '#556B2F', '#555555', '#551A8B', '#54FF9F', '#548B54', '#545454', '#53868B',
            '#528B8B', '#525252', '#515151', '#4F94CD', '#4F4F4F', '#4EEE94', '#4D4D4D',
            '#4B0082', '#4A708B', '#4A4A4A', '#48D1CC', '#4876FF', '#483D8B', '#474747',
            '#473C8B', '#4682B4', '#458B74', '#458B00', '#454545', '#43CD80', '#436EEE',
            '#424242', '#4169E1', '#40E0D0', '#404040', '#3D3D3D', '#3CB371', '#3B3B3B',
            '#3A5FCD', '#388E8E', '#383838', '#36648B', '#363636', '#333333', '#32CD32',
            '#303030', '#2F4F4F', '#2E8B57', '#2E2E2E', '#2B2B2B', '#292929', '#282828',
            '#27408B', '#262626', '#242424', '#228B22', '#218868', '#212121', '#20B2AA',
            '#1F1F1F', '#1E90FF', '#1E1E1E', '#1C86EE', '#1C1C1C', '#1A1A1A', '#191970',
            '#1874CD', '#171717', '#141414', '#121212', '#104E8B', '#0F0F0F', '#0D0D0D',
            '#0A0A0A', '#080808', '#050505', '#030303', '#00FFFF', '#00FF7F', '#00FF00',
            '#00FA9A', '#00F5FF', '#00EEEE', '#00EE76', '#00EE00', '#00E5EE', '#00CED1',
            '#00CDCD', '#00CD66', '#00CD00', '#00C5CD', '#00BFFF', '#00B2EE', '#009ACD',
            '#008B8B', '#008B45', '#008B00', '#00868B', '#00688B', '#006400', '#0000FF',
            '#0000EE', '#0000CD', '#0000AA', '#00008B', '#000080'
        );
        $color_num = rand(0, count($color));
        $color =  $color[$color_num];
        return imagecolorallocate($im, $this->GetRValue($color), $this->GetGValue($color), $this->GetBValue($color));
    }

    //分离颜色RGB
    function GetRValue($col) {
        return hexdec(substr($col, 1, 2));
    }

    function GetGValue($col) {
        return hexdec(substr($col, 3, 2));
    }

    function GetBValue($col) {
        return hexdec(substr($col, 5, 2));
    }

}
