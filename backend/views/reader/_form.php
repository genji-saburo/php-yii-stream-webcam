<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Reader;
use common\models\Property;

/* @var $this yii\web\View */
/* @var $model common\models\Reader */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="reader-form">

    <?php $form = ActiveForm::begin(); ?>
	
    <?= $form->field($model, 'property_id')->dropDownList(Property::getOptionsArr()) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>
	
    <?= $form->field($model, 'type')->dropDownList(Reader::values('type'), ['prompt' => 'Select RFID Reader Type']) ?>

    <?= $form->field($model, 'serial_number')->textInput(['maxlength' => true]) ?>
	
    <?= $form->field($model, 'status')->dropDownList(Reader::values('status')) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
