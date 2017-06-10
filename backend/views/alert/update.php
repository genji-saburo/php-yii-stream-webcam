<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Alert */

$this->title = 'Update Alert #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Alerts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="alert-update container">

    <div class="box">
        <div class="box-header with-border">
            Alerts Details
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
