<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2018/12/30
 * Time: 15:12
 */

namespace WebApi_v1\Controller;

class AuthController extends MainController
{
    /*
     * 用户登錄
     * 參數：
     * phone/email  手机号/邮箱/账号
     * userpwd 密碼
     */
    public function login(){

        $phone              = I('phone');
        $userpwd            = md5(I('userpwd')); //用户密码

        if(!$phone || !$userpwd) {
            self::returnAjax('100005'); //参数错误
        }

        $UserM              = M('user_list'); //实例化

        //多条件查询
        $map                = array();

        $map['userpwd']     = $userpwd;
        $map['_query']      = 'phone=' .$phone. '&email=' .$phone. '&username=' .$phone .'&_logic=or';

        $UserInfo           = $UserM->where($map)->find();

        if($UserInfo) {
            //判断用户状态 0-不可發言，1-正常，2-已封號 只有用户未被封号才可以登录成功
            if($UserInfo['state'] != 2) {
                $UserToken   = md5(time() . mt_rand(100000, 99999)); //用户token

                if($UserM->where(array('user_id' => $UserInfo['user_id']))->save(array('user_token' => $UserToken))) { //更新用户token
                    //session存储用户信息
                    $_SESSION['user']   = array(
                        'user_id'      => $UserInfo['user_id'],
                        'user_token'   => $UserToken
                    );
                    self::returnAjax(200, array('user_token' => $UserToken,'user_id'=>$UserInfo['user_id']));
                }

                $_SESSION['user'] = false;
                self::returnAjax(100000); //登录失败(用户token更新失败)
            }

            $_SESSION['user'] = false;
            self::returnAjax(100011); //用户已被封号
        }

        $_SESSION['user'] = false;
        self::returnAjax(100000); //登录失败 账号(邮箱)或密码错误
    }

    /*
     * 退出登錄
     */
    public function outLogin() {
        $_SESSION['user'] = false;
        self::returnAjax(200);
    }

    /*
 *  注册
 */
    public function Registered(){

    }

    /*
     * 发送短信
     */
    public function RegSendMsg(){
        $phone = I('phone');
        $code = I('code');
        if($phone && $code){
            if($code && $code==$_SESSION['regTestCode']){
                //self::SendMsg($phone,"測試");
                self::returnAjax(200,array('data'=>'發送短信'));
            }
            self::returnAjax(301,array('data'=>'圖形驗證碼錯誤'));
        }
        self::returnAjax(301,array('data'=>'手機號與驗證碼不能爲空'));
    }

    public function regImgCode(){
        $string = "abcdefghijklmnpqrstuvwxyz123456789";
        $str = "";
        $code = "";
        for($i=0;$i<4;$i++){
            $pos = mt_rand(0,33);
            $str .= $string{$pos}." ";
            $code .= $string{$pos};
        }
        $_SESSION['regTestCode'] = $code;
        $img_handle = Imagecreate(120, 30);  //图片大小80X20
        $back_color = ImageColorAllocate($img_handle, 255, 255, 255); //背景颜色（白色）
        imagecolortransparent($img_handle,$back_color);
        $txt_color = ImageColorAllocate($img_handle, 60,60, 60);  //文本颜色（黑色）
        Imagefill($img_handle, 0, 0, $back_color);             //填充图片背景色
        //ImageString($img_handle, 5, 10, 0, $str, $txt_color);//水平填充一行字符串
        @imagefttext($img_handle, 25 , 0, 0, 25, $txt_color, $_SERVER['DOCUMENT_ROOT'].'/api/Public/fonts/simsun.ttf',$str);
        ob_clean();   // ob_clean()清空输出缓存区
        header("Content-type: image/png"); //生成验证码图片
        Imagepng($img_handle);//显示图片
    }

    /*
     * 獲取國家代碼
     */
    public function CountryCode(){
        $json = array(
            array("cn"=>"阿富汗","en"=>"Afghanistan","phone_code"=>"93"),
            array("cn"=>"阿爾巴尼亞","en"=>"Albania","phone_code"=>"355"),
            array("cn"=>"阿爾及利亞","en"=>"Algeria","phone_code"=>"213"),
            array("cn"=>"美屬薩摩亞","en"=>"American Samoa","phone_code"=>"684"),
            array("cn"=>"安道爾","en"=>"Andorra","phone_code"=>"376"),
            array("cn"=>"安哥拉","en"=>"Angola","phone_code"=>"244"),
            array("cn"=>"安圭拉","en"=>"Anguilla","phone_code"=>"1264"),
            array("cn"=>"南極洲","en"=>"Antarctica","phone_code"=>"672"),
            array("cn"=>"安提瓜和巴布達","en"=>"Antigua and Barbuda","phone_code"=>"1268"),
            array("cn"=>"阿根廷","en"=>"Argentina","phone_code"=>"54"),
            array("cn"=>"亞美尼亞","en"=>"Armenia","phone_code"=>"374"),
            array("cn"=>"阿魯巴","en"=>"Aruba","phone_code"=>"297"),
            array("cn"=>"澳大利亞","en"=>"Australia","phone_code"=>"61"),
            array("cn"=>"奧地利","en"=>"Austria","phone_code"=>"43"),
            array("cn"=>"阿塞拜疆","en"=>"Azerbaijan","phone_code"=>"994"),
            array("cn"=>"巴林","en"=>"Bahrain","phone_code"=>"973"),
            array("cn"=>"孟加拉國","en"=>"Bangladesh","phone_code"=>"880"),
            array("cn"=>"巴巴多斯","en"=>"Barbados","phone_code"=>"1246"),
            array("cn"=>"白俄羅斯","en"=>"Belarus","phone_code"=>"375"),
            array("cn"=>"比利時","en"=>"Belgium","phone_code"=>"32"),
            array("cn"=>"伯利茲","en"=>"Belize","phone_code"=>"501"),
            array("cn"=>"貝甯","en"=>"Benin","phone_code"=>"229"),
            array("cn"=>"百慕大","en"=>"Bermuda","phone_code"=>"1441"),
            array("cn"=>"不丹","en"=>"Bhutan","phone_code"=>"975"),
            array("cn"=>"玻利維亞","en"=>"Bolivia","phone_code"=>"591"),
            array("cn"=>"波黑","en"=>"Bosnia and Herzegovina","phone_code"=>"387"),
            array("cn"=>"博茨瓦納","en"=>"Botswana","phone_code"=>"267"),
            array("cn"=>"巴西","en"=>"Brazil","phone_code"=>"55"),
            array("cn"=>"英屬維爾京群島","en"=>"British Virgin Islands","phone_code"=>"1284"),
            array("cn"=>"文萊","en"=>"Brunei Darussalam","phone_code"=>"673"),
            array("cn"=>"保加利亞","en"=>"Bulgaria","phone_code"=>"359"),
            array("cn"=>"布基納法索","en"=>"Burkina Faso","phone_code"=>"226"),
            array("cn"=>"緬甸","en"=>"Burma","phone_code"=>"95"),
            array("cn"=>"布隆迪","en"=>"Burundi","phone_code"=>"257"),
            array("cn"=>"柬埔寨","en"=>"Cambodia","phone_code"=>"855"),
            array("cn"=>"喀麥隆","en"=>"Cameroon","phone_code"=>"237"),
            array("cn"=>"加拿大","en"=>"Canada","phone_code"=>"1"),
            array("cn"=>"佛得角","en"=>"Cape Verde","phone_code"=>"238"),
            array("cn"=>"開曼群島","en"=>"Cayman Islands","phone_code"=>"1345"),
            array("cn"=>"中非","en"=>"Central African Republic","phone_code"=>"236"),
            array("cn"=>"乍得","en"=>"Chad","phone_code"=>"235"),
            array("cn"=>"智利","en"=>"Chile","phone_code"=>"56"),
            array("cn"=>"中華人民共和國","en"=>"China","phone_code"=>"86"),
            array("cn"=>"聖誕島","en"=>"Christmas Island","phone_code"=>"61"),
            array("cn"=>"科科斯（基林）群島","en"=>"Cocos (Keeling) Islands","phone_code"=>"61"),
            array("cn"=>"哥倫比亞","en"=>"Colombia","phone_code"=>"57"),
            array("cn"=>"科摩羅","en"=>"Comoros","phone_code"=>"269"),
            array("cn"=>"剛果（金）","en"=>"Democratic Republic of the Congo","phone_code"=>"243"),
            array("cn"=>"剛果（布）","en"=>"Republic of the Congo","phone_code"=>"242"),
            array("cn"=>"庫克群島","en"=>"Cook Islands","phone_code"=>"682"),
            array("cn"=>"哥斯達黎加","en"=>"Costa Rica","phone_code"=>"506"),
            array("cn"=>"科特迪瓦","en"=>"Cote d'Ivoire","phone_code"=>"225"),
            array("cn"=>"克羅地亞","en"=>"Croatia","phone_code"=>"385"),
            array("cn"=>"古巴","en"=>"Cuba","phone_code"=>"53"),
            array("cn"=>"塞浦路斯","en"=>"Cyprus","phone_code"=>"357"),
            array("cn"=>"捷克","en"=>"Czech Republic","phone_code"=>"420"),
            array("cn"=>"丹麥","en"=>"Denmark","phone_code"=>"45"),
            array("cn"=>"吉布提","en"=>"Djibouti","phone_code"=>"253"),
            array("cn"=>"多米尼克","en"=>"Dominica","phone_code"=>"1767"),
            array("cn"=>"多米尼加","en"=>"Dominican Republic","phone_code"=>"1809"),
            array("cn"=>"厄瓜多爾","en"=>"Ecuador","phone_code"=>"593"),
            array("cn"=>"埃及","en"=>"Egypt","phone_code"=>"20"),
            array("cn"=>"薩爾瓦多","en"=>"El Salvador","phone_code"=>"503"),
            array("cn"=>"赤道幾內亞","en"=>"Equatorial Guinea","phone_code"=>"240"),
            array("cn"=>"厄立特裏亞","en"=>"Eritrea","phone_code"=>"291"),
            array("cn"=>"愛沙尼亞","en"=>"Estonia","phone_code"=>"372"),
            array("cn"=>"埃塞俄比亞","en"=>"Ethiopia","phone_code"=>"251"),
            array("cn"=>"福克蘭群島（馬爾維納斯）","en"=>"Falkland Islands (Islas Malvinas)","phone_code"=>"500"),
            array("cn"=>"法羅群島","en"=>"Faroe Islands","phone_code"=>"298"),
            array("cn"=>"斐濟","en"=>"Fiji","phone_code"=>"679"),
            array("cn"=>"芬蘭","en"=>"Finland","phone_code"=>"358"),
            array("cn"=>"法國","en"=>"France","phone_code"=>"33"),
            array("cn"=>"法屬圭亞那","en"=>"French Guiana","phone_code"=>"594"),
            array("cn"=>"法屬波利尼西亞","en"=>"French Polynesia","phone_code"=>"689"),
            array("cn"=>"加蓬","en"=>"Gabon","phone_code"=>"241"),
            array("cn"=>"格魯吉亞","en"=>"Georgia","phone_code"=>"995"),
            array("cn"=>"德國","en"=>"Germany","phone_code"=>"49"),
            array("cn"=>"加納","en"=>"Ghana","phone_code"=>"233"),
            array("cn"=>"直布羅陀","en"=>"Gibraltar","phone_code"=>"350"),
            array("cn"=>"希臘","en"=>"Greece","phone_code"=>"30"),
            array("cn"=>"格陵蘭","en"=>"Greenland","phone_code"=>"299"),
            array("cn"=>"格林納達","en"=>"Grenada","phone_code"=>"1473"),
            array("cn"=>"瓜德羅普","en"=>"Guadeloupe","phone_code"=>"590"),
            array("cn"=>"關島","en"=>"Guam","phone_code"=>"1671"),
            array("cn"=>"危地馬拉","en"=>"Guatemala","phone_code"=>"502"),
            array("cn"=>"根西島","en"=>"Guernsey","phone_code"=>"1481"),
            array("cn"=>"幾內亞","en"=>"Guinea","phone_code"=>"224"),
            array("cn"=>"幾內亞比紹","en"=>"Guinea-Bissau","phone_code"=>"245"),
            array("cn"=>"圭亞那","en"=>"Guyana","phone_code"=>"592"),
            array("cn"=>"海地","en"=>"Haiti","phone_code"=>"509"),
            array("cn"=>"梵蒂岡","en"=>"Holy See (Vatican City)","phone_code"=>"379"),
            array("cn"=>"洪都拉斯","en"=>"Honduras","phone_code"=>"504"),
            array("cn"=>"中國香港","en"=>"Hong Kong (SAR)","phone_code"=>"852"),
            array("cn"=>"匈牙利","en"=>"Hungary","phone_code"=>"36"),
            array("cn"=>"冰島","en"=>"Iceland","phone_code"=>"354"),
            array("cn"=>"印度","en"=>"India","phone_code"=>"91"),
            array("cn"=>"印度尼西亞","en"=>"Indonesia","phone_code"=>"62"),
            array("cn"=>"伊朗","en"=>"Iran","phone_code"=>"98"),
            array("cn"=>"伊拉克","en"=>"Iraq","phone_code"=>"964"),
            array("cn"=>"愛爾蘭","en"=>"Ireland","phone_code"=>"353"),
            array("cn"=>"以色列","en"=>"Israel","phone_code"=>"972"),
            array("cn"=>"意大利","en"=>"Italy","phone_code"=>"39"),
            array("cn"=>"牙買加","en"=>"Jamaica","phone_code"=>"1876"),
            array("cn"=>"日本","en"=>"Japan","phone_code"=>"81"),
            array("cn"=>"約旦","en"=>"Jordan","phone_code"=>"962"),
            array("cn"=>"哈薩克斯坦","en"=>"Kazakhstan","phone_code"=>"73"),
            array("cn"=>"肯尼亞","en"=>"Kenya","phone_code"=>"254"),
            array("cn"=>"基裏巴斯","en"=>"Kiribati","phone_code"=>"686"),
            array("cn"=>"朝鮮","en"=>"North Korea","phone_code"=>"850"),
            array("cn"=>"韓國","en"=>"South Korea","phone_code"=>"82"),
            array("cn"=>"科威特","en"=>"Kuwait","phone_code"=>"965"),
            array("cn"=>"吉爾吉斯斯坦","en"=>"Kyrgyzstan","phone_code"=>"996"),
            array("cn"=>"老撾","en"=>"Laos","phone_code"=>"856"),
            array("cn"=>"拉脫維亞","en"=>"Latvia","phone_code"=>"371"),
            array("cn"=>"黎巴嫩","en"=>"Lebanon","phone_code"=>"961"),
            array("cn"=>"萊索托","en"=>"Lesotho","phone_code"=>"266"),
            array("cn"=>"利比裏亞","en"=>"Liberia","phone_code"=>"231"),
            array("cn"=>"利比亞","en"=>"Libya","phone_code"=>"218"),
            array("cn"=>"列支敦士登","en"=>"Liechtenstein","phone_code"=>"423"),
            array("cn"=>"立陶宛","en"=>"Lithuania","phone_code"=>"370"),
            array("cn"=>"盧森堡","en"=>"Luxembourg","phone_code"=>"352"),
            array("cn"=>"中國澳門","en"=>"Macao","phone_code"=>"853"),
            array("cn"=>"前南馬其頓","en"=>"The Former Yugoslav Republic of Macedonia","phone_code"=>"389"),
            array("cn"=>"馬達加斯加","en"=>"Madagascar","phone_code"=>"261"),
            array("cn"=>"馬拉維","en"=>"Malawi","phone_code"=>"265"),
            array("cn"=>"馬來西亞","en"=>"Malaysia","phone_code"=>"60"),
            array("cn"=>"馬爾代夫","en"=>"Maldives","phone_code"=>"960"),
            array("cn"=>"馬裏","en"=>"Mali","phone_code"=>"223"),
            array("cn"=>"馬耳他","en"=>"Malta","phone_code"=>"356"),
            array("cn"=>"馬紹爾群島","en"=>"Marshall Islands","phone_code"=>"692"),
            array("cn"=>"馬提尼克","en"=>"Martinique","phone_code"=>"596"),
            array("cn"=>"毛裏塔尼亞","en"=>"Mauritania","phone_code"=>"222"),
            array("cn"=>"毛裏求斯","en"=>"Mauritius","phone_code"=>"230"),
            array("cn"=>"馬約特","en"=>"Mayotte","phone_code"=>"269"),
            array("cn"=>"墨西哥","en"=>"Mexico","phone_code"=>"52"),
            array("cn"=>"密克羅尼西亞","en"=>"Federated States of Micronesia","phone_code"=>"691"),
            array("cn"=>"摩爾多瓦","en"=>"Moldova","phone_code"=>"373"),
            array("cn"=>"摩納哥","en"=>"Monaco","phone_code"=>"377"),
            array("cn"=>"蒙古","en"=>"Mongolia","phone_code"=>"976"),
            array("cn"=>"蒙特塞拉特","en"=>"Montserrat","phone_code"=>"1664"),
            array("cn"=>"摩洛哥","en"=>"Morocco","phone_code"=>"212"),
            array("cn"=>"莫桑比克","en"=>"Mozambique","phone_code"=>"258"),
            array("cn"=>"納米尼亞","en"=>"Namibia","phone_code"=>"264"),
            array("cn"=>"瑙魯","en"=>"Nauru","phone_code"=>"674"),
            array("cn"=>"尼泊爾","en"=>"Nepal","phone_code"=>"977"),
            array("cn"=>"荷蘭","en"=>"Netherlands","phone_code"=>"31"),
            array("cn"=>"荷屬安的列斯","en"=>"Netherlands Antilles","phone_code"=>"599"),
            array("cn"=>"新喀裏多尼亞","en"=>"New Caledonia","phone_code"=>"687"),
            array("cn"=>"新西蘭","en"=>"New Zealand","phone_code"=>"64"),
            array("cn"=>"尼加拉瓜","en"=>"Nicaragua","phone_code"=>"505"),
            array("cn"=>"尼日爾","en"=>"Niger","phone_code"=>"227"),
            array("cn"=>"尼日利亞","en"=>"Nigeria","phone_code"=>"234"),
            array("cn"=>"紐埃","en"=>"Niue","phone_code"=>"683"),
            array("cn"=>"諾福克島","en"=>"Norfolk Island","phone_code"=>"6723"),
            array("cn"=>"北馬裏亞納","en"=>"Northern Mariana Islands","phone_code"=>"1"),
            array("cn"=>"挪威","en"=>"Norway","phone_code"=>"47"),
            array("cn"=>"阿曼","en"=>"Oman","phone_code"=>"968"),
            array("cn"=>"巴基斯坦","en"=>"Pakistan","phone_code"=>"92"),
            array("cn"=>"帕勞","en"=>"Palau","phone_code"=>"680"),
            array("cn"=>"巴拿馬","en"=>"Panama","phone_code"=>"507"),
            array("cn"=>"巴布亞新幾內亞","en"=>"Papua New Guinea","phone_code"=>"675"),
            array("cn"=>"巴拉圭","en"=>"Paraguay","phone_code"=>"595"),
            array("cn"=>"秘魯","en"=>"Peru","phone_code"=>"51"),
            array("cn"=>"菲律賓","en"=>"Philippines","phone_code"=>"63"),
            array("cn"=>"波蘭","en"=>"Poland","phone_code"=>"48"),
            array("cn"=>"葡萄牙","en"=>"Portugal","phone_code"=>"351"),
            array("cn"=>"波多黎各","en"=>"Puerto Rico","phone_code"=>"1809"),
            array("cn"=>"卡塔爾","en"=>"Qatar","phone_code"=>"974"),
            array("cn"=>"留尼汪","en"=>"Reunion","phone_code"=>"262"),
            array("cn"=>"羅馬尼亞","en"=>"Romania","phone_code"=>"40"),
            array("cn"=>"俄羅斯","en"=>"Russia","phone_code"=>"7"),
            array("cn"=>"盧旺達","en"=>"Rwanda","phone_code"=>"250"),
            array("cn"=>"聖赫勒拿","en"=>"Saint Helena","phone_code"=>"290"),
            array("cn"=>"聖基茨和尼維斯","en"=>"Saint Kitts and Nevis","phone_code"=>"1869"),
            array("cn"=>"聖盧西亞","en"=>"Saint Lucia","phone_code"=>"1758"),
            array("cn"=>"聖皮埃爾和密克隆","en"=>"Saint Pierre and Miquelon","phone_code"=>"508"),
            array("cn"=>"聖文森特和格林納丁斯","en"=>"Saint Vincent and the Grenadines","phone_code"=>"1784"),
            array("cn"=>"薩摩亞","en"=>"Samoa","phone_code"=>"685"),
            array("cn"=>"聖馬力諾","en"=>"San Marino","phone_code"=>"378"),
            array("cn"=>"聖多美和普林西比","en"=>"Sao Tome and Principe","phone_code"=>"239"),
            array("cn"=>"沙特阿拉伯","en"=>"Saudi Arabia","phone_code"=>"966"),
            array("cn"=>"塞內加爾","en"=>"Senegal","phone_code"=>"221"),
            array("cn"=>"塞爾維亞和黑山","en"=>"Serbia and Montenegro","phone_code"=>"381"),
            array("cn"=>"塞舌爾","en"=>"Seychelles","phone_code"=>"248"),
            array("cn"=>"塞拉利","en"=>"Sierra Leone","phone_code"=>"232"),
            array("cn"=>"新加坡","en"=>"Singapore","phone_code"=>"65"),
            array("cn"=>"斯洛伐克","en"=>"Slovakia","phone_code"=>"421"),
            array("cn"=>"斯洛文尼亞","en"=>"Slovenia","phone_code"=>"386"),
            array("cn"=>"所羅門群島","en"=>"Solomon Islands","phone_code"=>"677"),
            array("cn"=>"索馬裏","en"=>"Somalia","phone_code"=>"252"),
            array("cn"=>"南非","en"=>"South Africa","phone_code"=>"27"),
            array("cn"=>"西班牙","en"=>"Spain","phone_code"=>"34"),
            array("cn"=>"斯裏蘭卡","en"=>"Sri Lanka","phone_code"=>"94"),
            array("cn"=>"蘇丹","en"=>"Sudan","phone_code"=>"249"),
            array("cn"=>"蘇裏南","en"=>"Suriname","phone_code"=>"597"),
            array("cn"=>"斯瓦爾巴島和揚馬延島","en"=>"Svalbard","phone_code"=>"47"),
            array("cn"=>"斯威士蘭","en"=>"Swaziland","phone_code"=>"268"),
            array("cn"=>"瑞典","en"=>"Sweden","phone_code"=>"46"),
            array("cn"=>"瑞士","en"=>"Switzerland","phone_code"=>"41"),
            array("cn"=>"敘利亞","en"=>"Syria","phone_code"=>"963"),
            array("cn"=>"中華民國","en"=>"Taiwan","phone_code"=>"886"),
            array("cn"=>"塔吉克斯坦","en"=>"Tajikistan","phone_code"=>"992"),
            array("cn"=>"坦桑尼亞","en"=>"Tanzania","phone_code"=>"255"),
            array("cn"=>"泰國","en"=>"Thailand","phone_code"=>"66"),
            array("cn"=>"巴哈馬","en"=>"The Bahamas","phone_code"=>"1242"),
            array("cn"=>"岡比亞","en"=>"The Gambia","phone_code"=>"220"),
            array("cn"=>"多哥","en"=>"Togo","phone_code"=>"228"),
            array("cn"=>"托克勞","en"=>"Tokelau","phone_code"=>"690"),
            array("cn"=>"湯加","en"=>"Tonga","phone_code"=>"676"),
            array("cn"=>"特立尼達和多巴哥","en"=>"Trinidad and Tobago","phone_code"=>"1868"),
            array("cn"=>"突尼斯","en"=>"Tunisia","phone_code"=>"216"),
            array("cn"=>"土耳其","en"=>"Turkey","phone_code"=>"90"),
            array("cn"=>"土庫曼斯坦","en"=>"Turkmenistan","phone_code"=>"993"),
            array("cn"=>"特克斯和凱科斯群島","en"=>"Turks and Caicos Islands","phone_code"=>"1649"),
            array("cn"=>"圖瓦盧","en"=>"Tuvalu","phone_code"=>"688"),
            array("cn"=>"烏幹達","en"=>"Uganda","phone_code"=>"256"),
            array("cn"=>"烏克蘭","en"=>"Ukraine","phone_code"=>"380"),
            array("cn"=>"阿拉伯聯合酋長國","en"=>"United Arab Emirates","phone_code"=>"971"),
            array("cn"=>"英國","en"=>"United Kingdom","phone_code"=>"44"),
            array("cn"=>"美國","en"=>"United States","phone_code"=>"1"),
            array("cn"=>"烏拉圭","en"=>"Uruguay","phone_code"=>"598"),
            array("cn"=>"烏茲別克斯坦","en"=>"Uzbekistan","phone_code"=>"998"),
            array("cn"=>"瓦努阿圖","en"=>"Vanuatu","phone_code"=>"678"),
            array("cn"=>"委內瑞拉","en"=>"Venezuela","phone_code"=>"58"),
            array("cn"=>"越南","en"=>"Vietnam","phone_code"=>"84"),
            array("cn"=>"美屬維爾京群島","en"=>"Virgin Islands","phone_code"=>"1340"),
            array("cn"=>"瓦利斯和富圖納","en"=>"Wallis and Futuna","phone_code"=>"681"),
            array("cn"=>"也門","en"=>"Yemen","phone_code"=>"967"),
            array("cn"=>"贊比亞","en"=>"Zambia","phone_code"=>"260"),
            array("cn"=>"津巴布韋","en"=>"Zimbabwe","phone_code"=>"263")
        );
        self::returnAjax(200,array('List'=>$json,'mycode'=>'86'));
    }

}