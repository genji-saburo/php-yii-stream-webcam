<?php

namespace backend\controllers;

use Yii;
use common\models\Alert;
use backend\models\AlertSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * AccessController implements abstract class for management user access to the certrain functionality.
 */
abstract class AccessController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * List of actionId which are not controlling by AccessControl
     * @var type 
     */
    protected static $ACCESS_EXCEPTION_ARRAY = ['write', 'skip', 'check', 'close', 'get-worktime', 'image', 'image-refresh', 'settings', 'comment'];

    /**
     * Check if request is ok
     * @param type $action
     * @return type
     * @throws \yii\web\HttpException
     */
    public function beforeAction($action) {

        $actionId = $action->controller->action->id;
        $controllerId = $action->controller->id;

        if (!Yii::$app->user->isGuest && !in_array($actionId, self::$ACCESS_EXCEPTION_ARRAY)) {
            $curUser = Yii::$app->user->getIdentity();

            /*   Allow admin access whatever   */
            if ($curUser->role === \common\models\User::ROLE_ADMIN)
                return parent::beforeAction($action);

            $res = $curUser->isAllowed($actionId, $controllerId);

            if (!$res)
                throw new \yii\web\HttpException(403, "You don't have permissions for this page. Please contact your administrator.");

            return $res && parent::beforeAction($action);
        }
        return parent::beforeAction($action);
    }

}
