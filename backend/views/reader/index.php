<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use common\models\Reader;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ReaderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Reader List';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reader-index">
    <div class="box">
        <div class="box-header with-border">
            Reader data
        </div>

        <div class="box-body">
            <?=
            GridView::widget([
                'pjax' => true,
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    'id',
                    [
                        'attribute' => 'property_id',
                        'format' => 'html',
                        'filter' => yii\helpers\ArrayHelper::map(common\models\Property::find()->all(), 'id', 'name'),
                        'label' => 'Property',
                        'value' => function($model){
                            return ($model->property_id ? Html::a("#{$model->property->id} - {$model->property->name}", ['property/view', 'id' => $model->property->id]) : "");
                        },
                    ],
                    'name',
                    [
                        'attribute' => 'address',
                        'format' => 'html',
                        'label' => 'IP',
                        'value' => function($model){
                            return ($model->address) ? $model->address : '';
                        },
                    ],
                    [
                        'attribute' => 'type',
                        'format' => 'html',
                        'filter' => Reader::values('type'),
                        'label' => 'Type',
                        'value' => function($model){
                            return ($model->type ? Reader::values('type')[$model->type] : "");
                        },
                    ],
                    'serial_number',
                    [
                        'attribute' => 'status',
                        'format' => 'html',
                        'filter' => Reader::values('status'),
                        'label' => 'Status',
                        'value' => function($model){
                            return ($model->status || $model->status == 0 ? Reader::values('status')[$model->status] : "");
                        },
                    ],
					
                    [
                        'class' => '\kartik\grid\ActionColumn',
                    ]
                ],
            ]);
            ?>
        </div>
    </div>
</div>
