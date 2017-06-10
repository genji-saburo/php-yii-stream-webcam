<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users List';
$this->params['breadcrumbs'][] = $this->title;

$options = common\models\User::getRoleName();
$options[''] = 'All roles';
ksort($options);
?>
<div class="user-index">

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="box">
        <div class="box-header with-border">
            User List
        </div>

        <div class="box-body">

            <?=
            GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'pjax' => true,
                'columns' => [
                    'id',
                    'username',
                    //'auth_key',
                    //'password_hash',
                    //'password_reset_token',
                    'email:email',
                    'status',
                    [
                        'width' => '150px',
                        'vAlign' => 'middle',
                        'attribute' => 'role',
                        'value' => function ($model, $key, $index, $widget) {
                            return $model->getRole();
                        },
                        'filter' => $options,
                        'filterType' => GridView::FILTER_SELECT2,
                        'filterWidgetOptions' => [
                            'pluginOptions' => [
                            //'allowClear' => true,
                            ]
                        ]
                    ],
                    'created_at:datetime',
                    // 'updated_at',
                    [
                        'class' => '\kartik\grid\ActionColumn',
                    ]
                ],
            ]);
            ?>

        </div>
    </div>

</div>
