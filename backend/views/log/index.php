<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\LogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Logs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-index">

    <div class="box">
        <div class="box-header with-border">
            Log List
        </div>
        <div class="box-body">
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'width' => '30px',
                    ],
                    'name',
                    'details',
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
                                'attribute' => 'camera_id',
                                'label' => 'Camera',
                                'value' => function($model) {
                                    if ($model->camera)
                                        return '#' . $model->camera->id . ' - ' . Html::a(ucfirst($model->camera->name), ['camera/view', 'id' => $model->camera->id]);
                                    else
                                        return '';
                                },
                                        'format' => 'html',
                                    ],
                                    'created_at:datetime',
                                    // 'updated_at',
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
