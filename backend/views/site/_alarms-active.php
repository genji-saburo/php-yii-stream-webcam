<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Alarm;

$initJS = <<<SCRIPT
   var updateAlarmList = function(){
        $(".update-alarm-list-loader").show();
        $.get('/alarm/get-alarm-list', function(data){
            if(data.result){
                $('.alarm-list').html(data.html);
            }
        }).always(function(){ $(".update-alarm-list-loader").hide();});
   }
   updateAlarmList();
   setInterval(updateAlarmList, 15000);
SCRIPT;
$this->registerJs($initJS, \yii\web\View::POS_READY);
?>


<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Active alarms <i class="fa fa-spinner fa-pulse update-alarm-list-loader" style="display:none;"></i></h3>
        <div class="box-tools pull-right">
            <button data-widget="collapse" class="btn btn-box-tool" type="button"><i class="fa fa-minus"></i>
            </button>
            <!--<button data-widget="remove" class="btn btn-box-tool" type="button"><i class="fa fa-times"></i></button>-->
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <ul class="products-list product-list-in-box alarm-list"></ul>
    </div>
    <!-- /.box-body -->
    <div class="box-footer text-center">
        <a class="uppercase" href="/alarm">View All Alarms</a>
    </div>
    <!-- /.box-footer -->
</div>
<!-- /.box -->