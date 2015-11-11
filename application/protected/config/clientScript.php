<?php

return [
    'coreScriptUrl'             => getApplicationsParam('site_url_for_console'),
    'coreScriptPosition'        => CClientScript::POS_END,
    'defaultScriptPosition'     => CClientScript::POS_END,
    'defaultScriptFilePosition' => CClientScript::POS_END,

    'corePackages' => [
        'js' => [
            'js'      => ['js/js.js'],
            'css'     => ['css/reset.css', 'css/styles.css'],
            'depends' => ['jquery', 'jquery.selectBox', 'jquery.jclock'],
        ],
    ],
    'packages' => [
        /**
         * Libs
         */
        'bbq' => [],
        'jquery' => [
            'js' => ['js/jquery-1.11.2.min.js'],
        ],
        'jquery.selectBox' => [
            'js'      => ['js/jquery.selectBox.min.js'],
            'css'     => ['css/jquery.selectBox.css'],
            'depends' => ['jquery'],
        ],

        'jquery.jclock' => [
            'js'      => ['js/jquery.jclock.js'],
            'depends' => ['jquery'],
        ],

        'jquery.datePicker' => [
            'js'      => ['js/date.js', 'js/jquery.datePicker.js', 'js/custom/TwoDateFilter.js'],
            'css'     => ['css/datePicker.css'],
            'depends' => ['jquery', 'jquery.selectBox']
        ],
        'jQueryRotateCompressed' => [
            'js'      => ['js/jQueryRotateCompressed.2.1.js'],
            'depends' => ['jquery'],
        ],
        'highstock' => [
            'js'      => ['js/highcharts-custom.js'],
            'depends' => ['jquery'],
        ],
        'jqplot' => [
            'js'      => ['js/jqplot/jquery.jqplot.js', 'js/jqplot/plugins/jqplot.dateAxisRenderer.js', 'js/jqplot/plugins/jqplot.highlighter.js', 'js/jqplot/plugins/jqplot.cursor.js'],
            'css'     => ['css/jquery.jqplot.css'],
            'depends' => ['jquery'],
        ],
        'maskInput' => [
            'js'      => ['js/jquery.maskInput.js'],
            'depends' => ['jquery'],
        ],
        'jquery.ui-lightness' => [
            'js'      => ['js/jquery-ui.min.js'],
            'css'     => ['css/jquery-ui-lightness.css'],
            'depends' => ['jquery'],
        ],
        'jquery.evol.colorPicker' => [
            'js'        => ['js/evol.colorpicker.js'],
            'css'       => ['css/evol.colorpicker.min.css'],
            'depends'   => ['jquery','jquery.ui-lightness']
        ],

        /**
         * Site Action
         */
        'site.index'	 => [],
        'site.error'	 => [],
        'site.awsgraph' => [
            'js'      => ['js/controller/site.awsgraph.js'],
            'depends' => ['highstock', 'jquery.datePicker', 'jquery.jclock', 'js']
        ],
        'site.awssingle' => [
            'js'      => ['js/controller/site.awssingle.js'],
            'depends' => ['highstock', 'jquery.selectBox', 'js']
        ],
        'site.awstable' => [
            'js'      => ['js/controller/site.awstable.js'],
            'depends' => ['js', 'jquery.datePicker']
        ],
        'site.awspanel' => [],

        'site.rgtable' => [
            'js'      => ['js/controller/site.rgtable.js'],
            'depends' => ['js', 'jquery.datePicker']
        ],
        'site.rgpanel' => [
            'js'      => [],
            'depends' => ['js']
        ],
        'site.rggraph' => [
            'js'      => ['js/controller/site.rggraph.js'],
            'depends' => ['highstock', 'js']
        ],
        'site.schedule' => [
            'js'      => ['js/jquery.form.js', 'js/schedule_work.js', 'js/controller/site.schedule.js'],
            'depends' => ['js']
        ],

        'site.export' => [],
        'site.msghistory' => [
            'js'      => ['js/controller/site.msghistory.js'],
            'depends' => ['js']
        ],
        'site.stationtypedataexport'    => [
            'js'      => ['js/jquery.form.js', 'js/schedule_type_work.js', 'js/controller/site.scheduletype.js'],
            'depends' => ['js', 'jquery.datePicker','maskInput'],
            'css'     => ['css/StationTypeDataExport.css'],
        ],
        'site.stationtypedatahistory'   => [
            'js'      => ['js/schedule_type_work.js'],
        ],
        'site.scheduletypedownload'     => [],

        'site.schedulehistory'          => [],
        'site.schedulestationhistory'   => [],
        'site.scheduledownload'         => [],
        'site.login'                    => [],
        'site.logout'                   => [],

        /**
         * Admin Action
         */

        'admin.index'                => [],
        'admin.stations'             => [],
        'admin.stationsave'        => [
            'depends' => ['jquery.evol.colorPicker']
        ],
        'admin.stationdelete'        => [],
        'admin.sensors'              => [],
        'admin.calculationsave'      => [],
        'admin.calculationdelete'    => [],
        'admin.deletesensor'         => [],
        'admin.sensor'               => [],
        'admin.connections'          => [],
        'admin.connectionslog'       => [],
        'admin.xmllog'               => [],
        'admin.startlistening'       => [],
        'admin.stoplistening'        => [],
        'admin.getstatus'            => [],
        'admin.setup'                => [],
        'admin.setupother'           => [],
        'admin.dbsetup'              => [],
        'admin.setupsensors'         => [],
        'admin.setupsensor'          => [],
        'admin.mailsetup'            => [],
        'admin.importmsg'            => [],
        'admin.importxml'            => [],
        'admin.msggeneration'        => [],
        'admin.awsfiltered'          => [],
        'admin.deleteforwardinfo'    => [],
        'admin.users'                => [],
        'admin.user'                 => [],
        'admin.userdelete'           => [],
        'admin.useraccesschange'     => [],
        'admin.forwardlist'          => [],
        'admin.stationgroups'        => [],
        'admin.heartbeatreports'     => [],
        'admin.heartbeatreport'      => [],
        'admin.coefficients'         => [],
        'admin.editsensor'           => [],
        'admin.exportadminssettings' => [],
        'admin.sendsmscommand'       => [
            'js'      => ['js/custom/jquery.ba-bbq.js'],
            'depends' => ['jquery.datePicker']
        ],
        'admin.smscommandsetup'      => [],
        'admin.generatesmscommand'   => [],
    ]
];