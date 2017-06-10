<?php

namespace backend\controllers;

use Yii;
use common\models\Alarm;
use backend\models\AlarmSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Log;
use yii\helpers\Html;
use \Twilio\Jwt\ClientToken;

/**
 * All actions related with calls
 */
class CallController extends \yii\web\Controller {

    const TYPE_AGENT_ALARM = "Created by agent";

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * @inheritdocs
     */
    public function beforeAction($action) {
        \Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Generate Twilio token for the given page
     * @return type
     */
    public function actionToken() {
        $clientToken = new ClientToken(Yii::$app->Yii2Twilio->account_sid, Yii::$app->Yii2Twilio->auth_key);
        $forPage = Yii::$app->request->get('forPage', 'index');
        $applicationSid = 'AP69f9c1e0250a3632d9cbc7c70594101b'; //  TwiML app sid
        $clientToken->allowClientOutgoing($applicationSid);
        //$clientToken->allowClientOutgoing('7083900600');
        //$clientToken->allowClientIncoming(\Yii::$app->params['twillio.from_number']);
        $token = $clientToken->generateToken();
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return ['token' => $token];
    }

    /**
     * Returns answer for the twilio API and tell what to do with call
     * @return type
     */
    public function actionForwardCall() {
        // phone number you've verified with Twilio
        $callerId = \Yii::$app->params['twillio.from_number'];
        // custom parameter from Twilio.Device.connect
        $tocall = \Yii::$app->request->post('phoneNumber', false);
        if ($tocall) {
            $response = '<Response><Dial callerId="' . $callerId . '"><Number>' . $tocall . '</Number></Dial></Response>';
            \Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            $headers = \Yii::$app->response->headers;
            $headers->add('Content-Type', 'text/xml');
            return $response;
        } else {
            throw new \Exception('No phone given');
        }
    }

}
