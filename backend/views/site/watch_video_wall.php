<?php

use yii\helpers\Html;
use kartik\switchinput\SwitchInput;
use common\models\PropertyAccessLog;
use yii\data\ActiveDataProvider;
use kartik\grid\GridView;
use common\models\Log;
use rmrevin\yii\fontawesome\component\Icon;

/* @var $this yii\web\View */
/* @var $alert \common\models\Alert */
/* @var $camera \common\models\Camera */
/* @var $property \common\models\Property */

$this->title = 'Video wall - ' . $property->name;

$cameras = [];
foreach ($property->cameras as $curCamera) {
    $cameras[] = $curCamera->getUrlHttp(1);
}
$cameraSourceJson = json_encode($cameras);

$backBtn = Html::a('<i class="fa fa-mail-reply"></i> Back to camera panel', ['/site/watch', 'alert_id' => $alert->id], ['class' => 'prioriry-load', 'style' => 'line-height: 50px;margin-left:50px;']);
/*
$initJs = <<<SCRIPT
        //$('.sidebar-toggle').click();
        $('.navbar-static-top').append('{$backBtn}');
        
        //  Show property cameras
        var cameraArr = JSON.parse('$cameraSourceJson');
        var width = $(".content-wrapper").width();
        var height = $(".content-wrapper").height();
        console.log(width + " " + height + " " + cameraArr.length);
        
        var itemWidth = 0;
        var itemHeight = 0;
        var lineCount = 0;
            
        if(cameraArr.length == 1){
            itemWidth = width;
            lineCount = 1;
        }
        else if(cameraArr.length <= 4){
            itemWidth = Math.round(width/2);
            lineCount = 2;
        }
        else if(cameraArr.length <= 6){
            itemWidth = Math.round(width/3);
            lineCount = 3;
        }
        else{
            itemWidth = Math.round(width/4);
            lineCount = 4;
        }
        itemHeight = itemWidth / 1.78;
        var cameraGrid = "";
        var colNumber = 12 / lineCount;
        cameraArr.forEach(function(item, i) {
            if(i == 0)
                cameraGrid += "<div class='row row-no-padding'>"
            if(i%lineCount == 0)
                cameraGrid += "</div><div class='row row-no-padding'>"
            cameraGrid += '<div class="col-xs-' + colNumber + '"><embed target="' + item + '" class="center-block" height="' + itemHeight + '" width="' + itemWidth + '" id="vlc" name="vlc" pluginspage="http://www.videolan.org" target="URL" type="application/x-vlc-plugin" version="VideoLAN.VLCPlugin.2"/></div>';
            if(i = 0)
                cameraGrid += "</div>"
        });
        $(".body-content").html(cameraGrid);
SCRIPT;
$this->registerJs($initJs, yii\web\View::POS_READY);
*/
$initJs = <<<SCRIPT
        //$('.sidebar-toggle').click();
        $('.navbar-static-top').append('{$backBtn}');
        
        //  Show property cameras
        var cameraArr = JSON.parse('$cameraSourceJson');
        var width = $(".content-wrapper").width();
        var height = $(".content-wrapper").height();
        console.log(width + " " + height + " " + cameraArr.length);
        
        var itemWidth = 0;
        var itemHeight = 0;
        var lineCount = 0;
            
        if(cameraArr.length == 1){
            itemWidth = width;
            lineCount = 1;
        }
        else if(cameraArr.length <= 4){
            itemWidth = Math.round(width/2);
            lineCount = 2;
        }
        else if(cameraArr.length <= 6){
            itemWidth = Math.round(width/3);
            lineCount = 3;
        }
        else{
            itemWidth = Math.round(width/4);
            lineCount = 4;
        }
        itemHeight = itemWidth / 1.78;
        var cameraGrid = "";
        var colNumber = 12 / lineCount;
        cameraArr.forEach(function(item, i) {
            if(i == 0)
                cameraGrid += "<div class='row row-no-padding'>"
            if(i%lineCount == 0)
                cameraGrid += "</div><div class='row row-no-padding'>"
            cameraGrid += '<div class="col-xs-' + colNumber + '"><img src="' + item + '" name="cameraStreamMain" id="cameraStreamMain" class="camera-main-stream"></div>';
            if(i = 0)
                cameraGrid += "</div>"
        });
        $(".body-content").html(cameraGrid);
SCRIPT;
$this->registerJs($initJs, yii\web\View::POS_READY);

?>
<div class="body-content">

</div>