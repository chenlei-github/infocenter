#!/usr/bin/python
# -*- coding:utf-8 -*-

import logging
import urllib2
import time
import MySQLdb
# import pymysql as MySQLdb
import datetime
import re
import argparse
import subprocess
import os
import uuid


def init_log(log_file, debug=False):
    # set up logging to file - see previous section for more details
    logging.basicConfig(level=logging.DEBUG,
                        format='%(asctime)s %(name)-12s %(levelname)-8s %(message)s',
                        datefmt='%m-%d %H:%M',
                        filename=log_file,
                        filemode='a')
    if debug:
        # define a Handler which writes INFO messages or higher to the sys.stderr
        console = logging.StreamHandler()
        console.setLevel(logging.INFO)
        # set a format which is simpler for console use
        formatter = logging.Formatter('%(name)-12s: %(levelname)-8s %(message)s')
        # tell the handler to use this format
        console.setFormatter(formatter)
        # add the handler to the root logger
        logging.getLogger('').addHandler(console)


# -------------------
#  Net Tools
# -------------------

def url_get(http_url, connect_timeout=None, trans_timeout=None, proxies=None, headers=None, user_agent=None):
    buff = None

    if trans_timeout:
        TIMEOUT = trans_timeout
    else:
        TIMEOUT = 30
    if not headers:
        headers = {
            'User-Agent': "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.87 Safari/537.36",
            'Pragma': 'No-Cache',
            'Expires': '0'
        }
    req = urllib2.Request(http_url, headers=headers)
    try:
        if proxies:
            print "GET_HTML_BY_PROXY:(%s)" % proxies
            my_proxies = {
                'https:': proxies,
                'http': proxies
            }
            ph = urllib2.ProxyHandler(my_proxies)
            opener = urllib2.build_opener(ph)
            f = opener.open(req, timeout=TIMEOUT)
        else:
            print "GET_HTML_No_PROXY"
            f = urllib2.urlopen(req, timeout=TIMEOUT)
        buff = f.read()
    except KeyboardInterrupt:
        print 'KeyboardInterrupt'
    except Exception as e:
        print "ERROR[Get_html_by_url]%s" % e
    finally:
        return buff


# Mysql Helper
#
#


def mysql_open_connection(mysql_config, log_tag=""):
    conn = None
    try:
        conn = MySQLdb.connect(host=mysql_config['host'],
                               user=mysql_config['user'],
                               passwd=mysql_config['passwd'],
                               db=mysql_config['dbname'])
    except MySQLdb.Error, e:
        logging.error("[%s]:mysql_open_connection:%s" % (log_tag, e))
    except:
        pass
    finally:
        return conn


def mysql_close_connection(mysql_connection, log_tag=""):
    success = False
    try:
        mysql_connection.close()
        success = True
    except MySQLdb.Error, e:
        logging.error("[%s]:mysql_close_connection:%s" % (log_tag, e))
    except:
        pass
    finally:
        return success


def mysql_execute(conn, execute_sql, log_tag=""):
    rowcount = 0
    try:
        cur = conn.cursor()
        cur.execute(execute_sql)
        rowcount = cur.rowcount
        conn.commit()
        cur.close()
    except MySQLdb.Error as e:
        logging.error("[%s]:mysql_execute:Error:%s" % (log_tag, e))
    except MySQLdb.DatabaseError as e:
        logging.error("[%s]:mysql_execute:DatabaseError:%s" % (log_tag, e))
    except:
        logging.error("[%s]:mysql_execute:Error:%s" % log_tag)
    finally:
        return rowcount


def mysql_query(conn, query_sql, log_tag=""):
    result = None
    success = False
    try:
        cursor = conn.cursor()
        cursor.execute(query_sql)
        result = cursor.fetchall()
        cursor.close()
        success = True
    except MySQLdb.Error, e:
        logging.error("[%s]:mysql_execute:Error:%s" % (log_tag, e))
    except MySQLdb.DatabaseError as e:
        logging.error("[%s]:mysql_execute:DatabaseError:%s" % (log_tag, e))
    except:
        logging.error("[%s]:mysql_execute:Error:%s" % log_tag)
    finally:
        return result, success


def gen_save_sql(table, model, field_list, unique_key):
    if not table or not isinstance(table, str):
        logging.error("save_one().table[%s] should be str()" % table)
        return None
    if not model or not isinstance(model, dict):
        logging.error("save_one().data[%s] should be dict()" % model)
        return None
    if not field_list or not isinstance(field_list, (list, tuple)):
        logging.error("save_one().field_list[%s] should be list()" % field_list)
        return None
    if not unique_key or not isinstance(unique_key, (list, tuple)):
        logging.error("save_one().unique_key[%s] should be list()" % unique_key)
        return None

    # 拼接 insert 从句
    column_list = []
    value_list = []
    for field in field_list:
        if field not in model.keys():
            logging.warning("save_one():FIELD[%s] not in [%s]." % (field, model))
            continue
        column_list.append("`%s`" % field)
        value_list.append("'%s'" % model[field])
    column_sql = ",".join(column_list)
    value_sql = ",".join(value_list)

    # 拼接 UPDATE 从句
    where_list = []
    for key in unique_key:
        if key not in model.keys():
            if key in ["country", "placement"]:
                val = '00'
            elif key in ["revenue", "request", "filled", "impression", "click"]:
                val = '0'
            else:
                continue
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


def datetime_to_timestamp(dt):
    return (dt - datetime.datetime(1970, 1, 1)).total_seconds()


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


#

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


# arg_parse

def parse_args():
    parser = argparse.ArgumentParser(description='Ads Data Crawler.')
    parser.add_argument("--from", type=str, dest="from_date", required=False,
                        help="the datetime from which  crawler start;\nformatter 2016-01-01")
    parser.add_argument("days", type=int, nargs='?', default=0,
                        help="How many recently days or with --from <DATE> "
                             "means how many days after <date> \n")

    args = parser.parse_args()
    # print "from:", args.from_date
    # print "days:", args.days

    # if args.days < 0:
    #     parser.print_help()

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
                return datetime_start, datetime_end
            else:
                return datetime_end, datetime_start
    else:
        if args.days < 0:
            print "days used as recently days,must be >= 0"
            print parser.print_help()
            exit(-1)
        datetime_end = datetime.datetime.utcnow()
        time_delta = datetime.timedelta(days=args.days)
        datetime_from = datetime_end - time_delta
        return datetime_from, datetime_end


def gen_sql_file_name():
    return "/tmp/%s.sql" % str(uuid.uuid4()).replace("-", "")


def execute_mysql_sql(user, passwd, dbname, sql_file):
    cmd = "mysql --user=%s --password=%s %s < %s " % (user, passwd, dbname, sql_file)
    # print "CMD:", cmd
    logging.info("execute_mysql_sql:CMD:%s" % cmd)
    # os.system(cmd)
    run_command(cmd)


def isfloat(value):
    try:
        float(value)
        return True
    except ValueError:
        return False


def unique_list_item(my_list):
    tmp_set = set()
    tmp_set_add = tmp_set.add
    for g in my_list:
        tmp_set_add(g)
    return list(tmp_set)


# -------- test -----------


def test_unique_list_item():
    a = [1, 1, 3, 4, 4, 5, 6]
    print "repeate list:", a
    print "uniq list:", unique_list_item(a)


if __name__ == '__main__':
    test_unique_list_item()
