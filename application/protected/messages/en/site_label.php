<?php

return array(
    //footer for each page
    'main_layout__footer_year'           => '&copy; 2007-'. date('Y') .'.',
    'main_layout__footer_copyright'      => 'Weather Monitor Software. Copyright Delairco',
    'page_is_autorefreshing'             => 'This page refreshes every 5 minutes.',
    
    //COMMON BUTTONS
    'do_refresh'                         => 'Refresh',
    'do_reset'                           => 'Reset',
    'do_filter'                          => 'Filter',
    'do_export'                          => 'Export',
    'do_generate'                        => 'Generate',
    'do_delete_checked'                  => 'Delete Checked',
    'do_delete'                          => 'Delete',
    'do_edit'                            => 'Edit',
    'do_history'                         => 'History',
    'do_download'                        => 'Download',
    'do_save'                            => 'Save',
    'do_regenerate'                      => 'Regenerate',
    'do_cancel'                          => 'Cancel',
    'do_update'                          => 'Update',
    'do_add'                             => 'Add',
    
    
    'no_stations'                        => 'No stations',
    'no_aws_stations'                    => 'No AWS stations',
    'no_rg_stations'                     => 'No RG stations',
    
    // COMMON FILTERS LABELS
    // AWS GRAPH, AWS TABLE, RG TABLE, RG GRAPH, MESSAGES HISTORY...
    'filter_date_from'                   => 'Start Date:',
    'filter_date_to'                     => 'End Date:',
    'filter_time_from'                   => 'UTC Time:',
    'filter_time_to'                     => 'UTC Time:',
    'filter_local_time_from'			 => 'Local Time:',
    'filter_local_time_to'				 => 'Local Time:',
	'filter_type'                        => 'Type:',
    'filter_select_station'              => 'Select Station:',
    'filter_select_stations'             => 'Select Stations:',
    'filter_select_feature'              => 'Select Feature:',
    'filter_select_features'             => 'Select Features:',
    'filter_select_rate_volume'          => 'Group Sums:',
    
    //MESSAGES HISTORY PAGE
    'msg_history__confirm_delete'           => 'Are you sure you want to delete checked message history logs?',
    'msg_history__login_as_admin_to_delete' => 'Login as Administrator to be able to delete failed messages.',
    'msg_history__empty_result'             => 'Messages History is empty. Try changing the  filter parameters.',
    'msg_history__col_message'              => 'Message',
    'msg_history__col_tools'                => 'Tools',
    'msg_history__col_added'                => 'Local Time',
    'msg_history__col_info'                 => 'Info',
    'msg_history__col_errors'               => 'Errors detected',
    'msg_history__message_was_not_processed_yet' => 'Message has not been processed yet. Please wait, it is in queue...',
    'msg_history__station_recognized'       => 'Station recognized:',
    'msg_history__station_not_recognized'   => 'Station was not recognized.',
    'msg_history__message_fatal_errors'     => 'Message has fatal errors. Was not processed.',
    'msg_history__some_sensors_were_not_processed' => 'Data from some sensors were not processed.',
    'msg_history__message_successfull'      => 'No Errors. No warnings. Messages was processed successfully.',
    'msg_history__fatal_errors_list'        => 'FATAL ERRORS:',
    'msg_history__warnings_errors_list'     => 'WARNINGS:',
    
);
?>
