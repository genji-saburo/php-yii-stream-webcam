<?php

namespace backend\controllers;

use Yii;
use common\models\Log;
use backend\models\LogSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * LogController implements the CRUD actions for Log model.
 */
class LogController extends AccessController {

    /**
     * @inheritdocs
     */
    public function beforeAction($action) {
        $excArr = ['actionWrite'];
        if (in_array($action->actionMethod, $excArr))
            \Yii::$app->controller->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * Lists all Log models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new LogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        $curUser = Yii::$app->user->getIdentity();
        if ($curUser && $curUser->role != \common\models\User::ROLE_ADMIN)
            $dataProvider->query->andWhere(['user_id' => $curUser->id]);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Log model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Log model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new Log();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Log model.
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
     * Deletes an existing Log model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Log model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Log the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Log::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Writes log to the system accepted with POST ajax request
     */
    public function actionWrite() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $name = \Yii::$app->getRequest()->post('name', '');
        $details = \Yii::$app->getRequest()->post('details', '');
        $cameraId = \Yii::$app->getRequest()->post('camera_id', null);
        $propertyId = \Yii::$app->getRequest()->post('property_id', null);
        $alertId = \Yii::$app->getRequest()->post('alert_id', null);
        if ($name && $details && !Yii::$app->user->isGuest) {
            $user = \Yii::$app->user->getIdentity();
            $model = new Log(['name' => $name, 'details' => $details]);
            $model->user_id = $user->id;
            $model->camera_id = $cameraId;
            $model->property_id = $propertyId;
            $model->alert_id = $alertId;
            return ['result' => $model->save(), 'msg' => implode(' ', $model->getFirstErrors())];
        }
        return ['result' => false, 'msg' => 'Incorrect data provided.'];
    }

}
