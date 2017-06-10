<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\TagSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tag-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'tag_id') ?>

    <?= $form->field($model, 'property_id') ?>

    <?= $form->field($model, 'username') ?>

    <?= $form->field($model, 'phone') ?>

    <?php // echo $form->field($model, 'image') ?>

    <?php // echo $form->field($model, 'access_level') ?>

    <?php // echo $form->field($model, 'access_interval') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-default']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
