<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Property */

$this->title = 'Update Property: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Properties', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="property-update container">

    <div class="box">
        <div class="box-header with-border">
            Property List
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
