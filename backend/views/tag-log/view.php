<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\TagLog */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Tag Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tag-log-view">

    <div class="box">
        <div class="box-header with-border">
            Tag Log Details
        </div>
        <div class="box-body">

            <?=
            DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'tag_id',
                    'reader_id',
                    'is_authorised',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ])
            ?>
        </div>
    </div>
</div>

