<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use common\models\User;
use common\models\Log;
use \common\models\Alert;
use common\models\Camera;
use common\models\CameraLog;
use yii\helpers\Html;
use common\models\UserRestriction;

/**
 * Site controller
 */
class SettingsController extends AccessController {

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex() {
        $dataProvider = new \yii\data\ActiveDataProvider(['query' => UserRestriction::find()]);
        return $this->render('index', compact('dataProvider'));
    }

    /**
     * Add new user restriction
     * @return type
     */
    public function actionAddUserRestriction() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $model = new UserRestriction();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return ['result' => true];
        } else {
            return ['result' => false, 'errors' => $model->errors];
        }
    }

    /**
     * Removes restriction with given id
     * @param type $id
     * @return type
     */
    public function actionDeleteUserRestriction($id) {
        $model = UserRestriction::findOne($id);
        if ($model && $model->delete())
            return ['result' => true];
        else
            return ['result' => false];
    }

}
