{extends 'layouts/home.layout.tpl'}

{block name=stylesheet append}
<style>
.center {
width: auto;
display: table;
margin-left: auto;
margin-right: auto;
}
.text-center {
text-align: center;
}
</style>
<link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap-table/1.11.0/bootstrap-table.min.css" integrity="sha384-5nsQ1S/tkV3k79V0HFUsh6FgjFOy7J6Y7zOcNtQRswfMabDb4WrPwSfNBD3fkGBV" crossorigin="anonymous">
<link rel="stylesheet" href="https://cdn.bootcss.com/bootstrap-daterangepicker/2.1.24/daterangepicker.min.css" integrity="sha384-2P5djm4uaAmneB+fXVYmpYEz0Ic97wQtF4v3ilTNGTxXkhHdWi7QxboqH3djtfJw" crossorigin="anonymous">
{/block}
{block name=content}
<div class="page-header">
  <h1>{$title}</h1>
</div>

<div id="report-charts" class="report-charts"></div>
<div class="row">
    <div class="btn-group">
        <label for="start_end_time" class="col-md-2 control-label">Date:</label>

        <div class="input-group datepicker start_end_time" style="min-width:240px;">
            <input type="text" class="form-control" name="daterangepicker" id="start_end_time" >
            <i class="glyphicon glyphicon-calendar fa fa-canlender input-group-btn"></i>
            <input type="hidden" id="startDate" value="{$startDate}">
            <input type="hidden" id="endDate" value="{$endDate}">
        </div>
    </div>

    {* end buttons *}

    <div class="btn-group">
        <select id="platform" class="form-control" required="required">
            <option value="">All Platforms</option>
            {foreach from=$support_platform item=p}
                {if $platform == $p}
                    <option value="{$p}" selected>{$p}</option>
                {else}
                    <option value="{$p}">{$p}</option>
                {/if}
            {/foreach}
        </select>
    </div>
    <div class="btn-group">
        <select id="appname" class="form-control" required="required">
            <option value="">All apps (account)</option>
            {foreach from=$support_app item=app}
                {if $appname == $app}
                    <option value="{$app}" selected>{$app}</option>
                {else}
                    <option value="{$app}">{$app}</option>
                {/if}
            {/foreach}
        </select>
    </div>
    {* end selector *}

    <div class="btn">
        <button type="button" onClick="" id="query_report_btn" class="btn btn-primary">Query</button>
    </div>

</div>
{* end buttons *}

<table id="re" class="table table-hover table-striped"></table>
{/block}

{block name=javascript append}
<script src="https://cdn.bootcss.com/bootstrap-table/1.11.0/bootstrap-table.min.js" integrity="sha384-cKm/6JlIlt9zZV/g+/NqAgMrygFeVtQsI+CWNQtldXF33rCqZYtVL/QH6UTDmQAn" crossorigin="anonymous"></script>
{* <script src="https://cdn.bootcss.com/bootstrap-table/1.11.0/bootstrap-table.js"></script> *}
<script src="https://cdn.bootcss.com/bootstrap-table/1.11.0/extensions/editable/bootstrap-table-editable.min.js" integrity="sha384-zCpDSxMf/vRf8R9Nf9F1TQIe2QmCBF6str3aBZYHiSp241wPJUrKfBN1Q+/sZM2/" crossorigin="anonymous"></script>
<script src="https://cdn.bootcss.com/bootstrap-table/1.11.0/extensions/export/bootstrap-table-export.min.js" integrity="sha384-Aj++TRYnzHfnkq0GhA6V9/9tp3olt1KvmYn/J1JXxo4PIW7cwso0TCoIyznSg5Mz" crossorigin="anonymous"></script>
{* <script src="//cdn.bootcss.com/bootstrap-table/1.11.0/extensions/editable/bootstrap-table-editable.js"></script> *}
<script src="//rawgit.com/vitalets/x-editable/master/dist/bootstrap3-editable/js/bootstrap-editable.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap-table/1.11.0/extensions/export/bootstrap-table-export.min.js"></script>
<script src="//rawgit.com/hhurz/tableExport.jquery.plugin/master/tableExport.min.js"></script>

<script src="https://cdn.bootcss.com/moment.js/2.14.1/moment.min.js" integrity="sha384-ohw6o2vy/chIavW0iVsUoWaIPmAnF9VKjEMSusADB79PrE3CdeJhvR84BjcynjVl" crossorigin="anonymous"></script>
<script src="https://cdn.bootcss.com/bootstrap-daterangepicker/2.1.24/daterangepicker.min.js" integrity="sha384-OUryzIv6N1W29AxQaHZ2imPUzjV5vSi2bdvJhR10CUwz0avr57YKm/vQeflPnHdM" crossorigin="anonymous"></script>
<script src="//cdn.bootcss.com/bootstrap-table/1.11.0/locale/bootstrap-table-zh-CN.min.js"></script>
<script src="https://cdn.bootcss.com/echarts/3.2.3/echarts.js" integrity="sha384-16SD79ld60up8829ndnVc8SbKYPzeyVxkjxaKqqN5nohv10vyPrKtx2Cbw74jOgG" crossorigin="anonymous"></script>

<script type="text/javascript">

$('.report-charts').width('100%').height('500');


$('#start_end_time').daterangepicker({
    "showDropdowns": true,
    // "showWeekNumbers": true,
    // "showISOWeekNumbers": true,
    // "timePicker": true,
    // "timePicker24Hour": true,
    // "timePickerIncrement": 5,
    "autoApply": true,
    // "dateLimit": {
        // "days": 7
    // },
    "ranges": {
        "last 6 months": [
            "{'first day of - 6 months'|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{'last day of last month'|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "last 3 months": [
            "{'first day of - 3 months'|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{'last day of last month'|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "last month": [
            "{'first day of last month'|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{'last day of last month'|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "this year": [
            "{'first day of January'|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "this month": [
            "{'first day of this month'|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{'last day of this month'|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "this week": [
            "{'last monday'|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{'sunday'|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "last 365 days": [
            "{'- 365 days'|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "last 90 Days": [
            "{'- 90 days'|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "last 30 Days": [
            "{'- 30 days'|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "last 14 Days": [
            "{'- 14 days'|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "last 7 Days": [
            "{'- 7 days'|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "yesterday": [
            "{'yesterday'|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
        "Today": [
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}",
            "{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}"
        ],
    },
    "locale": {
        "format": "YYYY-MM-DD",
        "separator": "  to  ",
        "applyLabel": "Apply",
        "cancelLabel": "Cancel",
        "fromLabel": "From",
        "toLabel": "To",
        "customRangeLabel": "Custom",
        // "weekLabel": "W",
        "daysOfWeek": [
            "Sun",
            "Mon",
            "Tue",
            "Wed",
            "Thu",
            "Fri",
            "Sat"
        ],
        "monthNames": [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December"
        ],
        "firstDay": 1
    },
    // "alwaysShowCalendars": true,
    "startDate": "{$startDate}",
    "endDate": "{$endDate}",
    "opens": "right",
    "drops": "up",
    "buttonClasses": "btn btn-xs btn-sm btn-md btn-lg"
}, function(start, end, label) {
    $('#startDate').val(start.format('YYYY-MM-DD'));
    $('#endDate').val(end.format('YYYY-MM-DD'));
    console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
});

function drawReportChart(report, valueName) {

    var dates = report.dates;

    var datas = report[valueName]
    var legends = report.legends;
    var legend_list = [];
    var selectedLegend = {};
    var series = [];

    var getMarkSymbol = function() {
        var symbol = ['circle', 'rect', 'roundRect', 'triangle', 'diamond', 'pin', 'arrow'];
        return symbol[Math.floor(Math.random() * symbol.length)]
    }

    var markData = [
        {
            name: 'Average Value',
            type: 'average'
        },
        {
            name: 'Max Value',
            type: 'max'
        },
        {
            name: 'Min Value',
            type: 'min'
        },
    ];
        console.log('legends:');
        console.log(legends);

    for (var i in legends) {
        var legend = legends[i];
        legend_list.push(legend.name);

        selectedLegend[legend.name] = legend.selected;

        var _series = {
            name: legend.name,
            type: 'line',
            smooth: true,
            smoothMonotone: 'x',
            symbol: getMarkSymbol(),
            markPoint: {
                symbol: getMarkSymbol(),
                symbolSize: 20,
                data:markData,
            },
            markLine: {
                data:markData,
            },
            data: datas[legend.name]
        };
        series.push(_series);
    }

    // 基于准备好的dom，初始化echarts实例
    var myChart = echarts.init(document.getElementById('report-charts'));

    // 指定图表的配置项和数据
    var option = {
        title: {
            text: valueName.toUpperCase() + ' Reports',
        },
        color: _.shuffle(['#c23531','#2f4554', '#61a0a8', '#d48265', '#91c7ae','#749f83',  '#ca8622', '#bda29a','#6e7074', '#546570', '#c4ccd3','navy', 'green', 'olive', 'fuchsia', 'blue', 'teal', 'red', 'pink', 'black', 'gray', 'maroon', 'brown', 'purple', 'orange']),
        //['aqua', 'black', 'blue', 'fuchsia', 'gray', 'green', 'lime','maroon', 'navy', 'olive', 'purple', 'red', 'silver','teal', 'yellow']),
        // ['', '#c23531','#2f4554', '#61a0a8', '#d48265', '#91c7ae','#749f83',  '#ca8622', '#bda29a','#6e7074', '#546570', '#c4ccd3'],
        tooltip: {
            trigger:'axis'
        },
        toolbox: {
            orient: 'vertical',
            feature: {
                dataZoom: {},
                dataView: {
                    readOnly: false,
                },
                magicType: {
                    type: ['line', 'bar', 'stack', 'tiled']
                },
                restore: {},
                saveAsImage: {
                    // name: valueName+，
                    pixelRatio:2 //保存图片分辨率为页面容器的2倍
                }
            }
        },
        brush: {
            // toolbox:['rect', 'polygon', 'lineX', 'lineY', 'keep', 'clear'],
            outOfBrush: {
                colorAlpha: 0.1,
            },
        },
        legend: {
            data: legend_list,
            selectedMode: 'multiple',
            // orient: 'vertical',
            // right: '5%',
            top: '0%',
            // bottom: '10%',
            selected: selectedLegend,
        },
        xAxis: [
            {
                name: 'date',
                type: 'category',
                data: dates,
                boundaryGap: false
            },
        ],
        yAxis: {
            name: valueName,
            type: 'value',
        },
        series: series,
        dataZoom:[
            {
                type: 'inside',
                xAxisIndex:[0]
            },
            {
                type: 'slider',
                xAxisIndex:[0]
            },
        ]
    };

    // 使用刚指定的配置项和数据显示图表。
    myChart.setOption(option);
}

function ajaxUpdateValue(value, platform, date) {
    // update value via ajax
    $.ajax({
        url: '/opdata/ajax/report/update?data=revenue&csrf_token={$csrf_token}',
        type: 'POST',
        async: true,
        // contentType:'application/json',
        data: {
            'date'       : date,
            'revenue'    : value,
            'platform'   : platform,
            // 'appname'    : $('#appname').val(),
            // 'placement'  : $('#placement').val(),
            // 'country'    : $('#country').val(),
            'csrf_token' : '{$csrf_token}',
        }
    }).done(function(resp, txtStatus, xhr){
        console.log(resp);
        if (resp.status == 'ok') {
            console.log('modify google play value success.');
            bootbox.alert({
                title: 'Success',
                message: 'Modify success. The table and charts will refresh...'
            });
            $('#query_report_btn').click();
        } else if (resp.status == 'error') {
            errors = resp.errors;//.join('<br>');
            bootbox.alert({
                title:resp.message,
                message:errors
            });
        }
    });
}

function fillReportTable(report) {

    $table = $('#re');

    $.extend($.fn.bootstrapTable.defaults, $.fn.bootstrapTable.locales['zh-CN']);

    var dates = report.dates;
    // dates.reverse();

    var fields = report.platform;
    var datas = report.revenues;

    for (i in datas) {
        datas[i].reverse();
    }

    var table = [];

    console.debug(datas);

    // prepare for table data
    // 1.for each rows
    for (i = 0; i < dates.length; i++) {
        table[i] = {
            'date':dates[i]
        };
        // 2.for each columns
        for (j = 0; j < fields.length; j++) {
            var p = fields[j];
            var r = datas[p];
            table[i][p] = r[i];
        }
    }

    console.log('table data:');
    console.log(table);

    var columns = [{
        field: 'date',
        title: 'date',
        sortable: true,
        align: "center",
    }];

    // build columns...
    for (i in fields) {
        var _columns = {
            field: fields[i],
            title: fields[i],
            sortable: true,
            align: "center",
            class: 'data-'+fields[i]
        };

        if (['googleplay', 'mobvista'].indexOf(fields[i]) != -1) {
            _columns.editable = {
                type: 'text',
                title: 'Value',
                validate: function (value) {
                    var value = Number($.trim(value));
                    var $this = $(this);

                    if (typeof value != "number") {
                        return 'This field must be number.'
                    }
                    var data = $table.bootstrapTable('getData'),
                        $tr = $(this).parents('tr'),
                        index = $tr.data('index'),
                        sum_old = Number($tr.find('data-sum').html()),
                        date = data[index].date,
                        platform = $this.data('name')
                    ;
                    ajaxUpdateValue(value, platform, date);

                    console.log(data[index]);
                    return '';
                }
            }
        }
        columns.push(_columns);
    } // build columns complete


    $table.bootstrapTable({
        // 导出表格需要tableExport.jquery.plugin-1.4.0插件
        showExport: true,
        exportDataType: "all",
        exportOptions: {
            fileName: 'report'
        },
        striped: true,
        pagination: true,
        pageSize: 10,
        pageList: "[5, 10, 20, 50, ALL]",
        sidePagination:'client',
        showColumns: true,
        showToggle: true,
        showRefresh: true,
        showPaginationSwitch: true,
        search: true,
        searchOnEnterKey:true,
        idField: 'date',
        columns: columns,
        data: table
    });
    // $table.html('');
    $table.bootstrapTable('load', table);
}

function renderReport(report) {

    var dates = report.dates;
    var categories = report.categories;

    var revenues = {
        date: dates,
        sum: [],
        admob: [],
        facebook: [],
        googleplay: [],
        pingstart: [],
        baidu:[],
        growth:[],
    };
    var platform = [];
    // var platform = ['facebook','admob','pingstart','baidu','googleplay'];
    // var platform = JSON.parse('{$support_platform|@json_encode nofilter}');

    {foreach from=$support_platform item=p}
        platform.push('{$p}');
    {/foreach}
    // console.log('platforms:');
    // console.log(platform);

    report.platform = platform;

    platform.push('sum');
    platform.push('growth');

    // prepare for report data revenues
    // 1.for everyday in dates
    for (var i = 0; i < report.dates.length; i++) {

        // 2.for each platform
        for (var j = 0; j < platform.length; j++) {
            var p = platform[j];
            // console.log('platform:' + p);
            if (!revenues[p]) {
                revenues[p] = [];
            }
            if (report[p] && report[p][dates[i]]) {
                revenues[p][i] = (report[p][dates[i]].revenue).toFixed(2);
            } else {
                revenues[p][i] = 0;
            }
        }
        if (i > 0) {
            var growth = parseFloat(revenues.sum[i])
                       - parseFloat(revenues.sum[i - 1]);
            revenues['growth'][i] = growth.toFixed(2);
        } else {
            revenues['growth'][i] = 0;
        }
    } // end for everyday in dates.

    console.log('report revenues:');
    console.log(revenues);


    var legends = {};
    var $platform = $('#platform').val();

    for (var i in platform) {
        var legend = {
            name: platform[i],
            selected: true,
        };

        if ($platform == legend.name || legend.name == 'sum') {
            selected = true;
        } else {
            selected = false;
        }

        legends[i] = {
            name: platform[i],
            selected: selected,
        }
    }


    console.log('report.legends');
    console.log(legends);

    report.revenues = revenues;
    report.legends = legends;

    drawReportChart(report, 'revenues');
    fillReportTable(report);
}

function queryReport(q) {
    $.ajax({
        url: '/opdata/ajax/report/revenue',
        type: 'GET',
        async: true,
        contentType:'application/json',
        data: {
            'startDate'  : q.startDate,
            'endDate'    : q.endDate,
            'platform'   : q.platform,
            'appname'    : q.appname,
            'placement'  : q.placement,
            'country'    : q.country,
            'csrf_token' : '{$csrf_token}',
        }
    }).done(function(data, txtStatus, xhr){
        console.log(data);
        if (data.status == 'ok') {
            renderReport(data.report);
        } else if (data.status == 'error') {
            errors = data.errors;//.join('<br>');
            bootbox.alert({
                title:data.message,
                message:errors
            });
        }
    });
}


$('#query_report_btn').click(function(){
    var q = {
        startDate : $('#startDate').val(),
        endDate   : $('#endDate').val(),
        platform  : $('#platform').val(),
        appname   : $('#appname').val(),
        placement : $('#placement').val(),
        country   : $('#country').val(),
    };
    queryReport(q);
});


$('#query_report_btn').click();
$(function(){


});
</script>
{/block}
