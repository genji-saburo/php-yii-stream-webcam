<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Reader */

$this->title = 'Add Reader';
$this->params['breadcrumbs'][] = ['label' => 'Reader', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="reader-create container">


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
