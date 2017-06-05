#!/usr/bin/python
# -*- coding:utf-8 -*-

import config
import toolkit
import logging
import argparse
import datetime
import re
import uuid
import time
import os
from MobvistaApi import MobvistaApi


def parse_args():
    mobvista_task = config.mobvista_task_list.keys()
    parser = argparse.ArgumentParser(description='Ads Data Crawler.')
    parser.add_argument("--from", type=str, dest="from_date", required=False,
                        help="the datetime from which  crawler start;\nformatter 2016-01-01")
    parser.add_argument("--task", type=str, dest="task", required=True, choices=mobvista_task, help="")
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
        time_delta = datetime.timedelta(days=args.days)
        datetime_from = datetime_end - time_delta
        return datetime_from, datetime_end, args.task


def gen_sql_file_name():
    return "/tmp/%s.sql" % str(uuid.uuid4()).replace("-", "")


def main_loop(task_type, sql_file_name, start_time, stop_time):
    global app
    _start_time = time.strftime('%Y%m%d', time.gmtime(start_time))
    _stop_time = time.strftime('%Y%m%d', time.gmtime(stop_time))
    task_config = config.mobvista_task_list[task_type]
    table_name = task_config["table_name"]
    field_list = task_config["field_list"]
    unique_key = task_config["unique_key"]
    account_list = config.mobvista_account_list.keys()
    for account in account_list:
        skey = config.mobvista_account_list[account]['skey']
        secret = config.mobvista_account_list[account]['secret']

        data = MobvistaApi(skey, secret).fetch_report(_start_time, _stop_time, field_list)

        if not data:
            logging.error("main:MobvistaApi.fetch_report.No_Data:"
                          "[task:%s,appid:%s,start_time:%s,stop_time:%s,field_list:%s]" % (
                              task_type, account, _start_time, _stop_time, field_list)
                          )
            continue
        sql_list = []
        for model in data:

            # insert some info
            model["account"] = account
            model['appname'] = model.get('app_id', 'unnamed')
            model['placement'] = model.get('unit_id', '')
            model["platform"] = "mobvista"

            model['revenue'] = model.get('est_revenue', 0)
            model['filled_rate'] = model.get('fill_rate', 0) / 100.0
            model['filled'] = model.get('filled', 0)
            model['impression'] = model.get('impression', 0)
            model['click'] = model.get('click', 0)
            model['country'] = model.get('country', 0)
            filled = model.get('filled', 0)
            fill_rate = model.get('filled_rate', 0.0)
            request = 0
            if toolkit.isfloat(filled) and toolkit.isfloat(fill_rate) and filled > 0 and fill_rate > 0:
                request = int(float(filled) / float(fill_rate))
            else:
                logging.info("main():main_loop:Cannot calculate Request:%s" % model)

            model['request'] = request

            # print model

            model_field_list = field_list + ["date", "appname", "platform"]
            model_unique_key = unique_key + ["date", "appname", "platform"]
            uniq_model_field_list = list(set(model_field_list))
            uniq_model_unique_key = list(set(model_unique_key))
            sql = toolkit.gen_save_sql(table_name, model=model, field_list=uniq_model_field_list,
                                       unique_key=uniq_model_unique_key)
            if sql:
                sql_list.append(sql)
            else:
                logging.error("main:gen_save_sql.Fail:"
                              "[table_name:%s, model=%s, field_list=%s, unique_key=%s]" % (
                                  task_type, model, field_list, unique_key)
                              )
        # print sql_list
        app["total_count"] += len(sql_list)
        with open(sql_file_name, 'a') as f:
            if not sql_list:
                return
            big_sql = "\n".join(sql_list)
            # print "big_sql:", big_sql
            f.write(big_sql)


def main():
    global app
    datetime_from, datetime_end, task_type = parse_args()
    task_config = config.mobvista_task_list[task_type]
    split_task = task_config["split_task"]
    size_per_split = task_config["size_per_split"]
    detail_log_file = task_config["detail_log_file"]
    clean_sql_file = task_config["clean_sql_file"]
    app["statics_log_file"] = task_config["statics_log_file"]

    init_logging(detail_log_file)

    sql_file_name = gen_sql_file_name()
    logging.info("main.sql_file_name.%s" % sql_file_name)

    if split_task:
        start_time = toolkit.datetime_to_timestamp(datetime_from)
        g_stop_time = toolkit.datetime_to_timestamp(datetime_end)
        while start_time < g_stop_time:
            stop_time = start_time + size_per_split * 24 * 3600
            if stop_time > g_stop_time:
                stop_time = g_stop_time
            main_loop(task_type, sql_file_name, start_time, stop_time)
            start_time += size_per_split * 24 * 3600
    else:
        start_time = toolkit.datetime_to_timestamp(datetime_from)
        stop_time = toolkit.datetime_to_timestamp(datetime_end)
        main_loop(task_type, sql_file_name, start_time, stop_time)

    # 执行 SQL
    mysql_config = config.mysql_config
    execute_mysql_sql(mysql_config["user"], mysql_config["passwd"], mysql_config["dbname"], sql_file_name)

    # END
    if clean_sql_file:
        os.remove(sql_file_name)
    else:
        logging.info("keep the tmp sql file @ %s" % sql_file_name)
    return


def init_logging(logfile):
    debug_mode = config.debug_mode
    current_date_string = toolkit.get_current_string_for_file_name(True)[0:10]
    log_file = "%s_%s.log" % (logfile, current_date_string)
    toolkit.init_log(log_file, debug_mode)


def execute_mysql_sql(user, passwd, dbname, sql_file):
    cmd = "mysql --user=%s --password=%s %s < %s " % (user, passwd, dbname, sql_file)
    # print "CMD:", cmd
    logging.info("execute_mysql_sql:CMD:%s" % cmd)
    # os.system(cmd)
    toolkit.run_command(cmd)


# global

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
