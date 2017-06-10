<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Alarm;

$initJS = <<<SCRIPT
   var updateDashboard = function(){
        $(".update-dashboard-loader").show();
        $.get('/site/dashboard-data', function(data){
            if(data.result && data.CameraLog){
                $('.camera-count').html(data.CameraLog.online + ' / ' + data.CameraLog.all);
                $('.cameras-offline').html(data.CameraLog.html);
            }
        }).always(function(){ $(".update-dashboard-loader").hide();});
   }
   updateDashboard();
   setInterval(updateDashboard, 60000);
SCRIPT;
$this->registerJs($initJS, \yii\web\View::POS_READY);
?>


<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Offline camera list <i class="fa fa-spinner fa-pulse update-dashboard-loader" style="display:none;"></i></h3>
        <div class="box-tools pull-right">
            <button data-widget="collapse" class="btn btn-box-tool" type="button"><i class="fa fa-minus"></i>
            </button>
            <!--<button data-widget="remove" class="btn btn-box-tool" type="button"><i class="fa fa-times"></i></button>-->
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <ul class="products-list product-list-in-box cameras-offline"></ul>
    </div>
    <!-- /.box-body -->
    <div class="box-footer text-center">
        <a class="uppercase" href="/camera">View All Cameras</a>
    </div>
    <!-- /.box-footer -->
</div>
<!-- /.box -->