<?php

use yii\helpers\Html;
use kartik\grid\GridView;

/* @var $this yii\web\View */

$this->title = 'Video Queuing';
$this->params['breadcrumbs'][] = $this->title;

$initJS = <<<SCRIPT
   var updateAlertList = function(){
        $.get('/alert/get-alert-queue', function(data){
                if(data.result){
                    $('.alert-list').html(data.html);
                    $('.label-pending-alerts').html(data.statistics.pending);
                    $('.label-watching-alerts').html(data.statistics.watching);
                    $('.label-answer-time-24h').html(data.statistics.answer_24h);
                    $('.label-watch-time').html(data.statistics.answer_overall);
                }
        });
   }
   updateAlertList();
   setInterval(updateAlertList, 10000);
SCRIPT;
$this->registerJs($initJS, \yii\web\View::POS_READY);
?>

<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-video-camera"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Alerts Pending</span>
                <span class="info-box-number label-pending-alerts"></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-users"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Alerts Watching</span>
                <span class="info-box-number label-watching-alerts"></span>
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
                <span class="info-box-text">24h avg. answer time</span>
                <span class="info-box-number label-answer-time-24h"></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-home"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">Avg. watch time</span>
                <span class="info-box-number label-watch-time"></span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
</div>

<div class="alert-index">
    <div class="box">
        <div class="box-header with-border">
            Last 24h video queuing
        </div>

        <div class="box-body alert-list">

        </div>
    </div>
</div>
