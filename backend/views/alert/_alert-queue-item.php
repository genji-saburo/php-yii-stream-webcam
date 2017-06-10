<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Alert;
use common\models\Log;

/* @var $this yii\web\View */
/* @var $alert common\models\Alert */

switch ($alert->status) {
    case Alert::STATUS_ASSIGNED:
        $statusIcon = Html::tag('span', Html::tag('i', '', ['class' => 'fa fa-user-o']), ['class' => 'info-box-icon bg-yellow']);
        $style = "warning";
        break;
    case Alert::STATUS_ACCEPTED:
        $statusIcon = Html::tag('span', Html::tag('i', '', ['class' => 'fa fa-user']), ['class' => 'info-box-icon bg-green']);
        $style = "success";
        break;
    default:
        $statusIcon = Html::tag('span', Html::tag('i', '', ['class' => 'fa fa-question']), ['class' => 'info-box-icon bg-gray']);
        $style = "default";
        break;
}

$firstLog = Log::find()->andWhere(['name' => Log::LOG_CAMERA_VIEW, 'alert_id' => $alert->id])->orderBy(['id' => SORT_ASC])->one();
$watchStart = ($firstLog ? $firstLog->created_at : time());
?>

<div class="row">
    <div class="col-xs-4 col-md-2 col-lg-1 text-center">
        <?= $statusIcon ?>
    </div>
    <div class="col-xs-8 col-md-10 col-lg-11">
        <div>
            <?= Html::a((isset($alert->camera->property->name) ? ucfirst($alert->camera->property->name) : $alert->camera->name), ['site/watch', 'alert_id' => $alert->id]) ?>
        </div>
        <div class="row">
            <div class="col-xs-4">
                <span>Time recieved: </span>
                <?= Html::tag('span', Yii::$app->formatter->asDatetime($alert->created_at), ['class' => "product-description"]) ?>
            </div>
            <div class="col-xs-4">
                <span>Watch delay: </span>
                <span><?= Alert::beautifySeconds($watchStart - $alert->created_at) ?></span>
            </div>
            <div class="col-xs-4">
                <span>Current status: </span>
                <span><?= Html::tag('div', $alert->status, ['class' => "label label-$style"]) ?></span>
            </div>
        </div>
        <div>
            <?= ($alert->user ? Html::tag('div', 'Assigned agent: ' . Html::a(ucfirst($alert->user->username), ['/user/view', 'id' => $alert->user_id], ['target' => '_blank']), ['class' => 'lead']) : Html::tag('div', 'Waiting for an agent: ' . Alert::beautifySeconds((time() - $alert->updated_at)), ['class' => 'small', 'style' => 'margin-top:5px;'])) ?>
        </div>
    </div>
</div>
<hr>