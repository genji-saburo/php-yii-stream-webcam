<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use miloschuman\highcharts\Highcharts;

/* @var $this yii\web\View */
/* @var $model common\models\Camera */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Cameras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="camera-view container">

    <p>
        <?= Html::a('Watch', ['watch', 'id' => $model->id], ['class' => 'btn btn-warning']) ?>
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
            Camera data
        </div>

        <div class="box-body">

            <?=
            DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'name',
                    [
                        'format' => 'html',
                        'label' => 'Property',
                        'value' => ($model->property ? Html::a("#{$model->property->id} - {$model->property->name}", ['property/view', 'id' => $model->property->id]) : ""),
                    ],
                    'address',
                    'port',
                    'login',
                    'password',
                    'serial_number',
                    'auth_type',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ])
            ?>

        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
            Camera status logs
        </div>
        <div class="box-body">
            <?php
            echo Highcharts::widget([
                'htmlOptions' => ['style' => 'height:500px;'],
                'options' => [
                    'legend' => ['enabled' => false],
                    'chart' => ['type' => 'spline', 'zoomType' => 'x'],
                    'credits' => false,
                    'title' => ['text' => '7 days camera logs'],
                    'xAxis' => [
                        'type' => 'datetime',
                        //'maxZoom' => (3600 * 1000),
                        //'minRange' => (12 * 3600 * 1000),
                        'dateTimeLabelFormats' => [
                            'millisecond' => '%H:%M:%S',
                            'second' => '%H:%M:%S',
                            'minute' => '%H:%M',
                            'hour' => '%H',
                        ],
                        //'minTickInterval' => 60 * 1000
                    ],
                    'yAxis' => [
                        [
                            'min' => 0,
                            'title' => [
                                'text' => 'Camera statuses',
                            ],
                        ],
                    ],
                    'series' => [[
                    'type' => 'spline',
                    'name' => 'Camera status',
                    'data' => $logSeries,
                        ]]
                ]
            ]);
            ?>
        </div>
    </div>


</div>
