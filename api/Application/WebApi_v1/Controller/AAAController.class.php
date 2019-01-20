<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2018/12/29
 * Time: 17:35
 */

namespace WebApi_v1\Controller;


class AAAController extends MainController
{

    public function ArrayToM3u8($array){
        $m3u8 = "#EXTM3U" .
            PHP_EOL . "#EXT-X-VERSION:3" .
            PHP_EOL . "#EXT-X-MEDIA-SEQUENCE:0" .
            PHP_EOL . "#EXT-X-ALLOW-CACHE:YES" .
            PHP_EOL . "#EXT-X-TARGETDURATION:" . $array['targetduration'];
        foreach ($array['ts_list'] as $k => $v) {
            $v = (array)$v;
            $m3u8 = $m3u8 . PHP_EOL . "#EXTINF:" . $v['ts_time'] . "," .
                PHP_EOL . self::ResourceAwsS3Url((C('urlRule'))['ResourceUrl'] . "/" . $v['ts_path']);

        }
        $m3u8 = $m3u8 . PHP_EOL . "#EXT-X-ENDLIST";
        return $m3u8;
    }

    public function PlayVideo()
    {
        $request = (array)json_decode('{"targetduration":"13","ts_list":[{"ts_time":"6.320000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052000.ts"},{"ts_time":"6.320000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052001.ts"},{"ts_time":"3.040000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052002.ts"},{"ts_time":"4.440000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052003.ts"},{"ts_time":"6.080000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052004.ts"},{"ts_time":"5.360000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052005.ts"},{"ts_time":"4.320000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052006.ts"},{"ts_time":"5.000000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052007.ts"},{"ts_time":"6.200000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052008.ts"},{"ts_time":"2.920000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052009.ts"},{"ts_time":"5.160000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052010.ts"},{"ts_time":"6.960000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052011.ts"},{"ts_time":"3.720000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052012.ts"},{"ts_time":"4.560000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052013.ts"},{"ts_time":"5.680000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052014.ts"},{"ts_time":"6.760000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052015.ts"},{"ts_time":"3.640000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052016.ts"},{"ts_time":"5.520000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052017.ts"},{"ts_time":"3.720000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052018.ts"},{"ts_time":"4.880000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052019.ts"},{"ts_time":"6.760000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052020.ts"},{"ts_time":"4.800000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052021.ts"},{"ts_time":"3.480000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052022.ts"},{"ts_time":"5.280000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052023.ts"},{"ts_time":"7.720000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052024.ts"},{"ts_time":"1.640000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052025.ts"},{"ts_time":"7.240000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052026.ts"},{"ts_time":"3.320000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052027.ts"},{"ts_time":"5.040000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052028.ts"},{"ts_time":"5.200000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052029.ts"},{"ts_time":"4.080000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052030.ts"},{"ts_time":"6.600000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052031.ts"},{"ts_time":"4.280000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052032.ts"},{"ts_time":"4.000000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052033.ts"},{"ts_time":"6.040000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052034.ts"},{"ts_time":"6.600000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052035.ts"},{"ts_time":"5.160000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052036.ts"},{"ts_time":"3.000000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052037.ts"},{"ts_time":"5.800000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052038.ts"},{"ts_time":"4.520000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052039.ts"},{"ts_time":"4.960000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052040.ts"},{"ts_time":"4.880000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052041.ts"},{"ts_time":"8.000000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052042.ts"},{"ts_time":"2.040000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052043.ts"},{"ts_time":"8.840000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052044.ts"},{"ts_time":"1.560000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052045.ts"},{"ts_time":"4.480000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052046.ts"},{"ts_time":"12.880000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052047.ts"},{"ts_time":"8.560000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052048.ts"},{"ts_time":"1.960000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052049.ts"},{"ts_time":"4.600000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052050.ts"},{"ts_time":"5.440000","ts_path":"Video\/System\/20181229\/2018122922423523751052\/2018122922423523751052051.ts"}]}');

    }

}