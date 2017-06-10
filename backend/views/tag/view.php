<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\models\Tag;

/* @var $this yii\web\View */
/* @var $model common\models\Tag */

$this->title = $model->tag_id;
$this->params['breadcrumbs'][] = ['label' => 'Tags', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="tag-view container">

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
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
            Tag data
        </div>

        <div class="box-body">

            <?=
            DetailView::widget([
                'model' => $model,
                'attributes' => [	
                    'id',
                    [
                        'format' => 'html',
                        'label' => 'Property',
                        'value' => ($model->property_id ? Html::a("#{$model->property->id} - {$model->property->name}", ['property/view', 'id' => $model->property->id]) : ""),
                    ],
                    'tag_id',
                    'username',
                    'phone',
                    [
                        'format' => 'html',
                        'label' => 'User Photo',
                        'value' => ($model->image ? Html::img("/upload/tag/". $model->id . '/' . $model->image, ['alt' => $model->username, 'style' => ['width' => '120px']]) : ""),
                    ],
                    [
                        'format' => 'html',
                        'label' => 'Access Level',
                        'value' => ($model->access_level ? Tag::values('access_level')[$model->access_level] : ""),
                    ],
                ],
            ])
            ?>

        </div>
    </div>
</div>