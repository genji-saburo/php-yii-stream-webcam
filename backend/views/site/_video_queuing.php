<?php
/* @var $this yii\web\View */
/* @var $alerts common\models\Alert */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$initJS = <<<SCRIPT
   var alertChart;
   var currentStat;
   var drawChart = function(statData){
       if(!compareArrays(currentStat, statData)){
            currentStat = statData;
            alertChart = Highcharts.chart('alerts-chart', {
            chart: {
                plotBackgroundColor: null,
                plotBorderWidth: 0,
                plotShadow: false
            },
            title: {
                text: 'Alerts States',
                align: 'center',
                verticalAlign: 'middle',
                y: 60
            },
            tooltip: {
                pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
            },
            credits: {
                enabled: false
            },
            plotOptions: {
                pie: {
                    dataLabels: {
                        enabled: true,
                        distance: -50,
                        style: {
                            fontWeight: 'bold',
                            color: 'white'
                        }
                    },
                    startAngle: -90,
                    endAngle: 90,
                    center: ['50%', '75%']
                }
            },
            series: [{
                type: 'pie',
                name: 'Alerts statuses',
                innerSize: '50%',
                data: statData
            }]
            });
       }
   }     
   var compareArrays = function(arr1, arr2){
       if(!(arr1 instanceof Array) || !(arr2 instanceof Array))
            if(arr1 === arr2)
                return true;
            else
                return false;
       if(arr1.length != arr2.length)
            return false;
       var result = 0; 
       arr1.forEach(function(item, i){
          var flag = false;
          if(!flag && !(item instanceof Array) && !(arr2[i] instanceof Array) && item == arr2[i]){
                result++;
                flag = true;
          }
          if(!flag && (item instanceof Array) && (arr2[i] instanceof Array) && compareArrays(item, arr2[i])){
                result++;
                flag = true;
            }    
       });
       return (result === arr1.length); 
   }     
   var updateAlertList = function(){
        $(".update-alert-list-loader").show();
        $.get('/alert/get-alert-list', function(data){
            if(data.result){
                $('.alert-list').html(data.html);
                if(data.statistics){
                    drawChart(data.statistics);
                }
            }
        }).always(function(){ $(".update-alert-list-loader").hide();});
   }
   updateAlertList();
   setInterval(updateAlertList, 10000);
SCRIPT;
$this->registerJs($initJS, \yii\web\View::POS_READY);
$this->registerJsFile('/js/highcharts_5_0_7.js');
?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::a('Video Queuing', '/alert/queue') ?> <i class="fa fa-spinner fa-pulse update-alert-list-loader" style="display:none;"></i></h3>

        <div class="box-tools pull-right">
            <button data-widget="collapse" class="btn btn-box-tool" type="button"><i class="fa fa-minus"></i>
            </button>
            <!--<button data-widget="remove" class="btn btn-box-tool" type="button"><i class="fa fa-times"></i></button>-->
        </div>
    </div>
    <!-- /.box-header -->
    <div class="box-body">
        <div id="alerts-chart"></div>
        <hr>
        <ul class="products-list product-list-in-box alert-list"></ul>
    </div>
    <!-- /.box-body -->
    <div class="box-footer text-center">
        <a class="uppercase" href="/alert">View All Alerts</a>
    </div>
    <!-- /.box-footer -->
</div>
<!-- /.box -->