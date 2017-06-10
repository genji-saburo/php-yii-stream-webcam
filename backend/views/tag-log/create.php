<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\TagLog */

$this->title = 'Create Tag Log';
$this->params['breadcrumbs'][] = ['label' => 'Tag Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tag-log-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
