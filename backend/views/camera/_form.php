<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Property;
use common\models\Camera;

/* @var $this yii\web\View */
/* @var $model common\models\Camera */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="camera-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'port')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'login')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'password')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'serial_number')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'auth_type')->dropDownList([Camera::CAMERA_AUTH_TYPE_DIGEST => Camera::CAMERA_AUTH_TYPE_DIGEST, Camera::CAMERA_AUTH_TYPE_BASIC => Camera::CAMERA_AUTH_TYPE_BASIC]) ?>

    <?= $form->field($model, 'property_id')->dropDownList(Property::getOptionsArr()) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
