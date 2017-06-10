<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;
use miloschuman\highcharts\Highcharts;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = 'View user: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


$initJS = <<<SCRIPT
   var getUserStatus = function(){
        $(".real-time-activity-loader").show();
        $.get('/user/get-status?user_id=' + {$model->id}, function(data){
            if(data.result)
                $('.real-time-activity').html(data.html);
        }).always(function(){ $(".real-time-activity-loader").hide();});
   }
   getUserStatus();
   setInterval(getUserStatus, 10000);
SCRIPT;
$this->registerJs($initJS, \yii\web\View::POS_READY);
?>
<div class="user-view container">

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?=
        Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ])
        ?>
    </p>

    <div class="box">
        <div class="box-header with-border">
            User data
        </div>

        <div class="box-body">

            <?=
            DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'username',
                    'auth_key',
                    'password_hash',
                    'password_reset_token',
                    'email:email',
                    [
                        'attribute' => 'role',
                        'value' => $model->getRole()
                    ],
                    'status',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ])
            ?>

        </div>
    </div>

    <div class="box box-success">
        <div class="box-header with-border">
            User Real-time Activity <i class="fa fa-spinner fa-pulse real-time-activity-loader" style="display:none;"></i>
        </div>
        <div class="box-body real-time-activity">
                 
        </div>
    </div>
    
    <div class="box box-info">
        <div class="box-header with-border">
            User Statistics
        </div>

        <div class="box-body">
            <?php
            echo Highcharts::widget([
                'htmlOptions' => ['style' => 'height:500px;'],
                'options' => [
                    'legend' => ['enabled' => true],
                    'chart' => ['type' => 'column', 'zoomType' => 'x'],
                    'credits' => false,
                    'title' => ['text' => '24h user activity'],
                    'xAxis' => [
                        'type' => 'datetime',
                        //'maxZoom' => (3600 * 1000),
                        //'minRange' => (12 * 3600 * 1000),
                        'dateTimeLabelFormats' => [
                            'millisecond' => '%H:%M:%S.%L',
                            'second' => '%H:%M:%S',
                            'minute' => '%H:%M',
                            'hour' => '%H',
                        ],
                        'minTickInterval' => 60 * 1000
                    ],
                    'yAxis' => [
                        [
                            'min' => 0,
                            'title' => [
                                'text' => 'Activity',
                            ],
                        ],
                        [
                            'gridLineWidth' => 0,
                            'title' => [
                                'text' => 'Other Activity',
                            ],
                            //'labels' => [
                                //'style' => [
                                //    'color' => 'Highcharts.getOptions().colors[2]'
                                //]
                            //],
                            'opposite' => true,
                        ]
                    ],
                    'series' => $logSeries,
                ]
            ]);
            ?>
        </div>
    </div>

    <div class="box box-warning">
        <div class="box-header with-border">
            User Activity
        </div>

        <div class="box-body">
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'pjax' => true,
                'columns' => [
                    'name',
                    'details',
                    'created_at:datetime',
                ],
            ]);
            ?>            
        </div>
    </div>
</div>
