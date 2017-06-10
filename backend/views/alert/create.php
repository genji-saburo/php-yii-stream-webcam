<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Alert */

$this->title = 'Create Alert';
$this->params['breadcrumbs'][] = ['label' => 'Alerts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alert-create container">

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
