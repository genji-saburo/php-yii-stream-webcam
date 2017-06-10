<?php

use yii\helpers\Html;
use common\models\Camera;
use common\models\User;
use common\models\Log;
use common\models\CameraLog;

/* @var $this yii\web\View */

$this->title = 'Dashboard';
?>
<div class="site-index">


    <div class="body-content">

        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-video-camera"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text"><?= Html::a('Cameras', '/camera/index') ?> (online/all)</span>
                        <span class="info-box-number camera-count"><i class="fa fa-spinner fa-pulse"></i></span>
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
                        <span class="info-box-text"><?= Html::a('Agents', ['/user/index']) ?> (Online/Watch/All)</span>
                        <span class="info-box-number">
                            <?php
                            echo Log::getCount(Log::LOG_USER_ONLINE, 60) . '/' .
                            Log::getCount(Log::LOG_CAMERA_VIEW, 60) . '/' .
                            User::find()->count() . Html::tag('br');
                            ?></span>
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
                        <span class="info-box-text">Alerts last 24h</span>
                        <span class="info-box-number"><?= \common\models\Alert::find()->where("(UNIX_TIMESTAMP(NOW()) - 86400) < updated_at")->count() ?></span>
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
                        <span class="info-box-text">Property count</span>
                        <span class="info-box-number"><?= \common\models\Property::find()->count() ?></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
        </div>

        <?php echo $this->render('panel_map_locations') ?>

    </div>
</div>
