<?php

use common\models\Camera;
use common\models\User;
use common\models\Log;
use common\models\Alert;
use common\models\Alarm;

/* @var $this yii\web\View */

$this->title = 'Dashboard';

$initJS = <<<SCRIPT
   var getUserWorktime = function(){
        $(".user-workhours-loader").show();
        $.get('/user/get-worktime', function(data){
            if(data.result)
                $('#user-worktime-text').html(data.work_hours + ':' + data.work_minutes);
        }).always(function(){ $(".user-workhours-loader").hide();});
   }
   getUserWorktime();
   setInterval(getUserWorktime, 70000);
SCRIPT;
$this->registerJs($initJS, \yii\web\View::POS_READY);
?>
<div class="site-index">


    <div class="body-content">

        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-video-camera"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Last login</span>
                        <span class="info-box-number"><?= Log::getLastLoginTime() ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-bell-o"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Alerts accepted</span>
                        <span class="info-box-number"><?= Alert::getUserAlerts()->count() ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>

            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-bell"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Alarm initiated</span>
                        <span class="info-box-number"><?= Alarm::find()->where(['user_id' => Yii::$app->user->id])->count() ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Current week worktime</span>
                        <span class="info-box-number"><span id="user-worktime-text"></span><i class="fa fa-spinner fa-pulse user-workhours-loader" style="display:none;"></i></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
        </div>

        <div class="box box-info">
            <div class="box-header with-border text-center">
                Notification
            </div>
            <div class="box-body">
                <div class="text-center">If you'd like to work, go to the "Camera panel" and you will receive an alert notification as soon as we detect some motion.</div>
            </div>
        </div>

    </div>
</div>
