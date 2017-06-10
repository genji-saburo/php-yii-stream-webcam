<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $model common\models\Property */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Properties', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="property-view container">

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
            Property List
        </div>

        <div class="box-body">

            <?=
            DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    [
                        'attribute' => 'security_status',
                        'value' => $model->getStatusText(),
                    ],
                    'name',
                    'owner_name',
                    'address',
                    'pin_code',
                    'phone1',
                    'phone2',
                    'phone3',
                    'phone_police',
                    'coord_lat',
                    'coord_lng',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ])
            ?>

        </div>
    </div>

    <div class="box">
        <div class="box-header with-border">
            Associated Cameras
        </div>
        <div class="box-body">
            <?=
            GridView::widget([
                'pjax' => true,
                'dataProvider' => (new \yii\data\ActiveDataProvider(['query' => $model->getCameras()])),
                'columns' => [
                    'id',
                    'name',
                    'address',
                    'port',
                    // 'password',
                    'serial_number',
                // 'created_at',
                // 'updated_at',
                ],
            ]);
            ?>
        </div>
    </div>
</div>
