<?php

/* @var $this yii\web\View */
/* @var $alerts common\models\Alert */

use yii\helpers\Html;
use yii\widgets\ActiveForm;


$alarmType = common\models\Log::LOG_ALARM_USER_INIT;
$initJs = <<<SCRIPT
    $('.alarm-enable-btn').click(function(){
       var clickedBtnEl = $(this);
       clickedBtnEl.html('<i class="fa fa-spinner fa-spin"></i> Enable alarm'); 
       $.post('/alarm/add-alarm?alertId=' + {$alert->id}, {type: "{$alarmType}"} , function(data){
           if(data.result)
                $.notify({
                    // options
                    icon: 'fa fa-warning',
                    title: 'Alert successfully enabled',
                    message: 'Alert has been enabled. All required notifications has been sent.',
               },{type: 'success'});
           else{
               $.notify({
                    // options
                    icon: 'fa fa-warning',
                    title: 'Alert closing error',
                    message: 'Unexpeted error while skipping',
               },{type: 'danger'});
           }                
       }).always(function(){
           clickedBtnEl.html('Enable alarm');
           $('#alarm-modal').modal('hide');
       });
   });
SCRIPT;
$this->registerJs($initJs, yii\web\View::POS_READY);
?>

<div class = "modal fade" id="alarm-modal">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Confirmation</h4>
            </div>
            <div class = "modal-body">You are to enable alarm and send notifications to the owner and police department. Are you sure you want to do that?</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary alarm-enable-btn">Enable alarm</button>
                <button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>