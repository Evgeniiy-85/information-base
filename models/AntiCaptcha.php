<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Parser is the model behind parser.
 */
class AntiCaptcha extends Model
{
    const CREATE_TASK_URL = 'https://api.anti-captcha.com/createTask';
    const GET_TASK_URL = 'https://api.anti-captcha.com/getTaskResult';

    private $siteKey;

    public function __construct($siteKey)
    {
        $this->siteKey = $siteKey;
    }

    public function createTask($image_base64) {
        $params = array (
            "clientKey" => $this->siteKey,
            "task" => array (
                "type" => "ImageToTextTask",
                "body" => $image_base64,
                "phrase" => false,
                "case" => false,
                "numeric" => false,
                "math" => 0,
                "minLength" => 0,
                "maxLength" => 0
            )
        );

        return $this->getResult(self::CREATE_TASK_URL, $params);
    }

    public function getTaskResult($taskId) {
        $params = array (
            "clientKey" => $this->siteKey,
            "taskId" => $taskId
        );

        return $this->getResult(self::GET_TASK_URL, $params);
    }

    public function getResult($url, $params) {
        $result = file_get_contents($url, false, stream_context_create(array(
            'http' => array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => json_encode($params)
            )
        )));

        return json_decode($result, true);
    }

    public function getCode($img)
    {
        $code = '';
        $antiCaptcha = new AntiCaptcha('7ab1a26549ec3798e80e61e3feaeed7e');

        $task = $antiCaptcha->createTask($img);

        if (!$task['errorId']) {
            $count = 0;

            do {
                $result = $antiCaptcha->getTaskResult($task['taskId']);

                if ($result['status'] == 'ready') {
                    $code .= trim($result['solution']['text']);
                }

                sleep(5);
            } while ($result['status'] == 'processing' && $count++ < 10);
        }

        return $code;
    }
}
