<?php
    $stations       =  $render_data['stations'];
    $handlers       =  $render_data['handlers'];
    $handlersCalc   =  $render_data['handlersCalc'];
    $sensorData     =  $render_data['sensorData'];
    $handlerGroups  =  $render_data['handlerGroup'];
    $stationGroups  =  $render_data['stationGroup'];
?>

<div class="middlenarrow awspanel">  <?php
    if($sensorData){
        $countStationsTop = count($stationGroups[0]);
        foreach($stationGroups as $stationGroup) {
            $countStations  =  count($stationGroup);?>
            <table class="tablelist <?php echo 'td_size_'.($countStationsTop+$countStationsTop%2); ?>">
            <!--        HEAD-->
            <tr class="paddingHeader">
                <th class="hideBg"></th><?php
                foreach($stationGroup as $station_id){ ?>
                    <th><?php
                        echo CHtml::link($stations[$station_id]->station_id_code, array('site/awssingle', 'station_id' => $stations[$station_id]->station_id));    ?>
                    </th>   <?php
                } ?>
            </tr>
            <tr class="paddingHeader">
                <td class="hideBg"></td><?php
                foreach($stationGroup as $station_id){ ?>
                    <td style="text-align: center" class="<?php echo $stations[$station_id]->nextMessageIsLates?'late':''; ?>"><?php
                        switch(($countStationsTop+$countStationsTop%2)){
                            case 8:
                                $date_s=5;
                                $date_e=11;
                                break;
                            case 6:
                            case 4:
                            case 2:
                                $date_s=0;
                                $date_e=99;
                                break;
                            default:
                                $date_s=11;
                                $date_e=5;
                                break;
                        }

                        echo substr($stations[$station_id]->lastMessage->measuring_timestamp,$date_s,$date_e);    ?>
                    </td> <?php
                } ?>
            </tr>
            <tr class="trSpace">
                <td class="hideBg"></td><?php
                for($i=0;$i<$countStations;$i++){
                    echo "<td></td>";
                }?>
            </tr>
            <!--        ROWS-->
            <?php
            foreach($handlerGroups as $handlerGroup_id => $handlerGroup){
                $flag = 0;
                foreach($handlerGroup as $handler_id_code => $param){
                    $handler_id = $param['id'];
                    if($param['name']=='handlers' and isset($sensorData['handlers'][$handler_id]['code'])){
                        foreach($sensorData['handlers'][$handler_id]['code'] as $code_id => $code){
                            $flag=1;?>
                            <tr title="<?php echo $code_id; ?>" class="<?php echo $handlerGroup_id%2?'tableSpace':''; ?> ">
                                <td class="handler"><?php
                                    echo  $handlers[$handler_id]->display_name;?>
                                    <div class="metric">
                                        <?= '&nbsp;'.$code['metric']; ?>
                                    </div>
                                </td>   <?php
                                foreach($stationGroup as $station_id ){
                                    $val = $sensorData['handlers'][$handler_id]['code'][$code_id]['stations'][$station_id]['view'];?>
                                    <td class="tableData">  <?php
                                        if(isset($val)){ ?>
                                        <div class="<?php echo $val['within']?>">
                                            <div class="<?php echo $val['change']?>">&nbsp; <?php
                                                echo $val['value']; ?>
                                            </div>  <?php
                                            }   ?>
                                        </div>
                                    </td>   <?php
                                }   ?>
                            </tr>   <?php

                        }
                    }
                    if ($param['name']=='handlersCalc'){?>
                        <tr class="<?php echo $handlerGroup_id%2 ? 'tableSpace':''; ?>">
                            <td class="handler">    <?php
                                echo  $handlersCalc[$handler_id]->display_name;   ?>
                                <div class="metric"><?php
                                    echo  $handlersCalc[$handler_id]->metric->html_code   ?>
                                </div>
                            </td>   <?php
                            foreach($stationGroup as $station_id){
                                $flag=1;
                                $val = $sensorData['handlersCalc'][$handler_id]['stations'][$station_id]['view'];?>
                                <td class="tableData">  <?php
                                    if(isset($val)){ ?>
                                    <div class="<?php echo $val['within']?>">
                                        <div class="<?php echo $val['change']?>">&nbsp; <?php
                                            echo $val['value']; ?>
                                        </div>  <?php
                                        }   ?>
                                    </div>
                                </td>   <?php
                            }   ?>
                        </tr>   <?php
                    }
                }
                if($flag){?>
                    <tr class="trSpace"><?php
                        for($i=0;$i<$countStations+1;$i++){
                            echo "<td></td>";
                        }?>
                    </tr>   <?php
                }
            } ?>

            </table>
            <div class="spacer"></div><?php
        }
    }else {
        echo 'no data';
    }
    ?>
</div>