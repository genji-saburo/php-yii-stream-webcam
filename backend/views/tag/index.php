<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use common\models\Tag;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\TagSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tags List';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tag-index">
    <div class="box">
        <div class="box-header with-border">
            Tag data
        </div>

        <div class="box-body">
            <?=
            GridView::widget([
                'pjax' => true,
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    'id',
                    'tag_id',
                    [
                        'attribute' => 'property_id',
                        'format' => 'html',
                        'filter' => yii\helpers\ArrayHelper::map(common\models\Property::find()->all(), 'id', 'name'),
                        'label' => 'Property',
                        'value' => function($model){
                            return ($model->property_id ? Html::a("#{$model->property->id} - {$model->property->name}", ['property/view', 'id' => $model->property->id]) : "");
                        },
                    ],
                    'username',
                    'phone',
                    [
                        'attribute' => 'image',
                        'format' => 'raw',
                        'filter' => false,
                        'contentOptions' => ['style'=>'text-align: center;'],
                        'value' => function($model){
                            return ($model->image ? Html::img("/upload/tag/". $model->id . '/' . $model->image, ['alt' => $model->username, 'style' => ['width' => '120px']]) : "");
                        },
                    ],
                    [
                        'attribute' => 'access_level',
                        'format' => 'html',
                        'filter' => Tag::values('access_level'),
                        'label' => 'Type',
                        'value' => function($model){
                            return ($model->access_level ? Tag::values('access_level')[$model->access_level] : "");
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