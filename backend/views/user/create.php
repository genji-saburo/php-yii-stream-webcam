<?php

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = 'Create User';
$this->params['breadcrumbs'][] = ['label' => 'Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-create container">

    <div class="box">
        <div class="box-header with-border">
            User data
        </div>

        <div class="box-body">

            <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>

            <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

            <?= $form->field($model, 'email') ?>

            <?= $form->field($model, 'password')->passwordInput() ?>

            <?php
            $options = common\models\User::getRoleName();
            $options[''] = 'Select role';
            ksort($options);
            echo $form->field($model, 'role')->dropDownList($options);
            ?>

            <div class="form-group">
<?= Html::submitButton('Signup', ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
            </div>

<?php ActiveForm::end(); ?>

        </div>
    </div>

</div>
