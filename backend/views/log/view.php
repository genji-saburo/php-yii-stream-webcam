<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Log */

$this->title = "Log #" . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Logs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="log-view container">

    <div class="box">
        <div class="box-header with-border">
            Log Details
        </div>
        <div class="box-body">

            <?=
            DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    [
                        'label' => 'User Name',
                        'value' => Html::a($model->user->username, ['user/view', 'id' => $model->user->id]),
                        'format' => 'html',
                    ],
                    'name',
                    'details',
                    'user_id',
                    'property_id',
                    'camera_id',
                    'alert_id',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ])
            ?>
        </div>
    </div>
</div>
