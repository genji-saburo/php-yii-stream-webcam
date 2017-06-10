<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\TagLog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tag-log-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'tag_id')->textInput() ?>

    <?= $form->field($model, 'reader_id')->textInput() ?>

    <?= $form->field($model, 'is_authorised')->textInput() ?>

    <?= $form->field($model, 'created_at')->textInput() ?>

    <?= $form->field($model, 'updated_at')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>