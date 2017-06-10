<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\CameraSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Camera List';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="camera-index">
    <div class="box">
        <div class="box-header with-border">
            User data
        </div>

        <div class="box-body">
            <?=
            GridView::widget([
                'pjax' => true,
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    'id',
                    'name',
                    'address',
                    'port',
                    [
                        'attribute' => 'property_id',
                        'format' => 'html',
                        'label' => 'Property',
                        'value' => function($model){return ($model->property ? Html::a("#{$model->property->id} - {$model->property->name}", ['property/view', 'id' => $model->property->id]) : "");},
                    ],
                    // 'password',
                    'serial_number',
                    // 'created_at',
                    // 'updated_at',
                    [
                        'class' => '\kartik\grid\ActionColumn',
                    ]
                ],
            ]);
            ?>
        </div>
    </div>
</div>
