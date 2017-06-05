{extends 'layouts/home.layout.tpl'}
{block name=stylesheet append}
<link href="//cdn.bootcss.com/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
<link href="//cdn.bootcss.com/bootstrap-table/1.11.1/bootstrap-table.min.css" rel="stylesheet">
<style type="text/css">
    #dimension_box select {
        display: none;
    }
    #dimension_box  .dimension_enum{
        display: inline-block;
    }
    .radio-inline{
        cursor: pointer;margin-left: 20px;
    }
</style>
{/block}

{block name=content}

<div>
  <ul class="nav nav-tabs" role="tablist">
    <li role="presentation" {if ($tb=='store') AND ($chart_type == 'line_chart')} class="active"{/if}><a href="/opdata/DataSummary"><h4>store折线图</h4></a></li>
    <li role="presentation" {if ($tb=='store') AND ($chart_type == 'histogram')} class="active"{/if}><a href="/opdata/DataSummary/lineChart"><h4>store柱状图</h4></a></li>
    <li role="presentation" {if ($tb=='push') AND ($chart_type == 'line_chart')} class="active"{/if}><a href="/opdata/DataSummary?tb=push"><h4>push折线图</h4></a></li>
    <li role="presentation" {if ($tb=='push') AND ($chart_type == 'histogram')} class="active"{/if}><a href="/opdata/DataSummary/lineChart?tb=push"><h4>push柱状图</h4></a></li>
  </ul>
</div>

<form method="GET" action="/opdata/{$action}" id="search_form">

 <div class="row">


{if $chart_type eq 'histogram'}
     <div class="col-md-12" >
        <label class="radio-inline y_axis">
          <input {if ($y_axis eq '0') or ($y_axis eq '')}checked="checked"{/if} type="radio" name="y_axis" value="0"> PV
        </label>&nbsp;&nbsp;
        <label class="radio-inline y_axis">
          <input {if $y_axis eq '1'}checked="checked"{/if} type="radio" name="y_axis" value="1"> UV
        </label>&nbsp;&nbsp;
        <label class="radio-inline y_axis">
          <input {if $y_axis eq '2'}checked="checked"{/if} type="radio" name="y_axis" value="2"> Clicks
        </label>&nbsp;&nbsp;
        <label class="radio-inline y_axis">
          <input {if $y_axis eq '3'}checked="checked"{/if} type="radio" name="y_axis" value="3"> CTR
        </label>
     </div>
{/if}


     <div class="col-md-9" >
         <div id="main" style="width: 800px;height:500px;"></div>
     </div>

</div>

<hr/>
<div class="row" style="">

    <div class="col-md-6">
        <div class="form-group form-inline">

        <input type="hidden" name="tb" value="{$tb}">

            <select class="form-control show_type" name="show_type" id="show_type">
                <option value="0" {if ($show_type eq '0') or ($show_type eq '')}selected="selected"{/if}>天</option>
                <option value="1" {if $show_type eq '1'}selected="selected"{/if}>小时</option>
            </select>

            <div class="input-daterange input-group" id="datepicker">
                <input type="text" value="{if $start neq ''}{$start}{/if}" class="form-control" name="start" id="qBeginTime" readonly="readonly" />
                <span class="input-group-addon to">至</span>
                <input type="text" value="{if $end neq ''}{$end}{/if}" class="form-control" name="end" id="qEndTime" readonly="readonly" />
            </div>

            <select class="form-control hour_num" name="start_hour" id="start_hour" style="display: none;">
                {for $val=0 to 23}
                <option value="{$val}" {if $start_hour eq $val}selected="selected"{/if}>{$val}</option>
                {/for}
            </select>
            <span class="hour_num">~</span>
            <select class="form-control hour_num" name="end_hour" id="end_hour" style="display: none;">
                {for $val=0 to 23}
                <option value="{$val}" {if $end_hour eq $val}selected="selected"{/if}>{$val}</option>
                {/for}
            </select>

        </div>
    </div>


    <div class="col-md-6 form-inline dimension_box" id="dimension_box">

        <div class="form-group">
            <span>
            <select class="form-control dimension_enum" name="dimension_enum2" >
            <option value="">维度一：</option>
            {foreach key=key from=$enum_arr['dimension_enum2'] item=row}
                <option {if $dimension_enum2 eq $key}selected="selecetd"{/if} value="{$key}">{$row}</option>
            {/foreach}
            </select>

            <select class="form-control" name="app_type" {if ($dimension_enum2 eq 'app_type') AND ($chart_type eq 'line_chart')}style="display:inline-block;"{/if} id="app_type">
            {foreach from=$option_config item=row}
                {if $row['dimension_name'] == 'app_type'}
                <option {if $app_type eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                {/if}
            {/foreach}
            </select>
            <select class="form-control" name="country" {if ($dimension_enum2 eq 'country') AND ($chart_type eq 'line_chart')}style="display:inline-block;"{/if} id="country">
            {foreach from=$option_config item=row}
                {if $row['dimension_name'] == 'country'}
                <option {if $country eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                {/if}
            {/foreach}
            </select>
            <select class="form-control" name="lang" {if ($dimension_enum2 eq 'lang') AND ($chart_type eq 'line_chart')}style="display:inline-block;"{/if} id="lang">
            {foreach from=$option_config item=row}
                {if $row['dimension_name'] == 'lang'}
                <option {if $lang eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                {/if}
            {/foreach}
            </select>
            <select class="form-control" name="brand" {if ($dimension_enum2 eq 'brand') AND ($chart_type eq 'line_chart')}style="display:inline-block;"{/if} id="brand">
            {foreach from=$option_config item=row}
                {if $row['dimension_name'] == 'brand'}
                <option {if $brand eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                {/if}
            {/foreach}
            </select>
            <select class="form-control" name="model" {if ($dimension_enum2 eq 'model') AND ($chart_type eq 'line_chart')}style="display:inline-block;"{/if} id="model">
            {foreach from=$option_config item=row}
                {if $row['dimension_name'] == 'model'}
                <option {if $model eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                {/if}
            {/foreach}
            </select>
            <select class="form-control" name="os_version" {if ($dimension_enum2 eq 'os_version') AND ($chart_type eq 'line_chart')}style="display:inline-block;"{/if} id="os_version">
            {foreach from=$option_config item=row}
                {if $row['dimension_name'] == 'os_version'}
                <option {if $os_version eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                {/if}
            {/foreach}
            </select>
            <select class="form-control" name="open_count" {if ($dimension_enum2 eq 'open_count') AND ($chart_type eq 'line_chart')}style="display:inline-block;"{/if} id="open_count">
            {foreach from=$option_config item=row}
                {if $row['dimension_name'] == 'open_count'}
                <option {if $open_count eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                {/if}
            {/foreach}
            </select>

            <select class="form-control" name="push_count" {if ($dimension_enum2 eq 'push_count') AND ($chart_type eq 'line_chart')}style="display:inline-block;"{/if} id="push_count">
            {foreach from=$option_config item=row}
                {if $row['dimension_name'] == 'push_count'}
                <option {if $push_count eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                {/if}
            {/foreach}
            </select>

            <select class="form-control" name="push_type" {if ($dimension_enum2 eq 'push_type') AND ($chart_type eq 'line_chart')}style="display:inline-block;"{/if} id="push_type">
            {foreach from=$option_config item=row}
                {if $row['dimension_name'] == 'push_type'}
                <option {if $push_type eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                {/if}
            {/foreach}
            </select>

            <select class="form-control" name="msg_id" {if ($dimension_enum2 eq 'msg_id') AND ($chart_type eq 'line_chart')}style="display:inline-block;"{/if} id="msg_id">
            {foreach from=$option_config item=row}
                {if $row['dimension_name'] == 'msg_id'}
                <option {if $msg_id eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                {/if}
            {/foreach}
            </select>


            </span>
            {if isset($enum_arr['dimension_enum1'])}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <select class="form-control dimension_enum" name="dimension_enum1" >
                    <option value="">维度二：</option>
                {foreach key=key from=$enum_arr['dimension_enum1'] item=row}
                    <option {if $dimension_enum1 eq $key}selected="selecetd"{/if} value="{$key}">{$row}</option>
                {/foreach}
                </select>

                <select class="form-control" name="store_item_type" {if $dimension_enum1 eq 'store_item_type'}style="display:inline-block;"{/if} id="store_item_type">
                {foreach from=$option_config item=row}
                    {if $row['dimension_name'] == 'store_item_type'}
                    <option {if $store_item_type eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                    {/if}
                {/foreach}
                </select>
                <select class="form-control" name="store_version" {if $dimension_enum1 eq 'store_version'}style="display:inline-block;"{/if} id="store_version">
                {foreach from=$option_config item=row}
                    {if $row['dimension_name'] == 'store_version'}
                    <option {if $store_version eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                    {/if}
                {/foreach}
                </select>
                <select class="form-control" name="pkg_name" {if $dimension_enum1 eq 'pkg_name'}style="display:inline-block;"{/if} id="pkg_name">
                {foreach from=$option_config item=row}
                    {if $row['dimension_name'] == 'pkg_name'}
                    <option {if $pkg_name eq $row['dimension_value']}selected="selecetd"{/if} value="{$row['dimension_value']}">{$row['dimension_value']}</option>
                    {/if}
                {/foreach}
                </select>
            {/if}
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
             <button type="button" class="btn btn-primary" onclick="submit();">
                 &nbsp;查&nbsp;&nbsp;询&nbsp;
             </button>



        </div>


    </div>


</div>

</form>



<table id="table"></table>


{/block}




{block name=javascript append}
<script src="//cdn.bootcss.com/echarts/3.5.4/echarts.min.js"></script>
<script src="//cdn.bootcss.com/moment.js/2.18.1/moment.min.js"></script>
<script src="//cdn.bootcss.com/moment.js/2.18.1/locale/zh-cn.js"></script>
<script src="//cdn.bootcss.com/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap-table/1.11.1/bootstrap-table.min.js"></script>
<script src="//cdn.bootcss.com/bootstrap-table/1.11.1/locale/bootstrap-table-zh-CN.min.js"></script>

<script src="//cdn.bootcss.com/bootstrap-table/1.11.0/extensions/export/bootstrap-table-export.min.js"></script>
<script src="//rawgit.com/hhurz/tableExport.jquery.plugin/master/tableExport.min.js"></script>

<script type="text/javascript">
$(function(){
{if $chart_type eq 'histogram'}
    $('#table').bootstrapTable({
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
        height : 548,
        columns: [{
            field: 'time',
            title: 'time'
        }, {
            field: '{$dimension_enum2}',
            title: '{$dimension_enum2}'
        }, {
            field: '{$field}',
            title: '{$field}'
        }, ],
        data: [{foreach item=row from=$data} {
            time: '{if $show_type=='0'}{$start}~{$end}{else}{$start} {$start_hour}~{$end_hour}{/if}', '{$dimension_enum2}': '{$row['dimension_2_value']}', '{$field}': '{$row[$field]}'
        } , {/foreach}]
    });
{else}
    $('#table').bootstrapTable({
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
        height : 548,
        columns: [{
            field: 'time',
            title: 'Time'
        }, {if $dimension_enum1 neq ''}{
            field: '{$dimension_enum1}',
            title: '{$dimension_enum1}'
        },{/if} {if $dimension_enum2 neq ''}{
            field: '{$dimension_enum2}',
            title: '{$dimension_enum2}'
        }, {/if} {
            field: 'pv',
            title: 'PV'
        }, {
            field: 'uv',
            title: 'UV'
        }, {
            field: 'clicks',
            title: 'Clicks'
        }, {
            field: 'ctr',
            title: 'CTR'
        }],
        data: [{foreach item=row from=$data} {
            time: '{$row['time']}', {if $dimension_enum1 neq ''}{$dimension_enum1} : '{$enum1_val}',{/if} {if $dimension_enum2 neq ''}{$dimension_enum2} : '{$enum2_val}',{/if} pv: '{$row['pv']}', uv: '{$row['uv']}', clicks: '{$row['clicks']}', ctr: '{$row['ctr']}'
        } , {/foreach}]
    });
{/if}


    $(".dimension_enum").change(function(){
        var all_select = $(this).nextAll('select'),
        selected_val = $(this).val();
        all_select.each(function(){
            ($(this).attr('id') == selected_val && '{$chart_type}' == 'line_chart')
            ? $(this).show() : $(this).hide();
        })
    })


{literal}
    var format_arr = ['YYYY-MM-DD', 'YYYY-MM-DD HH'],
    selected_type = $("#show_type").val() || 0;

    $('#qBeginTime').datetimepicker({
        useCurrent: false,
        locale: 'zh-cn',
        ignoreReadonly: true,
        showClear: true,
        format : 'YYYY-MM-DD',
    });
    $('#qEndTime').datetimepicker({
        useCurrent: false,
        locale: 'zh-cn',
        ignoreReadonly : true,
        showClear : true,
        format : 'YYYY-MM-DD',
    });

    $("#qBeginTime").on("dp.change", function (e) {
        $('#qEndTime').data("DateTimePicker").minDate(e.date);
    });
    $("#qEndTime").on("dp.change", function (e) {
        $('#qBeginTime').data("DateTimePicker").maxDate(e.date);
    });

    function MakeDateInput(type){
        if(type == '1'){
            $("#qEndTime").hide();
            $(".to").hide();
            $(".hour_num").show();
        }else{
            $("#qEndTime").show();
            $(".to").show();
            $(".hour_num").hide();
        }
    }

    $("#show_type").change(function(){
        var type = $("#show_type").val();
        MakeDateInput(type);
    })
    MakeDateInput(selected_type);

    var myChart = echarts.init(document.getElementById('main'));
{/literal}

{if $chart_type == 'line_chart'}
//折线图
    option = {
        title: {
            text: ''
        },
        tooltip: {
            trigger: 'axis'
        },
        legend: {
            selected: {
                'UV' : false,
                'Clicks' : false,
                'CTR' : false,
            },
            data:['PV', 'UV', 'Clicks', 'CTR']
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        toolbox: {
            feature: {
                saveAsImage: {}
            }
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: [{foreach item=row from=$data}"{$row['time']}",{/foreach}]
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name:'PV',
                type:'line',
                stack: '总量',
                data: [{foreach item=row from=$data}"{$row['pv']}",{/foreach}]
            },

            {
                name:'UV',
                type:'line',
                stack: '总量',
                data: [{foreach item=row from=$data}"{$row['uv']}",{/foreach}]
            },

            {
                name:'Clicks',
                type:'line',
                stack: '总量',
                data: [{foreach item=row from=$data}"{$row['clicks']}",{/foreach}]
            },

            {
                name:'CTR',
                type:'line',
                stack: '总量',
                data: [{foreach item=row from=$data}"{$row['ctr']}",{/foreach}]
            },

        ]
    };
{else}
//柱状图
    option = {
        color: ['#3398DB'],
        tooltip : {
            trigger: 'axis',
            axisPointer : {            // 坐标轴指示器，坐标轴触发有效
                type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
            }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis : [
            {
                type : 'category',
                data : [{foreach item=row from=$data}"{$row['dimension_2_value']}",{/foreach}],
                axisTick: {
                    alignWithLabel: true
                }
            }
        ],
        yAxis : [
            {
                type : 'value'
            }
        ],
        series : [
            {
                name:'直接访问',
                type:'bar',
                barWidth: '60%',
                data: [{foreach item=row from=$data}"{$row[$field]}",{/foreach}]
            }
        ]
    };
{/if}

    myChart.setOption(option);
})
</script>


{/block}