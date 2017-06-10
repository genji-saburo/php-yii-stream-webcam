<?php

use yii\helpers\Html;
use kartik\switchinput\SwitchInput;
use common\models\PropertyAccessLog;
use yii\data\ActiveDataProvider;
use kartik\grid\GridView;
use common\models\Log;

/* @var $this yii\web\View */
/* @var $alert \common\models\Alert */
/* @var $camera \common\models\Camera */
/* @var $property \common\models\Property */

$this->render('/camera/_vlc-js');

$this->title = 'Property dashboard ' . ($alert ? ' - Alert #' . $alert->id : "");

/*
  //  VCL player JS
  $initJs = <<<SCRIPT
  //   Logging options

  var logCameraView = function(){
  $.post("/log/write",{name: "Camera view", alert_id: {$alert->id}, camera_id: {$camera->id},property_id: {$camera->property_id}, details: JSON.stringify({camera_id: {$camera->id}})});
  }

  setInterval(logCameraView,10000);

  //   Camera options

  var hignQualitySrc = "{$camera->getUrlRTSP(0)}";
  var lowQualitySrc = "{$camera->getUrlRTSP(1)}";

  var setCameraSource = function(src){
  setTimeout(function(){ $('#play-btn').find('i').removeClass('fa-spin'); },4000);
  $('#play-btn').find('i').addClass('fa-spin');
  var ar = '16:9';
  var options = new Array(
  ':http-caching=3000',
  ':http-reconnect',
  'aspect-ratio=' + ar
  );
  //play();
  var vlc = document.getElementById("vlc");
  var ch = src;
  var id = vlc.playlist.add(ch, null, options);
  vlc.playlist.playItem(id);
  }

  $('#play-btn').click(function(){
  if($('#quality-switch').is(':checked'))
  setCameraSource(hignQualitySrc);
  else
  setCameraSource(lowQualitySrc);
  });

  var cameraThumbnailUpdate = function(){
  if($(".thumbnail-camera[data-status='update']").size() == 0){
  $(".thumbnail-camera").each(function(index,el){
  var src = $(el).data("camera");
  $(el).attr("data-status", "update");
  $.post("/camera/image-refresh", {id: src}, function(data){
  if(data.result == "success")
  $(el).attr("src", data.src);
  }).always(function(){
  $(el).attr("data-status", "done");
  });
  });
  }
  };

  //cameraThumbnailUpdate();
  setInterval(cameraThumbnailUpdate, 60000);

  var qualityChange = function(){
  if(!$('#quality-switch').is(':checked'))
  setCameraSource(hignQualitySrc);
  else
  setCameraSource(lowQualitySrc);
  };

  $('.resolved-btn').click(function(){
  var clickedBtnEl = $(this);
  clickedBtnEl.html('<i class="fa fa-spinner fa-spin"></i> Watching finished');
  $.get('/alert/close?alertId=' + {$alert->id}, function(data){
  if(data.result)
  window.location.href = "/watch";
  else{
  clickedBtnEl.html('<i class="fa thumbs-up"></i> Watching finished');
  $.notify({
  // options
  icon: 'glyphicon glyphicon-warning-sign',
  title: 'Alert closing error',
  message: 'Unexpeted error while closing alert',
  },{type: 'danger'});
  }
  }).fail(function(){
  clickedBtnEl.html('<i class="fa thumbs-up"></i> Watching finished');
  });
  });

  $('.skip-btn').click(function(){
  var clickedBtnEl = $(this);
  clickedBtnEl.html('<i class="fa fa-spinner fa-spin"></i> Skip');
  $.get('/alert/skip?alertId=' + {$alert->id}, function(data){
  if(data.result)
  window.location.href = "/watch";
  else{
  clickedBtnEl.html('<i class="fa fa-close"></i> Skip');
  $.notify({
  // options
  icon: 'glyphicon glyphicon-warning-sign',
  title: 'Alert closing error',
  message: 'Unexpeted error while skipping',
  },{type: 'danger'});
  }
  }).fail(function(){
  clickedBtnEl.html('<i class="fa fa-close"></i> Skip');
  });
  });

  $('.alarm-btn').click(function(){
  $('#alarm-modal').modal('show');
  });

  // Set default config
  setCameraSource(lowQualitySrc);

  SCRIPT;
  $this->registerJs($initJs, yii\web\View::POS_READY);
 */
$wsDomain = Yii::$app->params['WS_DOMAIN'];
$wsPORT = Yii::$app->params['WS_PORT'];
$pinNotificationTimeInterval = PropertyAccessLog::PIN_TYPING_INTERVAL;
$pinSecondsPassed = 1;//$property->propertyAccessLog->getTimePassed(false);
$serverDatetime = new DateTime(null, new DateTimeZone(\Yii::$app->formatter->timeZone));
$serverTime = ((new DateTime(null))->getTimestamp() + $serverDatetime->getOffset()) * 1000;
$initJS = <<<SCRIPT
   var serverTime = new Date({$serverTime});     
   
   var updateServerTime = function(){
       serverTime = new Date(serverTime.getTime() + 1000);
       $('.server-time').html(serverTime.getUTCHours()+":"+serverTime.getUTCMinutes()+":"+serverTime.getSeconds());
   };
   setInterval(updateServerTime,1000);
   
   var timePassedBeautify = function(secondsPassed){
       if(secondsPassed < 60)
            return secondsPassed + " s. ago";
       else if(secondsPassed < 60 * 60){
           return Math.floor(secondsPassed / 60) + " m. " + ( secondsPassed % 60) + " s. ago";
       }
       else if(secondsPassed < 24 * 60 * 60){
           return (Math.floor(secondsPassed / 60 * 60)) + "h. ago";
       }
       else{
           return (Math.floor(secondsPassed / (24 * 60 * 60))) + ' day ago';
       }
   };
   var timepassed = $pinSecondsPassed;
   var updatePinTime = function(){
        $('.pin-time').html(timePassedBeautify(timepassed));
        timepassed++;
        if(timepassed > {$pinNotificationTimeInterval})
        $('.pin-notification').removeClass("hidden");
   };        
   setInterval(updatePinTime,1000);
   updatePinTime();        
   
   //   Logging options        
   var logCameraView = function(){
       $.post("/log/write",{name: "Camera view", alert_id: {$alert->id}, camera_id: {$camera->id},property_id: {$camera->property_id}, details: JSON.stringify({camera_id: {$camera->id}})});
   }         
   setInterval(logCameraView,10000);
       
   var pingTypingTime = (new Date()).getTime();    
   var pinTypingFunction = function(){
       $('.pin-typing').removeClass('hidden');
       //$('.pin-typing').effect( "shake", {direction: 'up', distance: 3, times: 1} );
       pingTypingTime = (new Date()).getTime();
       setTimeout(function(){
           if((new Date()).getTime() - pingTypingTime  > 3000){
               $('.pin-typing').addClass('hidden');
           }
        }, 3100);
   };    
       
   //   Add real time connection
   var subscribeWatch = function(){
       if(window.conn === undefined)
            return;
       window.clearTimeout(subscribeTimeoutHandler);
	   
       window.conn.subscribe('property_{$camera->property_id}_watch', function(topic, message) {
                    if(typeof(message) === "string")
                        data = JSON.parse(message);
                    else
                        data = message;
                    if(data.params.pinTyping)
                        pinTypingFunction();
                    if(data.params.logUpdate){
                        $.pjax.reload({container: '#log-grid-pjax'});
                    }
                    if(data.params.connectionCount)
                        $('.connection-count').html(data.params.connectionCount);
                    if(data.params.pin){
                        if(data.params.pin.required)
                            $('.pin-notification').removeClass('hidden');
                        else
                            $('.pin-notification').addClass('hidden');
                        if(data.params.pin.status ){    
                            $('.pin-status').html('PIN Correct');
                            $('.pin-status').addClass('label-success');
                            $('.pin-status').removeClass('label-danger');
                        }
                        else{
                            $('.pin-status').html('PIN Incorrect');
                            $('.pin-status').removeClass('label-success');
                            $('.pin-status').addClass('label-danger');
                        }
                        timepassed = data.params.pin.timepassed;
                    }
                    if(data.params.comment){
                        $('.comments-block').prepend(data.params.comment.html);
                    }
                    console.log(data);
        });    
  };
  var subscribeTimeoutHandler = setTimeout(subscribeWatch, 1000);
        
   //   Camera options
   
   var hignQualitySrc = "{$camera->getUrlHttp(0)}"; 
   var lowQualitySrc = "{$camera->getUrlHttp(1)}"; 
        
   var setCameraSource = function(src){
        $('#cameraStreamMain').attr('src', src + "&" + (new Date()).getTime());
   };
   
   $('#play-btn').click(function(){
        if($('#quality-switch').is(':checked'))
            setCameraSource(lowQualitySrc);
        else
            setCameraSource(hignQualitySrc);
   });
   
   var cameraThumbnailUpdate = function(){
        if($(".thumbnail-camera[data-status='update']").size() == 0){ 
            $(".thumbnail-camera").each(function(index,el){
                var src = $(el).data("camera");
                $(el).attr("data-status", "update");
                $.post("/camera/image-refresh", {id: src}, function(data){
                    if(data.result == "success")
                        $(el).attr("src", data.src);
                }).always(function(){
                    $(el).attr("data-status", "done");
                });
            });
        }
   };
   
   //cameraThumbnailUpdate();
   setInterval(cameraThumbnailUpdate, 60000);
    
   var qualityChange = function(){
       if(!$('#quality-switch').is(':checked'))
            setCameraSource(lowQualitySrc);
        else
            setCameraSource(hignQualitySrc);
   };
   
   $('.resolved-btn').click(function(){
       var clickedBtnEl = $(this);
       clickedBtnEl.html('<i class="fa fa-spinner fa-spin"></i> Watching finished'); 
       $.get('/alert/close?alertId=' + {$alert->id}, function(data){
           if(data.result)
                window.location.href = "/watch";
           else{
               clickedBtnEl.html('<i class="fa thumbs-up"></i> Watching finished');
               $.notify({
                    // options
                    icon: 'glyphicon glyphicon-warning-sign',
                    title: 'Alert closing error',
                    message: 'Unexpeted error while closing alert',
               },{type: 'danger'});
           }                
       }).fail(function(){
           clickedBtnEl.html('<i class="fa thumbs-up"></i> Watching finished');
       });
   });
       
   $('.skip-btn').click(function(){
       var clickedBtnEl = $(this);
       clickedBtnEl.html('<i class="fa fa-spinner fa-spin"></i> Skip'); 
       $.get('/alert/skip?alertId=' + {$alert->id}, function(data){
           if(data.result)
                window.location.href = "/watch";
           else{
               clickedBtnEl.html('<i class="fa fa-close"></i> Skip');
               $.notify({
                    // options
                    icon: 'glyphicon glyphicon-warning-sign',
                    title: 'Alert closing error',
                    message: 'Unexpeted error while skipping',
               },{type: 'danger'});
           }                
       }).fail(function(){
           clickedBtnEl.html('<i class="fa fa-close"></i> Skip');
       });
   });    
       
    $('.alarm-btn').click(function(){
        $('#alarm-modal').modal('show');
    });   
   
   // Set default config
   setCameraSource(lowQualitySrc);  
       
   var addCommentFunction = function(){
       var message = $('.comment-create-text').val();
       $('.comment-create-text').val("");
       if(message)
            $.post('/camera/comment', {action: 'add', 'message': message, 'camera_id': {$camera->id}}, function(data){
               if(!data.result){
                   $('.comment-create-text').val(message);
                   $.notify({
                            icon: 'fa fa-warning',
                            title: "Add comment error<br>",
                            message: "No possible to add your comment",
                        },{
                            delay: 5000,
                            placement: {
                                from: "bottom",
                            align: "right"
                        }, 
                            type: 'danger'
                        });
                }
            }).error(function(){
                $('.comment-create-text').val(message);
                $.notify({
                            icon: 'fa fa-warning',
                            title: "Add comment error<br>",
                            message: "No server connection at the moment, please try later",
                        },{
                            delay: 5000,
                            placement: {
                                from: "bottom",
                            align: "right"
                        }, 
                            type: 'danger'
                        });
            });
   };    
   $('.comment-create-btn').click(addCommentFunction); 
   $('.comment-create-text').keypress(function(e) {
    if(e.which == 13) {
        addCommentFunction();
    }
});
		
		//   Add real time connection
   var subscribeUserTag = function(){
       if(window.conn === undefined)
            return;
       window.clearTimeout(subscribeTimeoutHandlerUserTag);
       
       window.conn.subscribe('find_tag_log', function(topic, message) {
			if(typeof(message) === "string")
				data = JSON.parse(message);
			else
				data = message;
			if (data.alert_id.length != 0) {
				$('.user-find-tag').empty();
				if(data.alert_id.authorised.length != 0) {
					$.each(data.alert_id.authorised, function(index, value){
						if(value["image"] == '') {
							var image = '/img/User-100.png';
						} else {
							var image = '/upload/tag/' + value["id"] + '/' + value["image"];
						}
						$('.user-find-tag').append('<p style="background:#ecf5ef;"><img src="' + image + '" style="width:50px;height:100%"> - ' + value["username"]+ '</p>');
					});
				}
				if(data.alert_id.notauthorised.length != 0) {
					$.each(data.alert_id.notauthorised, function(index, value){
						if(value["image"] == '') {
							var image = '/img/User-100.png';
						} else {
							var image = '/upload/tag/' + value["id"] + '/' + value["image"];
						}
						$('.user-find-tag').append('<p style="background:#f1d1d1;"><img src="' + image + '" style="width:50px;height:100%"> - ' + value["username"]+ '</p>');
					});
				}
			}
		});   
  };
  var subscribeTimeoutHandlerUserTag = setTimeout (subscribeUserTag, 1000);
SCRIPT;
$this->registerJs($initJS, \yii\web\View::POS_READY);

//  Add required modals
echo $this->render('_alarm-modal', ['alert' => $alert]);
?>
<div class="body-content container">

    <div class="box box-info">
        <div class="box-header with-border">
            <div class="row">
                <div class="col-sm-3">Name: <?= $property->owner_name ?></div>
                <div class="col-sm-3">Address: <?= $property->address ?></div>
                <div class="col-sm-3">Phone: <?= $property->phone1 ?><?= ($property->phone2 ? ", " . $property->phone2 : "") ?><?= ($property->phone3 ? ", " . $property->phone3 : "") ?></div>
                <div class="col-sm-3">Police: <?= $property->phone_police ?></div>
            </div>
        </div>

        <div class="box-body">
            <div class="row">
                <div class="col-sm-3"><?= Html::a('Video wall', ['/site/watch', 'alert_id' => $alert->id, 'video_wall' => 1], ['class' => 'prioriry-load']) ?> | Camera stream: <button id="play-btn" class="btn btn-default prioriry-load"><i class="fa fa-refresh"></i></button></div>
                <div class="col-sm-3">Connections: <strong class="connection-count"><?= Log::find()->andWhere(['name' => Log::LOG_CAMERA_VIEW, 'camera_id' => $camera->id])->andWhere("created_at > UNIX_TIMESTAMP(NOW()) - 30")->groupBy('user_id')->count(); ?></strong> Access Panel</div>
                <div class="col-sm-3">
                    <span>
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
                    </span>
                </div>
                <div class="col-sm-3">
                    <?= Html::button('<i class="fa fa-thumbs-up"></i> Watching finished', ['class' => 'btn btn-success resolved-btn prioriry-load', 'title' => 'Mark resolved', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom']) ?>
                    <?= Html::button('<i class="fa fa-close"></i> Skip', ['class' => 'btn btn-danger skip-btn prioriry-load', 'title' => 'Send to another agent', 'data-toggle' => 'tooltip', 'data-placement' => 'bottom']) ?>
                </div>
            </div>

            <div class="row" style="margin-top: 10px;">
                <div class="col-sm-8">

                    <div class="box text-center">
                        <div class="box-header with-border">
                            <div class="text-center"><div><?= $alert->camera->name ?></div><div class="pull-right server-time"><?= Yii::$app->formatter->asDatetime(time(), 'yyyy-MM-dd HH:mm:ss') ?></div></div>
                        </div>
                        <div class="box-body">

                            <img src="<?= $camera->getImagePath() ?>" name="cameraStreamMain" id="cameraStreamMain" class="camera-main-stream">

                            <!--
                            <embed class="center-block" height="400" width="712" id="vlc" name="vlc" pluginspage="http://www.videolan.org" target="URL" type="application/x-vlc-plugin" version="VideoLAN.VLCPlugin.2"/> 
                            -->
                        </div>
                    </div>


                </div>
                <div class="col-sm-4">
                    <div class="box text-center">
                        <div class="box-header with-border">
                            Real time pin verification
                        </div>
                        <div class="box-body">
                            <div class="panel panel-danger pin-notification <?= ($property->propertyAccessLog && $property->propertyAccessLog->getTimePassed(false) > PropertyAccessLog::PIN_TYPING_INTERVAL ? '' : 'hidden') ?>">
                                <div class="panel-heading blur-effect">
                                    <i class="fa fa-warning"></i> WAITING FOR PIN
                                </div>
                            </div>
                            <div>
                                <?php
                                if ($property->propertyAccessLog) {
                                    echo Html::tag('span', $property->propertyAccessLog->getTimePassed() . ' ago', ['class' => 'pin-time']) . ' ';
                                    if ($property->propertyAccessLog->check_result)
                                        echo Html::tag('span', "Correct PIN", ['class' => 'label label-success pin-status']);
                                    else
                                        echo Html::tag('span', "Incorrect PIN", ['class' => 'label label-danger pin-status']);
                                    echo ' ' . Html::tag('span', 'Typing now...', ['class' => 'label label-default blur-effect hidden pin-typing']);
                                } else {
                                    echo Html::tag('span', 'Never accessed', ['class' => 'pin-time']) . ' ';
                                    echo Html::tag('span', "Neve entered", ['class' => 'label label-success pin-status']);
                                    echo ' ' . Html::tag('span', 'Typing now...', ['class' => 'label label-default blur-effect hidden pin-typing']);
                                }
                                ?>
                            </div>
                        </div>
                    </div>

                    <?php
                    echo Html::tag('div', $this->render('_call-emergency-twilio', ['camera' => $camera]), ['class' => 'collapse', 'id' => 'emergency-block']);
                    ?>

                    <div class="row row-fluid">
                        <div class="col-xs-6">
                            <button class="btn btn-warning btn-block btn-lg">SUSPECIOUS</button>
                        </div>
                        <div class="col-xs-6">
                            <button class="btn btn-danger btn-block btn-lg" data-toggle="collapse" data-target="#emergency-block">EMERGENCY</button>
                        </div>
                    </div>
                    <div class="row row-fluid margin-top-block-md">
                        <div class="col-xs-6">
                            <button class="btn btn-default btn-block btn-lg">TALK</button>
                        </div>
                        <div class="col-xs-6">
                            <button class="btn btn-info btn-block btn-lg alarm-btn">ALARM</button>
                        </div>
                    </div>
                    <div class="user-find-tag panel panel-default text-center margin-top-block-md" style="height:auto; padding: 20px 20px 0; text-align: left;">
                        
                    </div>
                    <div class="panel panel-default text-center margin-top-block-md" style="height:50px;">
                        {VIDEO HISTORY}
                    </div>

                </div>               
            </div>

            <div class="row">
                <div class="col-sm-8">
                    <div class="box text-center">
                        <div class="box-header with-border">
                            <?= ($property->getCameras()->count() - 1) ?> other cameras associated with this property
                        </div>
                        <div class="box-body">
                            <?php
                            if (($property->getCameras()->count() - 1) === 0) {
                                echo "Nothing to show";
                            } else {
                                foreach ($property->getCameras()->all() as $curCamera) {
                                    if ($camera->id != $curCamera->id) {
                                        echo Html::tag('div', Html::a(Html::img($curCamera->getImagePath(), ['class' => 'img img-responsive img-thumbnail thumbnail-camera prioriry-load', 'data-camera' => $curCamera->id]), ['site/watch', 'alert_id' => $alert->id, 'camera_id' => $curCamera->id]) . Html::tag('div', $curCamera->name), ['class' => 'col-sm-3 text-center']);
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <div class="box text-center">
                        <div class="box-header with-border">
                            Recent comments
                        </div>
                        <div class="box-body">
                            <div class="comment-create-block">
                                <div class="input-group">
                                    <input type="text" class="form-control comment-create-text" placeholder="Comment text...">
                                    <span class="input-group-btn">
                                        <button class="btn btn-primary comment-create-btn" type="button">Add comment</button>
                                    </span>
                                </div><!-- /input-group -->
                            </div>
                            <div class="comments-block">
                                <?php
                                if ($property->getComments()->count() > 0) {
                                    foreach ($property->getComments()->limit(15)->all() as $curComment) {
                                        echo Html::tag('div', Html::tag('div', Html::tag('div', Html::tag('div', Html::tag('i', '', ['class' => 'fa fa-user']), ['class' => 'info-box-icon bg-gray'])
                                                        , ['class' => 'text-center']) . Html::tag('div', ucfirst($curComment->user->username), [])
                                                        , ['class' => 'col-sm-2']) .
                                                        Html::tag('div', 
                                                                Html::tag('div', Yii::$app->formatter->asDatetime($curComment->created_at)) .
                                                                Html::tag('div', $curComment->message, ['class' => 'well text-left'])
                                                        , ['class' => 'col-sm-10'])
                                                , ['class' => 'row margin-top-block-md']);
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="box text-center">
                        <div class="box-header with-border">
                            Property logs
                        </div>
                        <div class="box-body">
                            <?php
                            /* Show Property Logs */
                            $logProvider = new ActiveDataProvider(['query' => PropertyAccessLog::find(['proprerty_id' => $property->id])->orderBy(['id' => SORT_DESC])]);
                            $logProvider->setPagination(new yii\data\Pagination(['defaultPageSize' => 5]));
                            echo GridView::widget([
                                'id' => 'log-grid',
                                'pjax' => true,
                                'dataProvider' => $logProvider,
                                'columns' => [
                                    [
                                        'attribute' => 'check_result',
                                        'width' => '100px',
                                        'value' => function($model) {
                                            return ($model->check_result ? "Success" : "Failed");
                                        },
                                    ],
                                    'attribute' => 'created_at:datetime',
                                ]
                            ]);
                            ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>