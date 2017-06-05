#! /usr/bin/env python2
# -*- coding:utf-8 -*-

import logging
import os
import uuid
import datetime
import subprocess
import time
import pytz


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


def gen_tmp_file_name(prefix=None):
    if not prefix:
        return "/tmp/%s.sql" % str(uuid.uuid4()).replace("-", "")
    else:
        return "%s/%s.sql" % (prefix, str(uuid.uuid4()).replace("-", ""))


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
    # where_list = []
    # for key in update_list:
    #     if key not in model.keys():
    #         val = '00'
    #     else:
    #         val = model[key]
    #
    #     where_list.append("`%s`='%s'" % (key, val))
    # where_sql = ",".join(where_list)
    sql = " INSERT INTO `%s`(%s) VALUES(%s);" % \
          (table, column_sql, value_sql)
    # print "gen_save_sql:sql:", sql
    return sql


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
    logging.info("execute_mysql_sql:CMD:%s" % cmd)
    run_command(cmd)


def get_pst_now():
    return get_time_now("US/Pacific")


def get_beijing_now():
    return get_time_now("Asia/Shanghai")


def get_time_now(tzname="UTC"):
    tz = pytz.timezone(tzname)
    dt = datetime.datetime.now(tz=tz)
    return dt.strftime("%Y%m%d")


def is_float(t):
    try:
        float(t)
        return True
    except:
        return False


def format_utc_time(t):
    try:
        t = float(t)
    except Exception as e:
        t = 1
    tm = time.gmtime(t)
    return time.strftime('%Y-%m-%d %H:%M:%S', tm)


if __name__ == '__main__':
    print  format_utc_time('1')
