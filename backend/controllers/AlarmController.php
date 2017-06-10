<?php

namespace backend\controllers;

use Yii;
use common\models\Alarm;
use backend\models\AlarmSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use common\models\Log;
use yii\helpers\Html;

/**
 * AlarmController implements the CRUD actions for Alarm model.
 */
class AlarmController extends AccessController {

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
     * Lists all Alarm models.
     * @return mixed
     */
    public function actionIndex() {
        $curUser = Yii::$app->user->getIdentity();

        if ($curUser->role === \common\models\User::ROLE_ADMIN) {
            $searchModel = new AlarmSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        } else {
            $searchModel = new AlarmSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams, $curUser->id);
        }

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Alarm model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Alarm model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new Alarm();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Alarm model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Alarm model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Alarm model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Alarm the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Alarm::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Initialize alarm for the certain property
     * 
     * @return type
     */
    public function actionAddAlarm($alertId) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $type = \Yii::$app->getRequest()->post('type', '');
        $details = \Yii::$app->getRequest()->post('details', '');
        $alarm = new Alarm();
        if (($alert = \common\models\Alert::findOne($alertId)) && $type && !Yii::$app->user->isGuest) {
            $alarm->type = $type;
            $alarm->alert_id = $alertId;
            $alarm->details = $details;
            $alarm->user_id = Yii::$app->user->id;
            if ($alarm->save()) {
                Log::add(Log::LOG_ALARM_USER_INIT, '{}', $alert->camera->property_id, $alert->camera_id, $alert->id);

                return ['result' => true];
            }
        }
        return ['result' => false];
    }

    /**
     * Returns JSON data to display alarm list
     * 
     * @return type
     */
    public function actionGetAlarmList() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $alarms = Alarm::getActiveAlarms();
        $html = "";
        foreach ($alarms->each() as $alarm) {
            $html .= Html::tag('li', Html::tag('div', Html::tag('div', (isset($alarm->alert->camera->property->name) ? ucfirst($alarm->alert->camera->property->name) : $alarm->alert->camera->name) . ' - ' . Html::a('View alarm', ['/alarm/view', 'id' => $alarm->id]) .
                                            Html::tag('br') .
                                            Html::tag('div', $alarm->status, ['class' => "label label-danger"]) . ' ' . Yii::$app->formatter->asDatetime($alarm->updated_at), ['class' => "product-description col-xs-6 lead"]) .
                                    Html::tag('div', ($alarm->user ? Html::tag('div', 'Assigned agent: ' . Html::a(ucfirst($alarm->alert->user->username), ['/user/view', 'id' => $alarm->alert->user_id], ['target' => '_blank']), ['class' => 'lead']) : Html::tag('div', 'Waiting for an agent: ' . (time() - $alarm->alert->updated_at) . ' s.', ['class' => 'small', 'style' => 'margin-top:5px;']))
                                            , ['class' => 'col-xs-6'])
                                    , ['class' => "row"])
                            , ['class' => 'item']);
        }
        if (!$html)
            $html = "There is no active alarms at the moment";
        return ['result' => true, 'html' => $html];
    }

    /**
     * Disable alarm with given id
     * @param type $id
     * @return type
     */
    public function actionDisable($id) {
        $model = $this->findModel($id);
        if ($model) {
            $model->status = Alarm::STATUS_DISABLED;
            if ($model->save()) {
                Yii::$app->session->setFlash('success', "Alarm has been successfully disabled.");
            } else {
                Yii::$app->session->setFlash('warning', "Unexpected error while disabling alarm.");
            }
        }
        return $this->redirect(['/alarm/view', 'id' => $id]);
    }

}
