<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Camera */

$this->title = 'Add Camera';
$this->params['breadcrumbs'][] = ['label' => 'Cameras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="camera-create container">


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
