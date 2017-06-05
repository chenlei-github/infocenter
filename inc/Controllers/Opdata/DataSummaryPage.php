<?php
require_once 'inc/class/BaseController.php';
require_once 'inc/Models/Opdata/DimensionEnum.php';

class DataSummaryPage extends BaseController
{
    protected $template_dir = 'data_summary/';

    public function __construct()
    {
        parent::__construct();
        $this->requireUser();
    }

    private function _init($tb){
        $tb = (in_array($tb, ['store', 'push'])) ? $tb : 'store';

        $row = DimensionEnum::getAll(1, 999, ['category' => "$tb"]);
        $this->assign('option_config', $row);

        $res = require('config/opdata.php');
        $this->enum = $res[$tb.'_enum'];
        if($tb == 'store'){
            $this->enum_arr1 = array_keys($this->enum['dimension_enum1']);
        }
        $this->enum_arr2 = array_keys($this->enum['dimension_enum2']);
        $this->assign('enum', $this->enum);
        $this->assign('tb', $tb);
        $this->tb = $tb;
    }

    public function index(){

        $tb = isset($_REQUEST['tb']) ? $_REQUEST['tb'] : '';
        $this->_init($tb);

        $req_arr = [
            'dimension_enum1', 'dimension_enum2', 'start', 'end', 'show_type', 'start_hour', 'end_hour'
        ];

        $arr = array_merge(
            $req_arr,
            $this->enum_arr2,
            isset($this->enum_arr1) ? $this->enum_arr1 : []
        );
        foreach ($arr as $val) {
            $$val = isset($_REQUEST[$val]) ? (string) $_REQUEST[$val] ? : '' : '';
            //赋默认值
            if($val == 'start' && empty($$val)){
                $$val = date('Y-m-d',time()-86400*7);
            }
            if($val == 'end' && empty($$val)){
                $$val = date('Y-m-d');
            }
            if($val == 'show_type' ){
                $$val = empty($$val) ? 0 : 1;
            }
            if(empty($$val) && ($val == 'start_hour' || $val == 'end_hour')){
                $$val = '0';
            }
            $this->assign("{$val}", $$val);
        }
        $tbArr = [ $this->tb . '_summary_daily_', $this->tb . '_summary_hourly_'];

        $tb_str = $show_type==0 ? substr($start, 0, 7) : substr($start, 0, 10);
        $tb = $tbArr[$show_type] . str_replace('-', '', $tb_str);

        //拼装sql查询条件
        $sql = "SELECT `pv`,`uv`,`clicks`,`ctr`,`time` FROM {$tb} WHERE 1=1 ";
        if($this->tb == 'store'){
            $where_dimension_enum1 = " AND `dimension_1_name` = 'global' AND `dimension_1_value` = 'global' ";
            if(in_array("{$dimension_enum1}", $this->enum_arr1)
                && !empty($$dimension_enum1)){
                $where_dimension_enum1 = " AND `dimension_1_name` = '{$dimension_enum1}' AND `dimension_1_value` = '{$$dimension_enum1}' ";
            }
        }else{
            $where_dimension_enum1 = " AND `dimension_1_name` = 'global' ";
        }

        $where_dimension_enum2 = " AND `dimension_2_name` = 'global' AND `dimension_2_value` = 'global' ";
        if(in_array("{$dimension_enum2}", $this->enum_arr2)
            && !empty($$dimension_enum2)){
            $where_dimension_enum2 = " AND `dimension_2_name` = '{$dimension_enum2}' AND `dimension_2_value` = '{$$dimension_enum2}' ";
        }
        $sql .= $where_dimension_enum1 . $where_dimension_enum2;

        // if(empty($start_hour) && $show_type == 1) $start_hour = 0;
        // if(empty($end_hour) && $show_type == 1) $end_hour = 23;

        if(!empty($start)){
            if($show_type == 1 ){
                $end = "$start $end_hour";
                $start .= " $start_hour";
            }
            $sql .= " AND `time` >= '{$start}'";
        }

        if(!empty($end)){
            $sql .= " AND `time` <= '{$end}'";
        }

        $model = DimensionEnum::getDB();
        // echo $sql;
        $res = $model->query($sql);
        $data = $sort_arr = [];
        if($res){
            while($row = mysqli_fetch_array($res, MYSQL_ASSOC)){
                $sort_arr[] = $row['time'] = ($show_type==0 ? substr($row['time'], 0, 10) : substr($row['time'], 0, 13));
                $data[] = $row;
            }
            array_multisort($sort_arr, SORT_ASC, $data);
        }
        debug('exec sql ------------>' . $sql);
        $enum_arr = $this->enum;
        $this->assign('title', $this->tb . '折线图');
        $this->assign('data', $data);
        $this->assign('action', 'DataSummary');
        $this->assign('chart_type', 'line_chart');
        $this->assign('enum_arr', $enum_arr);

        $this->assign('enum1_val', (empty($$dimension_enum1) ? 'ALL' : $$dimension_enum1));
        $this->assign('enum2_val', (empty($$dimension_enum2) ? 'ALL' : $$dimension_enum2));

        return $this->display('data_summary.tpl');
    }


    public function lineChart(){
        $tb = isset($_REQUEST['tb']) ? $_REQUEST['tb'] : '';
        $this->_init($tb);

        $req_arr = [
            'dimension_enum2', 'start', 'end', 'show_type', 'y_axis', 'start_hour', 'end_hour'
        ];
        $arr = array_merge($req_arr, $this->enum_arr2);
        foreach ($arr as $val) {
            $$val = isset($_REQUEST[$val]) ? (string) $_REQUEST[$val] ? : '' : '';
            if($val == 'show_type' ){
                $$val = empty($$val) ? 0 : 1;
            }
            if(empty($$val) && ($val == 'start_hour' || $val == 'end_hour')){
                $$val = '0';
            }
            $this->assign("{$val}", $$val);
        }
        $tbArr = [$this->tb . '_summary_daily_', $this->tb . '_summary_hourly_'];

        $show_type = empty($show_type) ? 0 : 1 ;
        $tb_str = $show_type==0 ? substr($start, 0, 7) : substr($start, 0, 10);
        $tb = $tbArr[$show_type] . str_replace('-', '', $tb_str);

        $fieldArr = ['pv', 'uv', 'clicks', 'ctr'];

        $field_name = isset($fieldArr[$y_axis]) ? $fieldArr[$y_axis] : 'pv';

        $field = $field_name == 'ctr' ? 'SUM(pv) AS pv, SUM(clicks) AS clicks' : "SUM({$field_name}) AS {$field_name}";

        $sql = "SELECT {$field},`dimension_2_value` FROM {$tb} WHERE 1=1 ";

        if(in_array("{$dimension_enum2}", $this->enum_arr2)){
            $sql .= " AND `dimension_1_name` = 'global' AND `dimension_2_name` = '{$dimension_enum2}' ";
        }

        if(!empty($start)){
            if($show_type == 1 ){
                $end = "$start $end_hour";
                $start .= " $start_hour";
            }

            $sql .= " AND `time` >= '{$start}'";
        }

        if(!empty($end)){
            $sql .= " AND `time` <= '{$end}'";
        }

        $sql .= ' GROUP BY `dimension_2_value`';

        $model = DimensionEnum::getDB();
        $res = empty($dimension_enum2) ? '' : $model->query($sql);
        debug('exec sql ------------>' . $sql);
        $data = $sort_arr = [];
        if($res){
            while($row = mysqli_fetch_array($res, MYSQL_ASSOC)){
                $new_row = [];
                $sort_arr[] = $new_row['dimension_2_value'] = $row['dimension_2_value'];
                $new_row[$field_name] = ($field_name == 'ctr' ?  sprintf("%.4f", $row['clicks']/$row['pv']) : $row[$field_name]);
                $data[] = $new_row;
            }
            array_multisort($sort_arr, SORT_ASC, $data);
        }

        $enum_arr['dimension_enum2'] = $this->enum['dimension_enum2'];
        $this->assign('title', $this->tb . '柱状图');
        $this->assign('data', $data);
        $this->assign('field', $field_name);
        $this->assign('action', 'DataSummary/lineChart');
        $this->assign('chart_type', 'histogram');
        $this->assign('enum_arr', $enum_arr);

        return $this->display('data_summary.tpl');
    }






}