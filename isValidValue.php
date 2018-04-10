<?php

$field_source =  $_REQUEST['field_source'];
$text =  $_REQUEST['text'];
$source_pid =  $_REQUEST['source_pid'];
$case_sensitive =  $_REQUEST['case_sensitive'];
$src_values = array();
if (\LogicTester::isValid($field_source)) {
    $var_name_src = str_replace('[', '', $field_source);
    $var_name_src = str_replace(']', '', $var_name_src);
    $data = \REDCap::getData($source_pid, "array");
    foreach ($data as $record){
        if($record['repeat_instances']){
            foreach ($record['repeat_instances'] as $event_id=>$event){
                foreach ($event as $instrument_id=>$instrument){
//                    if($instrument_id == ''){
//                        //Repeat events
//
//                    }else{
//                        //Repeat instruments
//
//                    }
                    foreach ($instrument as $instance=>$value){
                        if($value[$var_name_src] != ""){
                            array_push($src_values,$value[$var_name_src]);
                        }
                    }
                }
            }
        }else{
            foreach ($record as $event_id=>$event){
                array_push($src_values, $event[$var_name_src]);
            }
        }
    }
}

$valid = false;
if($src_values != ""){
    foreach ($src_values as $soruce_val){
        if(!$case_sensitive && strtolower($soruce_val) == strtolower($text)){
            $valid = true;
            break;
        }else if($case_sensitive && $soruce_val == $text){
            $valid = true;
            break;
        }
    }
}

echo json_encode(array(
    'status' => 'success',
    'data' => $valid
));
?>