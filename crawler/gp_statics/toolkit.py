#!/usr/bin/python
# -*- coding:utf-8 -*-

import logging
import os
import time
import datetime

import subprocess
import uuid


def init_log(log_file, debug=False):
    logging.basicConfig(level=logging.DEBUG,
                        format='%(asctime)s %(name)-12s %(levelname)-8s %(message)s',
                        datefmt='%m-%d %H:%M',
                        filename=log_file,
                        filemode='a')
    if debug:
        console = logging.StreamHandler()
        console.setLevel(logging.INFO)
        formatter = logging.Formatter('%(name)-12s: %(levelname)-8s %(message)s')
        console.setFormatter(formatter)
        logging.getLogger('').addHandler(console)


#

def gen_sql_file_name():
    return "/tmp/%s.sql" % str(uuid.uuid4()).replace("-", "")


def run_command(command):
    try:
        p = subprocess.Popen(command,
                             stdout=subprocess.PIPE,
                             stderr=subprocess.PIPE,
                             stdin=subprocess.PIPE,
                             shell=True, env=os.environ)
        (output, err) = p.communicate()
        logging.info('run_command:(%s) output:\n%s\n' % (command, output))
        logging.info('run_command:(%s) error :\n%s\n' % (command, err))
        return (output, err)
    except Exception, e:
        logging.info('run_command exception: %s' % e)
        return False


def execute_mysql_sql(user, passwd, dbname, sql_file):
    cmd = "mysql --force --user=%s --password=%s %s < %s " % (user, passwd, dbname, sql_file)
    # print "CMD:", cmd
    logging.info("execute_mysql_sql:CMD:%s" % cmd)
    # os.system(cmd)
    run_command(cmd)


def mysql_gen_save_sql(table, model, field_list, update_list):
    if not table or not isinstance(table, str):
        logging.error("gen_save_sql().table[%s] should be str()" % table)
        return None
    if not model or not isinstance(model, dict):
        logging.error("gen_save_sql().data[%s] should be dict()" % model)
        return None
    if not field_list or not isinstance(field_list, (list, tuple)):
        logging.error("gen_save_sql().field_list[%s] should be list()" % field_list)
        return None
    if not update_list or not isinstance(update_list, (list, tuple)):
        logging.error("gen_save_sql().unique_key[%s] should be list()" % update_list)
        return None

    # 拼接 insert 从句
    column_list = []
    value_list = []
    for field in field_list:
        if field not in model.keys():
            logging.warning("gen_save_sql():FIELD[%s] not in [%s]." % (field, model))
            continue
        column_list.append("`%s`" % field)
        value_list.append("'%s'" % model[field])
    column_sql = ",".join(column_list)
    value_sql = ",".join(value_list)

    # 拼接 UPDATE 从句
    where_list = []
    for key in update_list:
        if key not in model.keys():
            val = '00'
        else:
            val = model[key]

        where_list.append("`%s`='%s'" % (key, val))
    where_sql = ",".join(where_list)
    sql = " INSERT INTO `%s`(%s) VALUES(%s) ON DUPLICATE KEY UPDATE %s ;" % \
          (table, column_sql, value_sql, where_sql)
    # print "gen_save_sql:sql:", sql
    return sql


# =================
# date time tools
# =================

def get_current_date_string(isUTC=False):
    tm = time.localtime()
    if isUTC:
        tm = time.gmtime()
    return time.strftime("%Y-%m-%d 00:00:00", tm)


def get_current_string_for_file_name(isUTC=False):
    tm = time.localtime()
    if isUTC:
        tm = time.gmtime()
    return time.strftime("%Y%m%d", tm)


def get_timestamp_from_date_string(date_string):
    tm = time.strptime("%Y-%m-%d", date_string)
    tm.tm_hour = 0
    tm.tm_min = 0
    tm.tm_sec = 0
    return time.mktime(tm)


def timestamp_pair_of_day(timestr):
    tm = time.strptime(timestr, '%Y%m%d')
    start = time.mktime(tm) - time.timezone
    stop = start + 86399  # 一天的最后一分钟
    return start, stop


def timestamp_pair_of_today():
    tm = time.gmtime()
    start = time.mktime((tm.tm_year, tm.tm_mon, tm.tm_mday, 0, 0, 0,
                         tm.tm_wday, tm.tm_yday, tm.tm_isdst)) - time.timezone
    stop = start + 86399  # 一天的最后一秒钟
    return start, stop


def mysql_gen_time_list_between(from_str="", to_str=""):
    time_start = None
    time_stop = None
    if from_str == "":
        time_start = datetime.datetime.now()
    else:
        time_start = datetime.datetime.strptime(from_str, "%Y%m%d")
    if to_str == "":
        time_stop = datetime.datetime.now()
    else:
        time_stop = datetime.datetime.strptime(to_str, "%Y%m%d")
    one_day = datetime.timedelta(days=1)
    time_str_list = []
    while time_start <= time_stop:
        # str_time = time_start.strftime("%Y-%m-%d")
        the_day = datetime.date(time_start.year, time_start.month, time_start.day)
        time_str_list.append(the_day)
        time_start += one_day
    return time_str_list


def mysql_gen_recent_days(days=7):
    today = datetime.datetime.now()
    before = today - datetime.timedelta(days=days)
    return mysql_gen_time_list_between(before.strftime("%Y%m%d"), today.strftime("%Y%m%d"))


def mysql_now():
    tm = time.gmtime()
    return time.strftime("%Y-%m-%d %H:%M:%S", tm)


# Report

def report_statics(log_file, app):
    success_count = app.get('success_count')
    fail_count = app.get('fail_count')
    total = success_count + fail_count
    msg = "=" * 20
    msg += "\n%s\n" % time.strftime("%Y-%m-%d\t%H:%M:%S", time.gmtime())
    msg += "实际花费时间: %0.2f秒\n" % app.get('real_time')
    msg += "处理器时间  : %0.2f秒\n" % app.get('cpu_time')
    msg += "记录总数    : %s\n" % total
    msg += "更新成功百分比  : %.2f%%\n" % (100.0 * success_count / total)
    msg += "更新成功数量    : %s\n" % success_count
    msg += "更新失败数量    : %s\n" % fail_count
    with open(log_file, 'a') as f:
        f.write(msg)
