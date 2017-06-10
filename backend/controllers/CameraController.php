<?php

namespace backend\controllers;

use Yii;
use common\models\Camera;
use backend\models\CameraSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\CameraLog;
use yii\helpers\Html;
use common\models\LogStream;

/**
 * CameraController implements the CRUD actions for Camera model.
 */
class CameraController extends AccessController {

    /**
     * Lists all Camera models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new CameraSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Camera model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id) {
        $cameraLogs = CameraLog::find()
                ->andWhere(['camera_id' => $id])
                ->andWhere('created_at > UNIX_TIMESTAMP(NOW()) - 86400')
                ->orderBy(['created_at' => SORT_ASC]);
        $logSeries = [];
        foreach ($cameraLogs->each(100) as $log) {
            $logSeries[] = [(integer) ($log->created_at + 14401) * 1000, ($log->status === CameraLog::STATUS_ONLINE ? 1 : 0)];
        }
        return $this->render('view', [
                    'model' => $this->findModel($id),
                    'logSeries' => $logSeries
        ]);
    }

    /**
     * Creates a new Camera model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate() {
        $model = new Camera();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Camera model.
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
     * Watch an existing Camera model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionWatch($id) {
        $model = $this->findModel($id);
        if ($model) {
            return $this->render('watch', [
                        'model' => $model,
            ]);
        } else {
            Yii::$app->session->setFlash('danger', 'No camera found.');
            return $this->redirect('/');
        }
    }

    /**
     * Deletes an existing Camera model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Camera model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Camera the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Camera::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Returns image content
     * @param type $id
     * @return string
     */
    public function actionImage($id) {
        Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
        if ($camera = Camera::findOne($id)) {
            return $camera->getImageContent();
        }
        return '';
    }

    /**
     * Refresh snapshot for the given camera
     * @return Json
     */
    public function actionImageRefresh() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        if ($id = \Yii::$app->getRequest()->post('id')) {
            $camera = Camera::findOne($id);
            if ($camera && $camera->downloadImage()) {
                return ['result' => 'success', 'src' => $camera->getImagePath()];
            } else {
                return ['result' => 'fail', 'msg' => 'Download error'];
            }
        }
        $response = Yii::$app->getResponse();
        $response->format = \yii\web\Response::FORMAT_JSON;
        return ['result' => 'fail'];
    }

    /**
     * Create new comment to the camera
     * @return type
     */
    public function actionComment() {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $camera_id = \Yii::$app->getRequest()->post('camera_id');
        $message = \Yii::$app->getRequest()->post('message');
        $camera = Camera::findOne($camera_id);
        if ($camera && $message && !Yii::$app->user->isGuest) {
            $curUser = Yii::$app->user->getIdentity();
            $comment = new \common\models\Comment([
                'camera_id' => $camera_id,
                'user_id' => $curUser->id,
                'message' => $message
            ]);
            if ($comment->save()) {
                $commentHtml = Html::tag('div', Html::tag('div', Html::tag('div', Html::tag('div', Html::tag('i', '', ['class' => 'fa fa-user']), ['class' => 'info-box-icon bg-gray'])
                                                , ['class' => 'text-center']) . Html::tag('div', ucfirst($comment->user->username), [])
                                        , ['class' => 'col-sm-2']) .
                                Html::tag('div', Html::tag('div', Yii::$app->formatter->asDatetime($comment->created_at)) .
                                        Html::tag('div', $comment->message, ['class' => 'well text-left'])
                                        , ['class' => 'col-sm-10'])
                                , ['class' => 'row margin-top-block-md']);
                LogStream::getSocketConnection()->send(LogStream::getPropertyWatchJson('1', ['comment' => [
                                'html' => $commentHtml
                ]]));
                return ['result' => true, 'html' => $commentHtml];
            } else {
                return ['result' => false, 'message' => 'Error while saving new comment'];
            }
        }
        $response = Yii::$app->getResponse();
        $response->format = \yii\web\Response::FORMAT_JSON;
        return ['result' => false, 'message' => 'Required data are missing'];
    }

}
