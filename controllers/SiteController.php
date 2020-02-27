<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\People;
use app\models\Parser;
use app\models\AntiCaptcha;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        if ($post = Yii::$app->request->post('People')) {
            if (!People::findOne(['ppl_itn' => $post['ppl_itn']])) {
                $model = new People;
                $model->load(Yii::$app->request->post());

                if ($model->validate() && $model->save()) {
                    $model->addSuccess('Данные успешно сохранены');
                }
            }
        } elseif ($itn = Yii::$app->request->get('itn')) {
            $model = new People;

            if ($model->validateItn($itn)) {
                $model = People::findOne(['ppl_itn' => $itn]);

                if (empty($model)) {
                    $model = new People;
                    $parser = new Parser();

                    $content = $parser->request('/salyk');
                    $part_link = $parser->getContent('src="/stats/counter?', '"', $content, 'middle');
                    $parser->request("/stats/counter?{$part_link}");

                    $params = array(
                        'do' => 'salyk',
                        'number' => $itn,
                        'code' => $parser->getCode($content),
                        'checksum' => $parser->getContent('<input type="hidden"  name="checksum" value="', '"', $content, "middle"),
                    );

                    $content = $parser->request('/salyk', $params);
                    $result = $parser->getResult($content);

                    if (is_array($result)) {
                        foreach ($result as $key => $value) {
                            $model->setAttribute(People::getFields($key), $value);
                        }
                    } else {
                        $model->addError("Ошибка", $result);
                    }
                }
            }
        }

        return $this->render('index', [
            'model' => !empty($model) ? $model: null,
            'fields' => People::getFields(),
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
