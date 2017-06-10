<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Alert */

$this->title = 'Alert #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Alerts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alert-view container">



    <p>
        <?=
        Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ])
        ?>
    </p>

    <div class="box">
        <div class="box-header with-border">
            Alerts Details
        </div>

        <div class="box-body">
            <?=
            DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'type',
                    'ip',
                    'raw_json',
                    'user_id',
                    'status',
                    'camera_id',
                    'created_at:datetime',
                    'updated_at:datetime',
                    //'deleted_at',
                ],
            ])
            ?>
        </div>
    </div>
</div>
