<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Parser is the model behind parser.
 */
class Parser
{
    const PARSED_URL = 'http://serj.ws';
    private $ch;

    public function getContent($p1, $p2, $content, $type = "all")
    {
        $pos1 = strpos($content, $p1);

        if ($type == 'middle') {
            $pos1 += strlen($p1);
        }

        if ($pos1 === false) {
            return '';
        }

        $content = substr($content, $pos1);

        $pos2 = strpos($content, $p2);

        if ($type == 'all') {
            $pos2 += strlen($p2);
        }

        return substr($content, 0, $pos2);
    }

    public function request($page, $params = [])
    {
        $page = str_replace('&amp;', '&', $page);

        $this->ch = curl_init();

        curl_setopt($this->ch, CURLOPT_URL, self::PARSED_URL . $page);
        curl_setopt($this->ch, CURLOPT_USERAGENT, 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36');
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($this->ch, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'] . '/cookie.txt');
        curl_setopt($this->ch, CURLOPT_COOKIEFILE, $_SERVER['DOCUMENT_ROOT'] . '/cookie.txt');


        if ($params) {
            curl_setopt($this->ch, CURLOPT_POST, 1);
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);
        }

        if ($page !== '/salyk') {
            $headers = ['Referer: ' . self::PARSED_URL  . '/salyk'];
            curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        }

        $data = curl_exec($this->ch);

        curl_close($this->ch);

        return $data;
    }

    public function getCode($content) {
        $code = '';
        $antiCaptcha = new AntiCaptcha('7ab1a26549ec3798e80e61e3feaeed7e');

        while ($img = $this->getContent('<img src="/sec.php?', '>', $content)) {
            $page = '/sec.php?' . $this->getContent('<img src="/sec.php?', '"', $img, "middle");
            $content = str_replace($img, '', $content);
            $img = base64_encode($this->request($page));
            $code .= $antiCaptcha->getCode($img);
        }

        return $code;
    }

    public function getResult($content) {
        $info = $this->getContent('<div class="otvetkt">', '</div>', $content, 'middle');

        $result = [];

        if (strpos($info, 'ИИН:') !== false) {
            foreach (explode("<br>", $info) as $line) {
                $item = trim(strip_tags($line));

                if (!empty($line)) {
                    list($key, $value) = explode(': ', $line);

                    if ($key == 'Адрес прописки' && array_key_exists($key, $result) !== false) {
                        $key = 'Адрес проживания';
                    }

                    $result[$key] = trim($value);
                }
            }
        } else {
            return strip_tags($info);
        }

        return $result;
    }
}
