<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use kartik\switchinput\SwitchInput;

/* @var $this yii\web\View */
/* @var $model common\models\Camera */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Cameras', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


/*
  $initJs = <<<SCRIPT
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
  $this->registerJs($initJs, yii\web\View::POS_READY);
 */

$initMjpegJs = <<<SCRIPT
        var highQualityStream = "{$model->getUrlHttp(0)}";
        var lowQualityStream = "{$model->getUrlHttp(1)}";
        var setCameraSource = function(src){
            $('#cameraStream').attr('src', src + "&" + (new Date()).getTime());
        };
        var qualityChange = function(){
            if(!$('#quality-switch').is(':checked'))
                setCameraSource(lowQualityStream);
            else
                setCameraSource(highQualityStream);
            };
        setCameraSource(lowQualityStream);
SCRIPT;
$this->registerJs($initMjpegJs, yii\web\View::POS_READY);
?>
<div class="camera-view container">

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?=
        Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ])
        ?>
    </p>

    <div class="box">
        <div class="box-header with-border">
            <div class="row">
                <div class="col-sm-6">
                    <span>Camera stream </span>
                    <button id="play-btn" class="btn btn-default"><i class="fa fa-refresh"></i></button>
                </div>
                <div class="col-sm-6">
                    <?php
                    echo SwitchInput::widget([
                        'name' => 'camera_quality',
                        'value' => false,
                        'options' => ['id' => 'quality-switch'],
                        'pluginOptions' => [
                            'handleWidth' => 80,
                            'onText' => 'High quality',
                            'offText' => 'Low quality',
                            'onColor' => 'success',
                            'offColor' => 'default',
                        ],
                        'pluginEvents' => [
                            "switchChange.bootstrapSwitch" => "qualityChange",
                        ],
                    ]);
                    ?>
                </div>
            </div>
        </div>

        <div class="box-body">

            <div align="center">


                <img src="<?= $model->getImagePath() ?>" name="cameraStream" id="cameraStream" class="camera-main-stream">

                <!--
                <embed height="400" id="vlc" name="vlc" pluginspage="http://www.videolan.org" target="URL" src="<?= $model->getUrlRTSP() ?>" type="application/x-vlc-plugin" version="VideoLAN.VLCPlugin.2" width="660"></embed> 
                -->

            </div>                   

        </div>
    </div>


</div>
