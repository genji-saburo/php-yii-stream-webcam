<?php

use yii\helpers\Html;
use common\models\Camera;

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

$this->title = "Test page";


$model = Camera::find()->orderBy(['id' => SORT_DESC])->one();
//$model = Camera::findOne(7);

$vlcInitJs = <<<SCRIPT
   $('#play-btn').click(function(){
   setTimeout(function(){ $('#play-btn').find('i').removeClass('fa-spin'); },4000);
        
   $(this).find('i').addClass('fa-spin');   
   var ar = '16:9';
   var options = new Array(
        ':http-caching=3000',
        ':http-reconnect',
        'aspect-ratio=' + ar
    );
    //myvlc = new VLCObject("vlc", "960", "540");
    //myvlc.write("player");
    //play();
    var vlc = document.getElementById("vlc");
    var ch = "{$model->getUrlRTSP()}";
    var id = vlc.playlist.add(ch, null, options);
    vlc.playlist.playItem(id);  
    
    
   });
SCRIPT;

$realInitJs = <<<SCRIPT
     //SET THE RTSP STREAM ADDRESS HERE
var address = "rtsp://admin:Test321$@166.255.152.193:7171/cam/realmonitor?channel=1&subtype=1";

var output = '<object width="640" height="480" id="qt" classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B" codebase="http://www.apple.com/qtactivex/qtplugin.cab">';
    output += '<param name="src" value="'+address+'">';
    output += '<param name="autoplay" value="true">';
    output += '<param name="controller" value="false">';
    output += '<embed id="plejer" name="plejer" src="' + address + '" bgcolor="000000" width="640" height="480" scale="ASPECT" qtsrc="'+address+'"  kioskmode="true" showlogo=false" autoplay="true" controller="false" pluginspage="http://www.apple.com/quicktime/download/">';
    output += '</embed></object>';

    //SET THE DIV'S ID HERE
    document.getElementById("player").innerHTML = output;   
        
        
SCRIPT;
?>
<section class="content">

    <div class="test-page text-center">
        <h2 class="headline text-info"><i class="fa fa-warning text-yellow"></i></h2>

        <div class="error-content">
            <h3>Test samples</h3>

            <div>
                <?php
                switch ($type) {
                    case "pure":
                        //echo Html::tag('video', '<source src="' . $model->getUrlHttp() . '">', ['autoplay' => 'autoplay', 'height' => 480, 'width' => '640']);
                        //echo Html::tag('img', '', [ 'src' => $model->getUrlHttp(), 'style' => 'width:100%;']);
                        //echo Html::tag('iframe', '', [ 'src' => $model->getUrlHttp(), 'height' => 480, 'width' => '640']);
                        //echo Html::tag('iframe', '', [ 'src' => "http://admin:DanielPass5@166.255.152.193:7575/cgi-bin/mjpg/video.cgi?subtype=1", 'height' => 480, 'width' => '640']);
                        //echo '<video autoplay="true" id="videoElement" src="' . $model->getUrlHttp() . '"></video>';
                        //echo '<embed src="' . $model->getUrlHttp() . '" HEIGHT="100%" WIDTH="100%" TYPE="video/quicktime" PLUGINSPAGE="http://www.apple.com/quicktime/download/" AUTOPLAY="true" CONTROLLER="true" SCALE = "Aspect" />';
                        break;
                    case "real":
                        $this->registerJs($realInitJs, yii\web\View::POS_READY);
                        echo Html::tag('div', '', ['id' => 'player']);
                        break;
                    case "cambozola":
                        echo Html::tag('applete', '<param name="url" value="' . $model->getUrlHttp() . '">', ['code' => 'com.charliemouse.cambozola.Viewer', 'archive' => '/java/cambozola.jar', 'height' => 480, 'width' => '640']);
                        break;
                    default:
                        $this->registerJs($vlcInitJs, yii\web\View::POS_READY);
                        echo '<embed height="400" id="vlc" name="vlc" pluginspage="http://www.videolan.org" target="URL" src="' . $model->getUrlRTSP() . '" type="application/x-vlc-plugin" version="VideoLAN.VLCPlugin.2" width="660"></embed> ';
                        break;
                }
                ?>
            </div>

        </div>

        <!--
        
        <img src="http://patroleum.net:8090/cam1.mjpg" class="img img-responsive"/>
        
        <img src="http://patroleum.net:8090/cam2.mjpg" class="img img-responsive"/>
        
        -->

        <canvas id="video-canvas"></canvas>
        <script type="text/javascript" src="/js/mjpeg.js"></script>
        <script type="text/javascript">
            var player = new MJPEG.Player("video-canvas", "http://system:password1@166.255.152.193:7171/cgi-bin/mjpg/video.cgi?subtype=1");
            player.start();
        </script>


    </div>

</section>
