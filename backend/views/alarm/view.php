<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Alarm */

$this->title = 'Alarm #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Alarms', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alarm-view">

    <div class="box">
        <div class="box-header with-border">
            Alarm data 
        </div>

        <div class="box-body">

            <?=
            DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'details',
                    'status',
                    'type',
                    'user_id',
                    'alert_id',
                    'created_at:datetime',
                    'updated_at:datetime',
                    //'deleted_at:datetime',
                ],
            ])
            ?>

        </div>
        <div class="box-footer">
            <?php
            if ($model->status == \common\models\Alarm::STATUS_ACTIVE) {
                echo Html::a('Disable alarm', ['/alarm/disable', 'id' => $model->id], [
                    'class' => 'btn btn-warning',
                    'data' => [
                        'confirm' => 'Are you sure you want to disale this alarm?',
                        'method' => 'post',
                    ]
                ]);
            }
            ?>
        </div>
    </div>
</div>
