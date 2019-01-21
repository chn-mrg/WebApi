<?php
return array(
	//'配置项'=>'配置值'
    'DB_TYPE'   => 'mysql', // 数据库类型
    'DB_HOST'   => '192.168.2.41', // 服务器地址
    'DB_NAME'   => 'sexweb', // 数据库名
    'DB_USER'   => 'root', // 用户名
    'DB_PWD'    => '123456', // 密码
    'DB_PORT'   => 3306, // 端口
    'DB_PARAMS' =>  array(), // 数据库连接参数
    'DB_PREFIX' => 'sex_', // 数据库表前缀
    'DB_CHARSET'=> 'utf8mb4', // 字符集
    'DB_DEBUG'  =>  false, // 数据库调试模式 开启后可以记录SQL日志
    'DATA_CACHE_TIME' => 0,

    'urlRule'=>array(
        'ResourceUrl'=>'{#ResourceUrl$#}',
    ),

    'S3Client'=>array(
        'version' => 'latest',
        'region' => 'ap-southeast-1',
        'credentials'=>array(
            'key'    => 'AKIAI3LE4QELKIBW2IYQ', #访问秘钥
            'secret' => 'VscHZB7B7swEbK1uHPomFHdBq7tjJCCMl5FK3VY1' #私有访问秘钥
        )
    ),

    'cacheName'=>array(
        'SysLevelCache'=>'SysLevelCache', //等級列表緩存名稱
        'SysConfCache'=>'SysConfCache',  //網站配置緩存名稱
        'LiveGiftCache'=>'LiveGiftCache', //禮物列表緩存名稱
        'FirstCategoryCache'=>'FirstCategoryCache', //一級分類緩存名稱
        'MovieCategoryCache'=>'MovieCategoryCache', //電影分類緩存名稱
        'ImageCategoryCache'=>'ImageCategoryCache', //圖片分類緩存名稱
        'FictionCategoryCache'=>'FictionCategoryCache', //小説分類緩存名稱
        'SysLabelCache'=>'SysLabelCache',//資源標簽緩存名稱
    ),

    'CodeMsg'=>array(
        '200'=>'成功',
        '301'=>'失敗',
        '404'=>'無數據',
        '100000'=>'登錄失敗',
        '100001'=>'賬號密碼錯誤',
        '100002'=>'權限不足',
        '100003'=>'工號重複',
        '100004'=>'管理員賬號重複',
        '100005'=>'無效參數',
        '100006'=>'用戶賬號重複',
        '100007'=>'用戶昵稱重複',
        '100008'=>'該手機號已綁定其他賬號',
        '100009'=>'該郵箱已綁定其他賬號',
        '100010'=>'流名稱重複',
        '100011'=>'用户账号被封',
        '100012'=>'用户未登录',
        '100013'=>'等級未達到10級',
        '100014'=>'發佈動態字數不大於250',
        '100015'=>'發佈評論字數不大於250',
        '100016'=>'發佈回復字數不大於100',
        '100017'=>'餘額不足',
    ),

    'SystemKey'=>array(
        'PUBLIC_KEY'=>'-----BEGIN PUBLIC KEY-----'.
            PHP_EOL .'MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAuhNvXmT93H7RwrwC+sql'.
            PHP_EOL .'oEy5Qv8z0I1MLKRBf/hmdVBhq9fqz9VQm6daYRjpepF6qwx1LtxTDMUANbRAvrBx'.
            PHP_EOL .'yozD5svwH++kJslXfYKyYbHDl3rjMOhUFdDxioGqGGu97/QqCJ9To1aUj66TSkNS'.
            PHP_EOL .'Czu8FrJmUwzCg8IiO3bCOvHSpLBesKEJfqnyMWifrIqR0AopBBaPyg32JLOLFLwt'.
            PHP_EOL .'WNU76dZusOUTut2BShWOZQzHFz9GJlTlGDD3uv4e5h7EJeDyPIPcFY8flM1DQzWs'.
            PHP_EOL .'Hq9OC8YK9F0wFfksPSIig0vBk9VSKHxt5gLL1qa4ZhNrJkvLjXj7j5+xxbeVw1ig'.
            PHP_EOL .'mNUEcwuuQZgj/OzOtldES84e8lq8XHQth3Je8jolBKRdqpfIcvB9ZpSmQDffKgSq'.
            PHP_EOL .'GGFoVXpPQS5NP7MPWQZjKWbmrC7J/u2Xdb0yf8HiR4gALtF0epRAWpk3eFA1QHIO'.
            PHP_EOL .'7/QP61xs0IlUEx3/4yzLK37UGUKHin/kLrGuNUavy/QwyjaN7KwbRgVAvJn1m6rE'.
            PHP_EOL .'4NUr4yR0zvjxXMVgEmW5/f2f6+tkNUK8Qvd7AU+szSx//pVavb5K2m5ByTTuXFTX'.
            PHP_EOL .'yoc3exh88AiCMYw1c1/aNx+hepRcqyqbCHHdGZCl+EtZvn9gW9s0DuoDgnxFwau6'.
            PHP_EOL .'kL5br/BCB0i52xAzGivq3KcCAwEAAQ=='.
            PHP_EOL .'-----END PUBLIC KEY-----',

        'PRIVATE_KEY'=>'-----BEGIN PRIVATE KEY-----'.
            PHP_EOL .'MIIJQQIBADANBgkqhkiG9w0BAQEFAASCCSswggknAgEAAoICAQC6E29eZP3cftHC'.
            PHP_EOL .'vAL6yqWgTLlC/zPQjUwspEF/+GZ1UGGr1+rP1VCbp1phGOl6kXqrDHUu3FMMxQA1'.
            PHP_EOL .'tEC+sHHKjMPmy/Af76QmyVd9grJhscOXeuMw6FQV0PGKgaoYa73v9CoIn1OjVpSP'.
            PHP_EOL .'rpNKQ1ILO7wWsmZTDMKDwiI7dsI68dKksF6woQl+qfIxaJ+sipHQCikEFo/KDfYk'.
            PHP_EOL .'s4sUvC1Y1Tvp1m6w5RO63YFKFY5lDMcXP0YmVOUYMPe6/h7mHsQl4PI8g9wVjx+U'.
            PHP_EOL .'zUNDNawer04Lxgr0XTAV+Sw9IiKDS8GT1VIofG3mAsvWprhmE2smS8uNePuPn7HF'.
            PHP_EOL .'t5XDWKCY1QRzC65BmCP87M62V0RLzh7yWrxcdC2Hcl7yOiUEpF2ql8hy8H1mlKZA'.
            PHP_EOL .'N98qBKoYYWhVek9BLk0/sw9ZBmMpZuasLsn+7Zd1vTJ/weJHiAAu0XR6lEBamTd4'.
            PHP_EOL .'UDVAcg7v9A/rXGzQiVQTHf/jLMsrftQZQoeKf+Qusa41Rq/L9DDKNo3srBtGBUC8'.
            PHP_EOL .'mfWbqsTg1SvjJHTO+PFcxWASZbn9/Z/r62Q1QrxC93sBT6zNLH/+lVq9vkrabkHJ'.
            PHP_EOL .'NO5cVNfKhzd7GHzwCIIxjDVzX9o3H6F6lFyrKpsIcd0ZkKX4S1m+f2Bb2zQO6gOC'.
            PHP_EOL .'fEXBq7qQvluv8EIHSLnbEDMaK+rcpwIDAQABAoICABfNKp65bluJAU3WfM8Vos/5'.
            PHP_EOL .'YG04dalEmazQKeyzmm+BI602hjulfpUaeA4ZgKwD9dvxUP/4gMsOW/OCphF+Ql/1'.
            PHP_EOL .'V42rIoEDR00Kzh0o3aZvdaRnvK3h2fecbXkZEufiyD3sToAh2TH4fjJO01pZeCIl'.
            PHP_EOL .'tu50TXBsHml6KKTQkRG3Iwmb4dDYGH3SQT+esWYBp5sj4ZE7TZM76/NP4Ad79pT5'.
            PHP_EOL .'WZuUqT4JX1e0w7f1P+yfEMxhdJisnU1V3ipWHR+0acSqnHGvMDS1xQVkqCjtsaGP'.
            PHP_EOL .'LP2GjJorXV5CLC15s052H0HItKSnHeuhCe/gDZBJBZi1c0kaY5tAgx3WWNrO+GO8'.
            PHP_EOL .'SN8ZleTAgt4EIp5V3DwiofzvAOlTGCOD9wQQEpnCYVUF9HjWf+dKilRzGrpSGXGE'.
            PHP_EOL .'ciY4acF8qGbEEBmjI4Dfeggcqj/X10lf/yktHobTQmlL04TJrVkHOTmaQt4a5dEj'.
            PHP_EOL .'9inFolqY3NSjYH7afZv7Qmq6/ZLuM51VMzXiiCFJ1LRFtyBcMKGQD5rPBqyLtlnn'.
            PHP_EOL .'qn3sQQzfq2wO0qb6K4BQ8GAZ4hl1Ut79eabAFMshQYW4qdAGvnsmKellkFFqDLTO'.
            PHP_EOL .'OTH97BqEYDqU7NxITsGx6aisccihvad4q01m+l37EA9KrycwzecQ8TQZOglKP5Pi'.
            PHP_EOL .'hy1Z9L07XKF63dauuL/pAoIBAQDqgEWEtZsDtvUAWJQA5/sEAWSuvpxXRBClVfVm'.
            PHP_EOL .'Xwr4V3pKktYpVmo8z7DXGhFjy23igrsI72XZwywEm/Wyqgi01DdL3JiUOEG5XRSD'.
            PHP_EOL .'2xeI3e08EcWjSl0YcjxLGyNUnackn8ICnaNI8lFub7tWC4dfi/pNFSjSsm+GK2bc'.
            PHP_EOL .'DIpXDF3h/Ad+06HSYB0pW7M+R29GU89O9xHIqONnDIPrST4l+ts8fWUymAy+QhBe'.
            PHP_EOL .'ulWK2WCUdfc9jnIZgCBqtzI3TceTQnLz2bay0KC2ceUTLH4QpyqyjKoJ/TV7p5P/'.
            PHP_EOL .'+BB5uSdxTMtc3n0IRGToXe3hiFT5xrg7KbMmTkJK2gLX+yyDAoIBAQDLIqCz3MJb'.
            PHP_EOL .'ZH6nwxslc61ifws+jDsPCJgdXUWJFnKx0teMpvnMKx1fJfWLfLVLKCbw/zwf1raz'.
            PHP_EOL .'2hLP7Knczc00kFjFLBv8gHumASyxIoVLt0O+lkRH1lykVufvGazNrf4m1B7aGHX8'.
            PHP_EOL .'g2y5Bpr/gLAY20ipRpessGbruyV1LEFXFp3pRxr5o+9NEuxBEcCHhO/kpMlEltZc'.
            PHP_EOL .'R4FU1oJzpXlBcBSdXd61V4leLzFSzgdu8BehcjTR1FJyOUpCorCAeoCuFhE9ZqdZ'.
            PHP_EOL .'NpcupYAIfG86TOY/fTaszsUeQFR7ZsDW2RrlM/gUZSXvTBPiUrHK13CgPWNpLMC5'.
            PHP_EOL .'dyUbuvLZMN4NAoIBACbopaBBabkSEFDAYb5Mv2+Is6Xy5onsNz7XpmIX/v/5s8bP'.
            PHP_EOL .'2kz9k58HbvYh0yTVyiO9QT9YSXP6WEFjhz7fy5YFaC9kKMTfGLii4xaFsb/54rUG'.
            PHP_EOL .'1d4kJpI50hs8I36Usfj3sP8yHLerzSsfytuaChomZZ+IlT9wb+S5KtX0Frgeyy0F'.
            PHP_EOL .'3lCC2OTJIc2M846v15y5pzoY8JB0xVaB0xmlC3TNzLaar8HXTLX8zC6LiEoDi2zW'.
            PHP_EOL .'rfE+w1vL0JUkVGastyKN8fjX9OJyj1f2SeYGidxFgGYjxMrngopD4eCkzkcEG7FG'.
            PHP_EOL .'5q9AE0rd3khX3XKAZmkTOdpHLvx6G7HDvpkLifUCggEATMtihZzHvVVYrIOOF7KL'.
            PHP_EOL .'sdjln1fUW/Kbzz4bs5/Q7HkRFBNsDEugZwAqu/kBcNBVKHbVfBPoLkYbJpAKB3em'.
            PHP_EOL .'RGDtrlzml6bbF/8Jrk2mpuE5syuL1LyteVOBi1rhgUBt/K/kmf71W+kziR9+KWm1'.
            PHP_EOL .'KTB9X8FYRejKgAPYPVvlt87NFAvVntri9PhaaJ8VciZHquDBVjTQBULjGfeeiand'.
            PHP_EOL .'FWgR4wxBzbyyKfEdbHiHRuFtjZNndIwYPyZ5dIecwnWNgELHcbcFPSzuKqxWot6o'.
            PHP_EOL .'DhpUUVw890eULcOULLLD5HVfPZdQCiXTGYxhWAZ7QHwCEBIlKvXIRlVMqhcbCBD5'.
            PHP_EOL .'YQKCAQBipO90YH3jRzudGYy1gC6Pnyo0lDAz5HrlgqTZ+v/H2BKOqiq17n6q3gaN'.
            PHP_EOL .'s9HrGvZprJ36zrj2bHTnQF/oiqHxSGSjFrA/pLq/03XCv4is8cRXB6Sm9UKnoAlb'.
            PHP_EOL .'94hMbeIu8cEndfQkQvIqexfjTEdRnIUnQaSah76DybwzE3kAixb09fkqaE6w3dbe'.
            PHP_EOL .'XTVjt6PHoeBuPz43+zDpgxtD3i7m6oHlE2M/PWNV1dkEi/5ksHzveCc96HaLLpON'.
            PHP_EOL .'EZ83B2+8MOyZ6pXSZk6uJA0iKfZFf5G3gSKWaiXaV60o+lhos2spsqOWoGu0rY+d'.
            PHP_EOL .'BcyKxh8x3H05RtUJNK+jQgJhV7HL'.
            PHP_EOL .'-----END PRIVATE KEY-----',
    )
);