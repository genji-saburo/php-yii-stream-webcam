<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Reader;

/* @var $this yii\web\View */
/* @var $model common\models\Reader */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Readers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="reader-view container">

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
            Reader data
        </div>

        <div class="box-body">

            <?=
            DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    [
                        'format' => 'html',
                        'label' => 'Property',
                        'value' => ($model->property_id ? Html::a("#{$model->property->id} - {$model->property->name}", ['property/view', 'id' => $model->property->id]) : ""),
                    ],
                    'name',
                    [
                        'format' => 'html',
                        'label' => 'IP',
                        'value' => ($model->address ? $model->address : ""),
                    ],
                    [
                        'format' => 'html',
                        'label' => 'Type',
                        'value' => ($model->type) ? Reader::values('type')[$model->type] : "",
                    ],
                    'serial_number',
                    [
                        'format' => 'html',
                        'label' => 'Status',
                        'value' => ($model->status) ? Reader::values('status')[$model->status] : "",
                    ],
                ],
            ])
            ?>

        </div>
    </div>
</div>