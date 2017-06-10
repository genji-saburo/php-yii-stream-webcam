<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\AlertSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Alerts recieved by you';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alert-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="box">
        <div class="box-header with-border">
            Alerts List
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
                    'type',
                    'ip',
                    'status',
                    //[
                    //    'filterType' => GridView::FILTER_DATE,
                    //    'attribute' => 'created_at',
                    //    'format'=>'datetime',                        
                    //],
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
