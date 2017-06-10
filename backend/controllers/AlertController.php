<?php

namespace backend\controllers;

use Yii;
use common\models\Alert;
use backend\models\AlertSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use \common\models\Log;
use \yii\helpers\Html;
use common\models\User;

/**
 * AlertController implements the CRUD actions for Alert model.
 */
class AlertController extends AccessController {

    /**
     * Lists all Alert models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new AlertSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all Alert models recieved by cirtain user.
     * @return mixed
     */
    public function actionRecieved() {
        $searchModel = new AlertSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        /* Show only for current user */
        $curUser = Yii::$app->user->getIdentity();
        $dataProvider->query->andWhere(['user_id' => $curUser->id]);

        return $this->render('recieved', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Alert model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Updates an existing Alert model.
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
     * Deletes an existing Alert model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Alert model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Alert the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Alert::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Checks if exist any urgent alert for the agent
     */
    public function actionCheck() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if (!Yii::$app->user->isGuest) {
            $curUser = Yii::$app->user->getIdentity();
            if ($curUser->role === \common\models\User::ROLE_AGENT) {
                $activeAlerts = Alert::find()
                        ->andWhere(['user_id' => $curUser->id])
                        ->andWhere(['or', ['status' => Alert::STATUS_ACCEPTED], ['status' => Alert::STATUS_ASSIGNED]])
                        ->count();
                if ($activeAlerts == 0 || \Yii::$app->session->get(User::SETTINGS_MULTISCREEN, false)) {
                    $curAlert = Alert::assignAlertToAgent($curUser->id);
                    if ($curAlert) {
                        $curAlert->user_id = $curUser->id;
                        $curAlert->status = Alert::STATUS_ASSIGNED;
                        $curAlert->save();
                        Log::add(Log::LOG_CAMERA_ASSIGN, '{}', $curUser->id, $curAlert->camera_id, $curAlert->camera->property->id, $curAlert->id);
                        return ['result' => true, 'alert_id' => $curAlert->id, 'camera_id' => $curAlert->camera_id];
                    }
                } else {
                    $curAlert = Alert::find()
                            ->andWhere(['user_id' => $curUser->id])
                            ->andWhere(['or', ['status' => Alert::STATUS_ACCEPTED], ['status' => Alert::STATUS_ASSIGNED]])
                            ->one();
                    if ($curAlert)
                        return ['result' => false, 'alert_id' => $curAlert->id, 'camera_id' => $curAlert->camera_id];
                }
            }
        }
        //return ['result' => true, 'alert_id' => 1, 'camera_id' => 1]; //  Test mode
        return ['result' => false];
    }

    /**
     * Refresh assigned to user alert
     * @param type $alertId
     */
    public function actionSkip($alertId) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $alert = Alert::findOne($alertId);
        if ($alert && ($alert->user_id == Yii::$app->user->id ||
                \Yii::$app->user->getIdentity()->role === \common\models\User::ROLE_ADMIN)) {
            $alert->status = Alert::STATUS_PENDING;
            $result = $alert->save();
            if ($result)
                Log::add(Log::LOG_CAMERA_SKIPPED, '{}', $alert->camera->property_id, $alert->camera_id, $alert->id);
            return ['result' => $result];
        }
        return ['result' => false];
    }

    /**
     * Close the alert, accepted by user
     * 
     * @param type $alertId
     * @return type
     */
    public function actionClose($alertId) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $alert = Alert::findOne($alertId);
        if ($alert && ($alert->user_id == \Yii::$app->user->id && $alert->status == Alert::STATUS_ACCEPTED ||
                \Yii::$app->user->getIdentity()->role === \common\models\User::ROLE_ADMIN)) {
            $alert->user_id = \Yii::$app->user->id;
            $alert->status = Alert::STATUS_VIEWED;
            $result = $alert->save();
            if ($result)
                Log::add(Log::LOG_CAMERA_CLOSED, '{}', $alert->camera->property_id, $alert->camera_id, $alert->id);
            return ['result' => $result];
        }
        return ['result' => false];
    }

    /**
     * Returns JSON with active alerts info
     * 
     * @return type
     */
    public function actionGetAlertList() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $alerts = Alert::getActive();
        $html = "";
        $statData = [
                //Alert::STATUS_ACCEPTED => [ucfirst(Alert::STATUS_ACCEPTED), 0],
                //Alert::STATUS_ASSIGNED => [ucfirst(Alert::STATUS_ASSIGNED), 0],
                //Alert::STATUS_PENDING => [ucfirst(Alert::STATUS_PENDING), 0],
        ];
        foreach ($alerts->each() as $alert) {
            if (isset($statData[$alert->status])) {
                $statData[$alert->status][1] += 1;
            } else {
                $statData[$alert->status] = [ucfirst($alert->status), 1];
            }

            switch ($alert->status) {
                case Alert::STATUS_ASSIGNED:
                    $statusIcon = Html::tag('span', Html::tag('i', '', ['class' => 'fa fa-user-o']), ['class' => 'info-box-icon bg-yellow']);
                    $style = "warning";
                    break;
                case Alert::STATUS_ACCEPTED:
                    $statusIcon = Html::tag('span', Html::tag('i', '', ['class' => 'fa fa-user']), ['class' => 'info-box-icon bg-green']);
                    $style = "success";
                    break;
                default:
                    $statusIcon = Html::tag('span', Html::tag('i', '', ['class' => 'fa fa-question']), ['class' => 'info-box-icon bg-gray']);
                    $style = "default";
                    break;
            }
            $html .= Html::tag('li', Html::tag('div', Html::tag('div', $statusIcon, ['class' => 'col-xs-4 text-center']) .
                                    Html::tag('div', Html::a((isset($alert->camera->property->name) ? ucfirst($alert->camera->property->name) : $alert->camera->name), ['site/watch', 'alert_id' => $alert->id]) .
                                            Html::tag('span', Yii::$app->formatter->asDatetime($alert->updated_at), ['class' => "product-description"]) . Html::tag('div', $alert->status, ['class' => "label label-$style"]) .
                                            ($alert->user ? Html::tag('div', 'Assigned agent: ' . Html::a(ucfirst($alert->user->username), ['/user/view', 'id' => $alert->user_id], ['target' => '_blank']), ['class' => 'lead']) : Html::tag('div', 'Waiting for an agent: ' . (time() - $alert->updated_at) . ' s.', ['class' => 'small', 'style' => 'margin-top:5px;']))
                                            , ['class' => 'col-xs-8'])
                                    , ['class' => "row"])
                            , ['class' => 'item']);
        }
        if (!$html)
            $html = "There is no video in stack";
        return ['result' => true, 'html' => $html, 'statistics' => array_values($statData)];
    }

    /**
     * Shows video queue
     * @return type
     */
    public function actionQueue() {
        return $this->render('queue');
    }

    /**
     * Returns alerts queue html
     * @return type
     */
    public function actionGetAlertQueue() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $stat = [];
        $stat['pending'] = Alert::find()
                ->andWhere(['in', 'status', [ Alert::STATUS_PENDING, Alert::STATUS_ASSIGNED]])
                ->count();
        $stat['watching'] = Alert::find()
                ->andWhere(['status' => Alert::STATUS_ACCEPTED])
                ->count();
        $stat['answer_24h'] = Alert::find()
                ->andWhere(['in', 'status', [Alert::STATUS_ACCEPTED, Alert::STATUS_VIEWED]])
                ->andWhere(['>', 'created_at', (time() - 24 * 60 * 60)])
                ->average('(updated_at - created_at)');
        if(!$stat['answer_24h'])
            $stat['answer_24h'] = 'No data';
        $stat['answer_overall'] = Alert::find()
                ->select('(updated_at - created_at) as answer_time')
                ->andWhere(['status' => Alert::STATUS_ACCEPTED])
                ->average('(updated_at - created_at)');


        $alerts = Alert::find()
                ->andWhere(['not', ['status' => Alert::STATUS_BOUNCED]])
                ->andWhere(['>', 'created_at', (time() - 24 * 60 * 60)])
                ->orderBy([Alert::tableName() . '.id' => SORT_DESC]);
        $html = "";
        foreach ($alerts->each() as $alert) {
            $html .= $this->renderAjax('_alert-queue-item', compact('alert'));
        }
        if (!$html)
            $html = "There is no video in stack";
        return ['result' => true, 'html' => $html, 'statistics' => $stat];
    }

}
