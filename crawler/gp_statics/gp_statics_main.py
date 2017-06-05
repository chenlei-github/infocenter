#!/usr/bin/python
# -*- coding:utf-8 -*-

import argparse
import datetime
import os
import re
import time
import config
import toolkit
from GooglePlayStaticsApi import GooglePlayStaticsApi
import logging

import sys

reload(sys)
sys.setdefaultencoding('utf-8')


def parse_args(task_list):
    """
    :return: datetime_from , type datatime : 起始时间
              datetime_end , type datetime : 结束时间
              task_type ,  string : 任务类型
    """
    parser = argparse.ArgumentParser(description='Ads Data Crawler.')
    parser.add_argument("--from", type=str, dest="from_date", required=False,
                        help="the datetime from which  crawler start;\nformatter 2016-01-01")
    parser.add_argument("--task", type=str, dest="task", required=True, choices=task_list, help="")
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


def convert_time(date_time):
    date_time.strftime("%Y%m%d")
    return date_time.strftime("%Y%m%d")


# loop by account
def worker(task_type, sql_file_name, start_date):
    global app
    mysql_date = start_date.strftime("%Y-%m-%d")
    start_date = convert_time(start_date)

    default_task = config.google_play_default_task
    proxies = default_task["proxies"]

    task_config = config.google_play_task_list[task_type]
    mysql_table_name = task_config["table_name"]
    mysql_field_list = task_config["field_list"]
    mysql_update_list = task_config["update_list"]

    auth_map = config.google_play_statics_auth_list
    for auth_name in auth_map.keys():
        auth = auth_map[auth_name]
        account = auth["account"]
        dev_acc = auth["dev_acc"]
        xsrf = auth["xsrf"]
        cookie = auth["cookie"]
        x_client_data = auth["x_client_data"]
        g = GooglePlayStaticsApi(account, dev_acc, cookies=cookie, x_client_data=x_client_data, xsrf=xsrf,
                                 proxies=proxies)
        pkg_list = g.fetch_package_list()
        if not pkg_list:
            logging.error("worker({start_date},{stop_date}):{msg}".format(start_date=start_date,
                                                                          stop_date=start_date,
                                                                          msg="Get Package List Failed. "))
            continue
        for pkg in pkg_list:
            model_list = None
            if task_type == "inner":
                model_list = g.fetch_users(pkg, start_date, start_date, True)
            elif task_type == "outer":
                model_list = g.fetch_users(pkg, start_date, start_date, False)
            elif task_type == "country":
                model_list = g.fetch_country(pkg, start_date, start_date)
            elif task_type == "organic":
                model_list = g.fetch_country_organic(pkg, start_date, start_date)
            elif task_type in ('conversion', 'conversion_week', 'conversion_month'):
                duration_map = {
                    'conversion': 'day',
                    'conversion_week': 'week',
                    'conversion_month': 'month',
                }
                duration = duration_map[task_type]
                model_list = g.fetch_conversion(pkg, start_date, duration)
            else:
                logging.error("worker({task_type}):Bad Task Type!".format(task_type=task_type))
                continue

            if not model_list or not isinstance(model_list, (list, tuple)):
                logging.error("worker({task_type}):Failed to fetch data({pkg}, {start_date}, {stop_date})".format(
                    task_type=task_type, start_date=start_date, stop_date=start_date, pkg=pkg))
                continue
            sql_list = []
            for model in model_list:
                if task_type in ('conversion', 'conversion_week', 'conversion_month'):
                    try:
                        _date = model['date']
                        _tm = time.strptime(str(_date), '%Y%m%d')
                        _n_date = "%04d-%02d-%02d" % (_tm.tm_year, _tm.tm_mon, _tm.tm_mday)
                        model['date'] = _n_date
                    except Exception as e:
                        logging.error("worker[129](task:[%s]):Error:[%s]:RawData:[%s]" % (task_type, e, model))
                        continue
                else:
                    model["date"] = mysql_date
                sql = toolkit.mysql_gen_save_sql(mysql_table_name, model, mysql_field_list, mysql_update_list)
                if not sql:
                    logging.error("worker():Gen Sql Error:[{table_name}, {model}, {field_list}, {update_list}]".format(
                        table_name=mysql_table_name, model=model, field_list=mysql_field_list,
                        update_list=mysql_update_list
                    ))
                    continue
                app["total_count"] += 1
                sql_list.append(sql)
            big_sql = "\n".join(sql_list)
            with open(sql_file_name, "a") as f:
                f.write(big_sql)


# loop by date
def main():
    global app
    default_task = config.google_play_default_task
    statics_log_file = default_task["statics_log_file"]
    app["statics_log_file"] = statics_log_file
    detail_log_file = default_task["detail_log_file"]
    clean_sql_file = default_task["clean_sql_file"]
    init_logging(detail_log_file)
    #
    # task_list = ["inner", "outer", "country", "organic"]
    task_list = config.google_play_task_list.keys()
    start_date, stop_date, task_type = parse_args(task_list)
    print start_date, stop_date, task_type
    # exit()

    # tmp sql file
    sql_file_name = toolkit.gen_sql_file_name()
    # loop by date
    time_step = config.google_play_task_list[task_type].get('time_step', 1)
    p_date = start_date
    while p_date <= stop_date:
        worker(task_type, sql_file_name, p_date)
        p_date += datetime.timedelta(days=time_step)

    # update year,month, year_month
    table_name = config.google_play_task_list[task_type]['table_name']
    update_time_sql_pattern = "\nUPDATE `{table_name}` " \
                              "SET `year` = YEAR(`date`), `month` = Month(`date`), " \
                              " `year_month` = DATE_FORMAT(`date`,'%Y%m') " \
                              "WHERE `year` IS NULL;\n"
    update_sql = update_time_sql_pattern.format(table_name=table_name)
    with open(sql_file_name, 'a') as f:
        f.write(update_sql)

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
    "statics_log_file": "",
    'mysql_connection': None,
    'mysql_write_count': 0,
    'mysql_max_count': 100,
    'success_count': 0,
    'fail_count': 0,
    'total_count': 0,
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
