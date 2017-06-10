<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */

$vlcJS = <<<SCRIPT
$(document).on('click', '.play-all-btn', function(e){
    e.preventDefault();
    $('embed').each(function(){
        this.playlist.play();
    })
    $.notifyClose('all');    
});        
$('.prioriry-load').click(function(){      
    var pausedCount = 0;    
    $.notifyClose('all');        
    $('embed').each(function(){
        pausedCount++;
        this.playlist.pause();
    })
    if(pausedCount > 0)    
        $.notify({
                    icon: 'fa fa-warning',
                    title: "All video streams has been paused<br>",
                    message: 'To <a href="#" class="play-all-btn">click here</a> to start video streaming again.',
               },{
                   delay: 0,
                   placement: {
			from: "bottom",
			align: "right"
                    }, 
                   type: 'info'
                   });
});
SCRIPT;
//  Disable while don't use vlc
//$this->registerJs($vlcJS, \yii\web\View::POS_READY);