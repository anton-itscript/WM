<?php

return array(
    'scheduled_report_mail_subject' => 'Scheduled {report_type} report for {station_id_code}',
    'scheduled_report_mail_message' => 'Hello,<br/>
                             We have new scheduled {report_type} report (schedule period: {schedule_period}).<br/>
                             Station: <b>{station_id_code}</b>.<br/>
                             Report actuality time: <b>{actuality_time} UTC</b><br/><br/>
                             See <b>{report_file_name}</b> in attachment<br/><br/>
                             To find all generated {report_type} reports please go with link: <a href="{link}">{link}</a>',


    'scheduled_report_allstations_mail_subject' => 'Scheduled {report_type} report {report_id}',

    'scheduled_report_allstations_mail_message' => 'Hello,<br/>
                             We have new scheduled {report_type} report (schedule period: {schedule_period}).<br/>
                             Stations: <b>{stations_id_code}</b>.<br/>
                             Report actuality time: <b>{actuality_time} UTC</b><br/><br/>

                             To find all generated {report_type} reports please go with link: <a href="{link}">{link}</a>',

    'scheduled_report_allstations_messages_inside_mail_message' => 'Hello,<br/>
                             We have new scheduled {report_type} report (schedule period: {schedule_period}).<br/>
                             Stations: <b>{stations_id_code}</b>.<br/>
                             Report actuality time: <b>{actuality_time} UTC</b><br/><br/>
                             Your messages:<br/><br/>
                             {messages_content}<br/><br/>
                             To find all generated {report_type} reports please go with link: <a href="{link}">{link}</a>',

    'scheduled_report__messages_inside_mail_message' => 'Hello,<br/>
                             We have new scheduled {report_type} report (schedule period: {schedule_period}).<br/>
                             Stations: <b>{station_id_code}</b>.<br/>
                             Report actuality time: <b>{actuality_time} UTC</b><br/><br/>
                             Your message:<br/><br/>
                             {messages_content}<br/><br/>
                             To find all generated {report_type} reports please go with link: <a href="{link}">{link}</a>',
);
?>
