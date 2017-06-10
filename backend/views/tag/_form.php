<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Property;
use common\models\Tag;
use yii\bootstrap\Modal;
use kartik\file\FileInput;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model common\models\Tag */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="tag-form">

    <?php $form = ActiveForm::begin(['options'=>['enctype'=>'multipart/form-data']]); ?>
    
    <?= $form->field($model, 'property_id')->dropDownList(Property::getOptionsArr()) ?>

    <?= $form->field($model, 'tag_id')->textInput() ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>
    
    <?php Modal::begin([
        'header' => '<h2>Add User Photo</h2>',
        'toggleButton' => [
            'tag' => 'button',
            'class' => 'btn btn-primary',
            'label' => 'Browse',
        ]
    ]); ?>
    
    <?= $form->field($model, 'image')->widget(FileInput::classname(), [
        'options' => [
            'multiple' => false,
        ],
        'pluginOptions' => [
            'layoutTemplates' => [
                'actions' => '<div class="file-actions">' .
                    '    <div class="file-footer-buttons">' .
                    '        {zoom} {other}' .
                    '    </div>' .
                    '    {drag}' .
                    '    <div class="clearfix"></div>' .
                    '</div>',
            ],
            'initialPreview' => ( $model->image )? [ Html::img( '/upload/tag/' . $model->id . '/' . $model->image, ['class'=>'file-preview-image', 'alt' => $model->username, 'title' => $model->username, 'width' => 'auto', 'height' => '100%']) ] : [],
            'previewFileType' => 'any', 
            'uploadUrl' => false,
            'showUpload' => false,
            'showCaption' => false,
        ],
    ]); ?>

    <?php Modal::end(); ?>

    <?= $form->field($model, 'access_level')->dropDownList(Tag::values('access_level')) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
