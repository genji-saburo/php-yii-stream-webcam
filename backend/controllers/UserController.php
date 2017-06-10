<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use backend\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use frontend\models\SignupForm;
use yii\filters\AccessControl;
use \common\models\Log;
use yii\bootstrap\Html;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends AccessController {
    
    /**
     * @inheritdocs
     */
    public function beforeAction($action) {
        \Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        $model = $this->findModel($id);

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => Log::find()
                    ->andWhere(['user_id' => $model->id])
                    ->andWhere(['not', ['name' => Log::LOG_USER_ONLINE]])
                    ->orderBy(['id' => SORT_DESC])
        ]);

        $logQuery = new \yii\db\Query();
        $logQuerySeries = $logQuery->from('log')
                ->addSelect(["created_at", "name", "count(*) as counter"])
                ->andWhere(['user_id' => $model->id])
                //->andWhere(['not', ['name' => Log::LOG_USER_ONLINE]])
                ->andWhere('created_at > UNIX_TIMESTAMP(NOW()) - 86400')
                ->groupBy(["name", "(created_at DIV 600)"])
                ->orderBy(['created_at' => SORT_ASC])
                ->all();


        $logNameId = [];
        $logSeries = [];
        foreach ($logQuerySeries as $log) {
            $seriesId = array_search($log['name'], $logNameId);
            if (!in_array($log['name'], $logNameId))
                $logNameId[] = $log['name'];
            if (!isset($logSeries[$seriesId])) {
                $logSeries[$seriesId] = [
                    'type' => 'column',
                    'name' => $log['name'],
                    'data' => []
                ];
                if ($log['name'] == Log::LOG_CAMERA_VIEW || $log['name'] == Log::LOG_USER_ONLINE)
                    $logSeries[$seriesId]['yAxis'] = 1;
            }
            $logSeries[$seriesId]['data'][] = [(integer) ($log['created_at'] + 14401) * 1000, (integer) $log['counter']];
        }

        ksort($logSeries);  //  Sort array before showing, highchart requires

        return $this->render('view', [
                    'model' => $model,
                    'dataProvider' => $dataProvider,
                    'logSeries' => $logSeries
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                Yii::$app->session->setFlash('success', 'User account has been successfully created.');
                return $this->redirect(\yii\helpers\Url::to(['/user/']));
            }
        }
        return $this->render('create', [
                    'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
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
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Returns required user status
     * 
     * @param type $user_id
     * @return type
     */
    public function actionGetStatus($user_id) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $user = User::findOne($user_id);
        if ($user) {
            $logs = Log::getUserLogs($user_id);
            if (count($logs) > 0)
                $activity = Html::tag('div', Html::tag('span', 'User online', ['class' => 'label label-success']), ['class' => 'col-xs-4 col-sm-3 col-md-2']);
            else {
                $lastActivity = Log::find()->andWhere(['user_id' => $user->id])->orderBy(['id' => SORT_ASC])->one();
                if ($lastActivity)
                    $lastLog = 'Since ' . Yii::$app->formatter->asDatetime($lastActivity->created_at);
                else
                    $lastLog = 'Never logged in';
                $activity = Html::tag('div', Html::tag('span', 'User offline', ['class' => 'label label-default']) . ' ' .
                                $lastLog
                                , ['class' => 'col-xs-12']);
            }

            $logCount = 0;
            foreach ($logs as $curLog) {
                if ($curLog['name'] == Log::LOG_USER_ONLINE) {
                    
                } else {
                    $activity .= Html::tag('div', $curLog['name'] . ' ' .
                                    Html::tag('span', Yii::$app->formatter->asTime($curLog['created_at']), ['class' => 'badge']) .
                                    ( ++$logCount + 1 != count($logs) ? ' <i class="fa fa-arrow-left"></i>' : ""), ['class' => 'col-xs-4 col-sm-3 col-md-2']);
                }
            }
            return ['result' => true, 'html' =>
                Html::tag('div', $activity, ['class' => 'row'])
            ];
        }
        return ['result' => false];
    }

    /**
     * Returns user activity in hours for the given term or for the last week if nothing given
     */
    public function actionGetWorktime($from = null, $to = null, $user_id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($user_id)
            $user = User::findOne($user_id);
        else
            $user = Yii::$app->user->getIdentity();
        if ($user) {
            $logQuery = new \yii\db\Query();
            $logQuery->from('log')
                    ->addSelect(["created_at", "count(*) as counter"])
                    ->andWhere(['name' => Log::LOG_USER_ONLINE])
                    ->andWhere(['user_id' => $user->id])
                    ->groupBy(["name", "(created_at DIV 60)"]);
            if($from)
                $logQuery->andWhere(['>', 'created_at', $from]);
            if($to)
                $logQuery->andWhere(['<', 'created_at', $to]);
            else
                $logQuery->andWhere('created_at > UNIX_TIMESTAMP(NOW()) - 604800');
            
            $minuteCounter = $logQuery->count();
            
            return ['result' => true, 'work_hours' => round($minuteCounter / 60), 'work_minutes' => $minuteCounter % 60];
        }
        return ['result' => false];
    }
    
    /**
     * Sets user preferences and returns json
     */
    public function actionSettings(){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $user = \Yii::$app->user->getIdentity();        
        $action = \Yii::$app->request->post('action');        
        switch($action){
            case "multiscreen":
                $curValue = \Yii::$app->session->get(User::SETTINGS_MULTISCREEN, false);
                \Yii::$app->session->set(User::SETTINGS_MULTISCREEN, !$curValue);
                if($curValue)
                    return ['result' => true, 'value' => !$curValue, 'message' => 'Multiscreen has been successfully disabled'];
                else
                    return ['result' => true, 'value' => !$curValue, 'message' => 'Multiscreen has been successfully enabled'];
            default:
                break;
        }        
        
        return ['result' => false, 'message' => 'Nothing to do'];
    }

}
