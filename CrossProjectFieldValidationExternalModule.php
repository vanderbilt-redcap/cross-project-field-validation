<?php
namespace Vanderbilt\CrossProjectFieldValidationExternalModule;

class CrossProjectFieldValidationExternalModule extends \ExternalModules\AbstractExternalModule{

    public function __construct(){
        parent::__construct();
    }

    function validateSettings($settings){
        $error = "";

        foreach ($settings['validation'] as $validation=>$validation_content){
            if($settings['project-source'][$validation] == ""){
                $setting_number = ($validation+1).".".($validation+1);
                $error .= "Please select a project on".$setting_number." .\n";
            }

            foreach ($settings['field-source'][$validation] as $number => $field){
                if (strpos($field, '[') === false || strpos($field, ']') === false) {
                    $setting_number = ($validation+1).".".($number+1);
                    $error .= "The format of the of the source field" . $field . " on " . $setting_number . " is incorrect. Please enter a correct format:[field_name].\n";
                }

            }

            foreach ($settings['field-destination'][$validation] as $number => $field){
                if (strpos($field, '[') === false || strpos($field, ']') === false) {
                    $setting_number = ($validation+1).".".($number+1);
                    $error .= "The format of the of the destination field" . $field . " on " . $setting_number . " is incorrect. Please enter a correct format:[field_name].\n";
                }

            }

            $pids = empty($this->getProjectSetting('pids'))?array():$this->getProjectSetting('pids');
            if($pids != ""){
                $found = false;
                foreach ($pids as $index=>$data){
                    $pid = explode(",", $data);
                    $source_pid = $pid[0];
                    if($source_pid == $settings['project-source'][$validation]){
                        $found = true;
                    }
                }
                if($found == false){
                    $error .= "Please select a source project available for this module. If you want to use the current project contact your administrator.";
                }
            }
        }
        return $error;
    }

    /**
     * Only show the link if the user is in the destination project, the one that validates the data
     * @param $project_id
     * @return null
     */
    function redcap_module_configure_button_display($project_id) {
        if($project_id != ""){
            $pids = empty($this->getProjectSetting('pids'))?array():$this->getProjectSetting('pids');
            if($pids != ""){
                foreach ($pids as $index=>$data){
                    $pid = explode(",", $data);
                    $destination_pid = $pid[1];
                    if($destination_pid == $project_id){
                        return true;
                    }
                }
            }
            return null;
        }else{
            return true;
        }
    }

    function hook_every_page_top($project_id){
        if($project_id != ""){
            $pids = empty($this->getProjectSetting('pids'))?array():$this->getProjectSetting('pids');
            if($pids != ""){
                $validation = empty($this->getProjectSetting('validation'))?array():$this->getProjectSetting('validation');
                $project_source = empty($this->getProjectSetting('project-source'))?array():$this->getProjectSetting('project-source');
                $field_source = empty($this->getProjectSetting('field-source'))?array():$this->getProjectSetting('field-source');
                $field_destination = empty($this->getProjectSetting('field-destination'))?array():$this->getProjectSetting('field-destination');
                $case_sensitive = empty($this->getProjectSetting('case-sensitive'))?array():$this->getProjectSetting('case-sensitive');
                $prevent_submission = empty($this->getProjectSetting('prevent-submission'))?array():$this->getProjectSetting('prevent-submission');

                foreach ($pids as $data){
                    $pid = explode(",", $data);
                    $source_pid = $pid[0];
                    $destination_pid = $pid[1];
                    if($destination_pid == $project_id){
                        $prevent = false;
                        foreach ($validation as $index=>$validation_content) {
                            if($project_source[$index] == $source_pid) {
                                $var_name_dest = str_replace('[', '', $field_destination[$index]);
                                $var_name_dest = str_replace(']', '', $var_name_dest);

                                echo "<script>$(function(){                                            
                                            $('[name=" . $var_name_dest . "]').parent().append('<div name=\"valid_data\" id=\"valid_data_" . $var_name_dest . "\" value=\"\" prevent=\"".$prevent_submission[$index]."\"><i id=\"icon_" . $var_name_dest . "\"></i> <span id=\"valid_" . $var_name_dest . "\"></span></div>');
                                            $('[name=" . $var_name_dest . "]').focusout(function(){
                                                if($('[name=" . $var_name_dest . "]').val() != ''){
                                                   $.ajax({
                                                        type: 'POST',
                                                        url: " . json_encode($this->getUrl('isValidValue.php')) . ",
                                                        data:'&field_source=" . $field_source[$index] . "&case_sensitive=" . $case_sensitive[$index] . "&source_pid=" . $source_pid . "&text='+$(this).val()
                                                        ,
                                                        error: function (xhr, status, error) {
                                                            alert(xhr.responseText);
                                                        },
                                                        success: function (result) {
                                                            jsonAjax = jQuery.parseJSON(result);
                                                            if(jsonAjax.data){
                                                                 $('#valid_" . $var_name_dest . "').text('VALID');
                                                                 $('#icon_" . $var_name_dest . "').attr('class','fas fa-check');
                                                                 $('#valid_" . $var_name_dest . "').attr('style','color: green;font-weight: bold;');
                                                                 $('#icon_" . $var_name_dest . "').attr('style','color: green;font-weight: bold;');
                                                                 $('[name=" . $var_name_dest . "]').attr('style','font-weight: normal; background-color: none;');
                                                                 $('#valid_data_" . $var_name_dest . "').val('0');
                                                            }else{
                                                                 $('#valid_" . $var_name_dest . "').text('NOT VALID');
                                                                 $('#icon_" . $var_name_dest . "').attr('class','fas fa-times');
                                                                 $('#valid_" . $var_name_dest . "').attr('style','color: red;font-weight: bold;');
                                                                 $('#icon_" . $var_name_dest . "').attr('style','color: red;font-weight: bold;');
                                                                 $('[name=" . $var_name_dest . "]').attr('style','font-weight: bold; background-color: rgb(255, 183, 190);');
                                                                 $('[name=" . $var_name_dest . "]').attr('style','font-weight: bold; background-color: rgb(255, 183, 190);');
                                                                 $('#valid_data_" . $var_name_dest . "').val('1');
                                                            }
                                                            
                                                        }
                                                    });
                                                 }else{
                                                    $('[name=" . $var_name_dest . "]').attr('style','font-weight: normal; background-color: none;');
                                                     $('#valid_" . $var_name_dest . "').hide();
                                                     $('#valid_" . $var_name_dest . "').val('');
                                                     $('#icon_" . $var_name_dest . "').hide();
                                                 }
                                            });});</script>";
                            }
                        }

                        if ($prevent) {
                            echo "<script>
                                    $(function(){
                                        //to get all elements dybamically created
                                        window.onload = function(){
                                            $('#formSaveTip').hide();
                                        }
                                        function checkValid(){
                                             var prevent = true;
                                             $('[name=valid_data]').each(function(index){
                                                 var name = $(this).attr('id').replace(/valid_data_/g,'');
                                                if($(this).val() != '0' && $('#'+name).val() != '' && ($(this).attr('prevent') == '1')){
                                                    alert('Please review your data. Some fields are not correct.');
                                                    prevent = false;
                                                }
                                            });
                                             return prevent;
                                        }
                                        
                                        $('button[name^=\"submit-btn-save\"]').each(function(){
                                          
                                             $('[name='+$(this).attr('name')+']')[0].onclick = function(){
                                                 var submit = checkValid();
                                                 if(submit){
                                                     dataEntrySubmit($(this).attr('name'));
                                                 }
                                                return submit;
                                            };
                                        });
                                        
                                        $('#__SUBMITBUTTONS__-div').find('li').find('a').each(function(){
                                             $('#'+$(this).attr('id')+'')[0].onclick = function(){
                                                 var submit = checkValid();
                                                 if(submit){
                                                     dataEntrySubmit($(this).attr('name'));
                                                 }
                                                return submit;
                                            };
                                        });  
                                    });
                              </script>";
                        }
                    }
                }
            }
        }
    }
}