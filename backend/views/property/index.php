<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\PropertySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Properties';
$this->params['breadcrumbs'][] = $this->title;


$statusOptions = common\models\Property::getSecuriryStatusArr();
$statusOptions[''] = 'All';
$statusOptions = array_reverse($statusOptions);
?>
<div class="property-index">

    <div class="box">
        <div class="box-header with-border">
            Property List
        </div>

        <div class="box-body">
            <?=
            GridView::widget([
                'pjax' => true,
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    [
                        'attribute' => 'id',
                        'width' => '50px',
                    ],
                    'name',
                    'owner_name',
                    'address',
                    'phone1',
                    //'pin_code',
                    [
                        'width' => '150px',
                        'vAlign' => 'middle',
                        'attribute' => 'security_status',
                        'value' => function ($model, $key, $index, $widget) {
                            return $model->getStatusText();
                        },
                        'filter' => $statusOptions,
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'pluginOptions' => [
                            //'allowClear' => true,
                            ]
                        ]
                    ],
                    //'phone2',
                    //'phone3',
                    // 'coord_lat',
                    // 'coord_lng',
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
