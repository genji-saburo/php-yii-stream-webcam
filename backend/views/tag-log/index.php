<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\TagLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Tag Logs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="tag-log-index">

    <div class="box">
        <div class="box-header with-border">
            Tag Log List
        </div>
        <div class="box-body">
            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    
                    'id',
                    'tag_id',
                    'reader_id',
                    'is_authorised',
                    'created_at:datetime',
                    'updated_at:datetime',
                    [
                        'class' => '\kartik\grid\ActionColumn',
                        'buttons' => [
                            'delete' => function ($url, $model) {
                                return '';
                            },
                            'update' => function ($url, $model) {
                                return '';
                            },
                        ],
                    ]
                ],
            ]);
            ?>
        </div>
    </div>
</div>

