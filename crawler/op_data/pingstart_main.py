#!/usr/bin/python
# -*- coding:utf-8 -*-

import config
import toolkit
import datetime
import argparse
import re
import time
import PingstartAdApi
import logging
import os


def parse_args():
    """
    :return: datetime_from , type datatime : 起始时间
              datetime_end , type datetime : 结束时间
              task_type ,  string : 任务类型
    """
    pingstart_task = config.pingstart_task_list.keys()
    parser = argparse.ArgumentParser(description='Ads Data Crawler.')
    parser.add_argument("--from", type=str, dest="from_date", required=False,
                        help="the datetime from which  crawler start;\nformatter 2016-01-01")
    parser.add_argument("--task", type=str, dest="task", required=True, choices=pingstart_task, help="")
    parser.add_argument("days", type=int, nargs='?', default=0,
                        help="How many recently days or with --from <DATE> "
                             "means how many days after <date> \n")

    args = parser.parse_args()
    pattern = "\d{4}\-\d{2}\-\d{2}"

    if args.from_date:
        if not re.match(pattern, args.from_date):
            print "--from in bad format,(%s);should be [ 2016-01-01 ] " % args.from_date
            parser.print_help()
            exit(-1)
        else:
            datetime_start = datetime.datetime.strptime(args.from_date, "%Y-%m-%d")
            time_delta = datetime.timedelta(days=args.days)
            datetime_end = datetime_start + time_delta
            if datetime_start < datetime_end:
                return datetime_start, datetime_end, args.task
            else:
                return datetime_end, datetime_start, args.task
    else:
        if args.days < 0:
            print "days used as recently days,must be >= 0"
            print parser.print_help()
            exit(-1)
        datetime_end = datetime.datetime.utcnow()
        datetime_from = datetime_end - datetime.timedelta(days=args.days)
        return datetime_from, datetime_end, args.task


def main_loop(task_type, start_date, stop_date, sql_file_name):
    pingstart_appid_list = config.pingstart_appid_config
    pingstart_task_config = config.pingstart_task_list[task_type]
    table_name = pingstart_task_config["table_name"]
    field_list = pingstart_task_config["field_list"]
    unique_key = pingstart_task_config["unique_key"]
    date_or_country = pingstart_task_config["date_or_country"]
    field_list = field_list + ["date", "appname", "platform"]
    unique_key = unique_key + ["date", "appname", "platform"]
    slot_list = pingstart_task_config["slot"]
    for app_name in pingstart_appid_list.keys():
        for slot in slot_list:
            pingstart_app = pingstart_appid_list[app_name]
            Cookie = pingstart_app["Cookie"]
            ssl_cert = pingstart_app["ssl_crt"]

            pingstart = PingstartAdApi.PingstartAdApi(Cookie, ssl_cert)
            model_list = pingstart.fetch(start_date, stop_date, date_or_country, slot=slot)
            if not model_list or not isinstance(model_list, (list, tuple)):
                __msg = "main_loop():pingstart.fetch:get None:(status:%s)(message:%s)" % (
                    pingstart.status, pingstart.message)
                logging.error(__msg)
                continue
            sql_list = []
            for model in model_list:
                model["appname"] = app_name
                model["platform"] = "pingstart"
                if model["date"] == "" and start_date == stop_date:
                    model["date"] = start_date
                sql = toolkit.gen_save_sql(table_name, model, field_list, unique_key)
                if not sql:
                    logging.error("main_loop():gen_save_sql():error")
                else:
                    sql_list.append(sql)
            app["total_count"] += len(sql_list)
            with open(sql_file_name, "a") as f:
                big_sql = "\n".join(sql_list)
                f.write(big_sql)


def main():
    global app
    sql_file_name = toolkit.gen_sql_file_name()
    start_date, stop_date, task_type = parse_args()

    pingstart_task_config = config.pingstart_task_list[task_type]
    detail_log_file = pingstart_task_config["detail_log_file"]
    app["statics_log_file"] = pingstart_task_config["statics_log_file"]
    clean_sql_file = pingstart_task_config["clean_sql_file"]
    split_task = pingstart_task_config["split_task"]

    init_logging(detail_log_file)
    if split_task:
        current_date = start_date
        while current_date <= stop_date:
            data_str = current_date.strftime("%Y-%m-%d")
            main_loop(task_type, data_str, data_str, sql_file_name)
            current_date += datetime.timedelta(days=1)
    else:
        main_loop(task_type, start_date, stop_date, sql_file_name)

    # 执行 SQL
    mysql_config = config.mysql_config
    toolkit.execute_mysql_sql(mysql_config["user"], mysql_config["passwd"], mysql_config["dbname"], sql_file_name)

    # END
    if clean_sql_file:
        os.remove(sql_file_name)
    else:
        logging.info("keep the tmp sql file @ %s" % sql_file_name)
    return


# 全局共享变量

app = {
    "total_count": 0,
    "statics_log_file": None,
    'mysql_connection': None,
    'mysql_write_count': 0,
    'mysql_max_count': 100,
    'success_count': 0,
    'fail_count': 0,
    'total': 0,
    'cpu_time': 0,
    'real_time': 0,
}


def init_logging(logfile):
    debug_mode = config.debug_mode
    current_date_string = toolkit.get_current_string_for_file_name(True)[0:10]
    log_file = "%s_%s.log" % (logfile, current_date_string)
    toolkit.init_log(log_file, debug_mode)


if __name__ == "__main__":
    start_time = time.time()
    start_clock = time.clock()
    main()  # 入口
    stop_time = time.time()
    stop_clock = time.clock()
    msg = "=" * 20
    msg += "\n%s\n" % time.strftime("%Y-%m-%d\t%H:%M:%S GMT", time.gmtime())
    msg += "实际花费时间: %0.2f秒\n" % (stop_time - start_time)
    msg += "处理器时间  : %0.2f秒\n" % (stop_clock - start_clock)
    msg += "记录总数    : %s\n" % app["total_count"]
    with open(app["statics_log_file"], 'a') as f:
        f.write(msg)
