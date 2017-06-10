<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AlarmSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Alarms';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alarm-index">

    <div class="box">
        <div class="box-header with-border">
            Alarm list
        </div>

        <div class="box-body">
            <?=
            GridView::widget([
                'pjax' => true,
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    'id',
                    'details',
                    'type',
                    [
                        'attribute' => 'user_id',
                        'label' => 'User',
                        'value' => function($model) {
                            if ($model->user)
                                return '#' . $model->user->id . ' - ' . Html::a(ucfirst($model->user->username), ['user/view', 'id' => $model->user->id]);
                            else
                                return '';
                        },
                                'format' => 'html',
                            ],
                            [
                                'attribute' => 'alert_id',
                                'label' => 'Alert',
                                'value' => function($model) {
                                    if ($model->user)
                                        return '#' . $model->alert->id . ' - ' . Html::a(($model->alert->camera->property ? ucfirst($model->alert->camera->property->name) : ''), ['alert/view', 'id' => $model->alert->id]);
                                    else
                                        return '';
                                },
                                        'format' => 'html',
                                    ],
                                    'created_at:datetime',
                                    // 'updated_at',
                                    // 'deleted_at',
                                    [
                                        'class' => '\kartik\grid\ActionColumn',
                                        'buttons' => [
                                            'delete' => function ($url, $model) {
                                                return '';
                                            },
                                            'update' => function ($url, $model) {
                                                return '';
                                            },
                                        ],
                                    ]
                                ],
                            ]);
                            ?>
        </div>
    </div>
</div>
