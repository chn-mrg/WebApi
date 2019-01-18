<?php
/**
 * Created by PhpStorm.
 * User: chnmrg
 * Date: 2018/11/1
 * Time: 2:43
 */

namespace Rsa;


class Rsa {
    private $PUBLIC_KEY;
    private $PRIVATE_KEY;
    public function __construct()
    {
        $this->PUBLIC_KEY = (C('SystemKey'))['PUBLIC_KEY'];
        $this->PRIVATE_KEY= (C('SystemKey'))['PRIVATE_KEY'];
    }

    /**
     * 获取私钥
     * @return bool|resource
     */
    private function getPrivateKey()
    {
        $privKey = $this->PRIVATE_KEY;
        return openssl_pkey_get_private($privKey);
    }

    /**
     * 获取公钥
     * @return bool|resource
     */
    private function getPublicKey()
    {
        $publicKey = $this->PUBLIC_KEY;
        return openssl_pkey_get_public($publicKey);
    }

    /**
     * 私钥加密
     * @param string $data
     * @return null|string
     */
    public function privEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_private_encrypt($data,$encrypted,$this->getPrivateKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * 公钥加密
     * @param string $data
     * @return null|string
     */
    public function publicEncrypt($data = '')
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_public_encrypt($data,$encrypted,$this->getPublicKey()) ? base64_encode($encrypted) : null;
    }

    /**
     * 私钥解密
     * @param string $encrypted
     * @return null
     */
    public function privDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, $this->getPrivateKey())) ? $decrypted : null;
    }

    /**
     * 公钥解密
     * @param string $encrypted
     * @return null
     */
    public function publicDecrypt($encrypted = '')
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, $this->getPublicKey())) ? $decrypted : null;
    }
}