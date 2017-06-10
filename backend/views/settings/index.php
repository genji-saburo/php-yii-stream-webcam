<?php

use yii\helpers\Html;
use common\models\Camera;
use common\models\User;
use common\models\Log;
use common\models\CameraLog;
use common\models\UserRestriction;
use kartik\grid\GridView;

/* @var $this yii\web\View */

$this->title = 'Settings';

$initJS = <<<SCRIPT
    $('.add-user-restriction').click(function(){   
        $('#modal-alert').addClass('hidden');
        $.post('/settings/add-user-restriction', {'UserRestriction[action]': $('#action').val(), 'UserRestriction[ip]': $('#ip').val()},
            function(data){
                if(data.result){
                    $.pjax.reload({container: '#restrictions-grid-pjax'});   
                    $('#user-restriction-modal').modal('hide');
                    $('#action').val("");
                    $('#ip').val("");
                }else{
                    if(data.errors){
                        $.each(data.errors, function(index, value){
                            $('#modal-alert').html(index + ": " + value);
                        });
                        $('#modal-alert').removeClass('hidden');
                    }
                }
            }    
        )
    });
    $(document).on('click', '.restrictions-grid-action-del', function(event){
        var url = $(this).data('url');
        $.get(url, function(){}).always(function(){
            $.pjax.reload({container: '#restrictions-grid-pjax'});
        });
    });
SCRIPT;

$this->registerJs($initJS, \yii\web\View::POS_READY);
?>
<div class="site-index">
    <div class="body-content">

        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">User Restrictions</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#user-restriction-modal">
                        <i class="fa fa-plus"></i> Add Rule
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <?=
                GridView::widget([
                    'id' => 'restrictions-grid',
                    'pjax' => true,
                    'dataProvider' => $dataProvider,
                    'columns' => [
                        'ip',
                        [
                            'attribute' => 'action',
                            'format' => 'html',
                            'label' => 'Rule Type',
                            'value' => function($model){return $model->action ? 'Allow' : 'Block';}
                        ],
                        'created_at:datetime',
                        [
                            'class' => '\kartik\grid\ActionColumn',
                            'buttons' => [
                                'update' => function ($url, $model) {
                                    
                                },
                                'view' => function ($url, $model) {
                                    
                                },
                                'delete' => function ($url, $model) {
                                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', '#', ['class' => "restrictions-grid-action-del", 'data-url' => '/settings/delete-user-restriction?id=' . $model->id]);
                                }
                                    ]
                                ],
                            ],
                        ]);
                        ?>
                    </div>
                </div>
                <!-- /.box -->

            </div>
        </div>

        <!-- UserResriction Modal -->
        <div class="modal fade" id="user-restriction-modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title">Ip Restriction Rule</h4>
                    </div>
                    <div class="modal-body">
        <?php \yii\widgets\MaskedInput::widget(['name' => 'ip', 'mask' => '999\.999\.999\.999', 'id' => 'ip']) ?>
                        <label>Ip mask (use 0 to fit any digit)</label>
                        <input type="text" id="ip" class="form-control">
                        <label>Rule type</label>
        <?= Html::dropDownList('action', '0', ['0' => 'Block', '1' => 'Allow'], ['id' => 'action', 'class' => 'form-control']) ?>
                <div class="label label-danger hidden" id="modal-alert"></div>
                <div>* - allow rule less powerfull than block</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary add-user-restriction">Add</button>
            </div>
        </div>
    </div>
</div>