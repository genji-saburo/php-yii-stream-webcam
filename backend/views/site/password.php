<?php
/* @var $this yii\web\View */
/* @var $model common\models\PasswordResetForm */

use yii\bootstrap\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\bootstrap\ActiveField;
?>

<div class="container">
    <div class="box">
        <div class="box-header with-border">
            Password reset form
        </div>

        <div class="box-body">

            <?php
            echo Html::beginTag('div', ['class' => '']);

            $form = ActiveForm::begin(['action' => '/password', 'method' => 'post']);

            echo $form->field($model, 'password_new')->passwordInput();

            echo $form->field($model, 'password_old')->passwordInput();

            echo Html::submitButton('Save', ['class' => 'btn btn-primary']);

            $form->end();

            echo Html::endTag('div');
            ?>

        </div>
    </div>
</div>