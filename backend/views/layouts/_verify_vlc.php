<?php
/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$initJs = <<<SCRIPT
    window.onload = function(){    
        var isVLCInstalled = function() {
        var name = "VLC";
        if (navigator.plugins && (navigator.plugins.length > 0)) {
            for(var i=0;i<navigator.plugins.length;++i) 
                if (navigator.plugins[i].name.indexOf(name) != -1) 
                return true;
        }
        else {
            try {
                new ActiveXObject("VideoLAN.VLCPlugin.2");
                return true;
            } catch (err) {}
        }
        return false;
    }
    if(!isVLCInstalled())
        $("#modal-vlc-plugin").modal();
    }
SCRIPT;
//  Disable verification while don't use VLC
//$this->registerJs($initJs, yii\web\View::POS_HEAD);
?>

<div class = "modal fade" id="modal-vlc-plugin">
    <div class = "modal-dialog">
        <div class = "modal-content">
            <div class = "modal-header">
                <h4 class = "modal-title">Warning</h4>
            </div>
            <div class = "modal-body">Your browser is missing VCL plugin required for proper system work. Please install it before continue.</div>
            <div class="modal-footer">
                <button type="button" onclick="window.open('http://www.videolan.org/vlc/','_blank');" class="btn btn-primary" data-dismiss="modal">Install now</button>
                <button type="button" class="btn btn-link" data-dismiss="modal">Skip</button>
            </div>
        </div>
    </div>
</div>