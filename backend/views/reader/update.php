<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Reader */

$this->title = 'Update Reader: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Readers', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="reader-update container">

    <div class="box">
        <div class="box-header with-border">
            Reader data
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
