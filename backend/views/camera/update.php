<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Camera */

$this->title = 'Update Camera: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Cameras', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="camera-update container">

    <div class="box">
        <div class="box-header with-border">
            Camera data
        </div>

        <div class="box-body">
            <?=
            $this->render('_form', [
                'model' => $model,
            ])
            ?>
        </div>
    </div>
</div>
