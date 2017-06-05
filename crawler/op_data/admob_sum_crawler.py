#!/usr/bin/python
# -*- coding:utf-8 -*-

import time
import config
import requests
import toolkit
import StringIO
import csv
import re
import logging
import datetime
import sys

dimensions_list = [
    "DATE",
    "COUNTRY_CODE",
    "APP_ID",
]

metrics_list = [
    "AD_REQUESTS",  # request
    "AD_REQUESTS_COVERAGE",  # filled_rate
    "CLICKS",  # click
    "EARNINGS",
    "INDIVIDUAL_AD_IMPRESSIONS",  # impression
    "INDIVIDUAL_AD_IMPRESSIONS_CTR",  # ctr
    "INDIVIDUAL_AD_IMPRESSIONS_RPM",  # ecpm
    "MATCHED_AD_REQUESTS",  # filled
]

# 查询字段到数据库字段的映射
map_to_db = {
    "DATE": 'date',
    "COUNTRY_CODE": 'country',
    "APP_ID": 'app',
    "AD_REQUESTS": 'request',  # request
    "AD_REQUESTS_COVERAGE": 'filled_rate',  # filled_rate
    "CLICKS": 'click',  # click
    "EARNINGS": 'revenue',
    "INDIVIDUAL_AD_IMPRESSIONS": 'impression',  # impression
    "INDIVIDUAL_AD_IMPRESSIONS_CTR": 'ctr',  # ctr
    "INDIVIDUAL_AD_IMPRESSIONS_RPM": 'ecpm',  # ecpm
    "MATCHED_AD_REQUESTS": 'filled',  # filled
}

column_list = dimensions_list + metrics_list


def get_url(start_date="", end_date=""):
    url = "https://content.googleapis.com/adsense/v1.4/accounts/pub-3935620297880745/reports?alt=csv"
    url += "&startDate=" + start_date
    url += "&endDate=" + end_date
    url += "&currency=USD"
    for d in dimensions_list:
        url += "&dimension=" + d
    for m in metrics_list:
        url += "&metric=" + m
    return url


'''
client_id=8819981768.apps.googleusercontent.com&
client_secret={client_secret}&
refresh_token=1/6BMfW9j53gdGImsiyUH5kU5RsR4zwI9lUVX-tqf8JXQ&
grant_type=refresh_token
'''


def get_token():
    adsense_config = config.adsense_config
    data = {
        "Content-Type": "application/x-www-form-urlencoded",
        'client_id': adsense_config["CLIENT_ID"],
        'client_secret': adsense_config["CLIENT_SECRET"],
        'refresh_token': adsense_config['REFRESH_TOKEN'],
        'grant_type': 'refresh_token'
    }
    headers = {
        'X-Origin': 'https://developers.google.com',
        'user-agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)'
                      ' Chrome/47.0.2526.80 Safari/537.36 Core/1.47.933.400 QQBrowser/9.4.8699.400',
        'accept-language': 'zh-CN,zh;q=0.8',
    }
    r = requests.post('https://www.googleapis.com/oauth2/v4/token', data=data, headers=headers)
    return r.json()


def get_content(start_date="", end_date=""):
    url = get_url(start_date, end_date)
    access_token = get_token()
    # print "access_token", access_token
    headers = {
        'Authorization': 'Bearer ' + access_token['access_token'],
        'X-Origin': 'https://developers.google.com',
        'user-agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)'
                      ' Chrome/47.0.2526.80 Safari/537.36 Core/1.47.933.400 QQBrowser/9.4.8699.400',
        'accept-language': 'zh-CN,zh;q=0.8',
    }
    # print "url", url
    # print "headers", headers
    return toolkit.url_get(url, headers=headers)


def get_update_sql(my_data):
    now_string = toolkit.mysql_now()

    update_sql = 'UPDATE ad_summary SET \t'
    for m in metrics_list:
        update_sql += " `%s` = '%s' ,\t" % (map_to_db[m], my_data[m])
    update_sql += " `updated_at` = '%s' \t" % now_string
    update_sql += " WHERE\t"
    for d in dimensions_list:
        update_sql += " `%s` = '%s' AND \t" % (map_to_db[d], my_data[d])
    update_sql += "`platform` = 'admob' ;"
    return update_sql


def get_insert_sql(my_data):
    head_sql = ""
    val_sql = ""
    for c in column_list:
        head_sql += "`%s`,\t" % map_to_db[c]
        val_sql += "'%s',\t" % my_data[c]
    head_sql += "`platform`"
    val_sql += "'admob'"
    insert_sql = "INSERT INTO ad_summary (%s) VALUES (%s) ;" % (head_sql, val_sql)
    return insert_sql


def repack_data(data):
    # 重新整理数据
    my_data = {}
    for i in xrange(len(data)):
        name = column_list[i]
        val = data[i]
        if val == "":
            val = '0'
        my_data[name] = val
    return my_data


def save_to_db(conn, data):
    logging.info("=" * 40)
    logging.info(data)

    my_data = repack_data(data)
    update_sql = get_update_sql(my_data)
    # logging.info("saveToDb:INFO:UPDATE_SQL:[%s]" % (update_sql))
    stat = toolkit.mysql_execute(conn, update_sql)
    if stat > 0:
        logging.info("saveToDb:SUCCESS:UPDATE_SQL:[%s][%s]" % (update_sql, stat))
        return True
    else:
        logging.error("saveToDb:FAIL:UPDATE_SQL:[%s][%s]" % (update_sql, stat))
        insert_sql = get_insert_sql(my_data)
        stat = toolkit.mysql_execute(conn, insert_sql)
        if stat > 0:
            logging.info("saveToDb:SUCCESS:INSERT_SQL:[%s][%s]" % (insert_sql, stat))
            return True
        else:
            logging.error("saveToDb:FAIL:INSERT_SQL:[%s][%s]" % (insert_sql, stat))
            return False


def save_data(data):
    global app
    conn = app['mysql_connection']
    if not conn:
        conn = toolkit.mysql_open_connection(config.mysql_config)
    if not conn:
        logging.error("save_data:Error:mysql_open_connection:[%s]" % data)
        return
    stat = save_to_db(conn, data)
    if stat:
        app['success_count'] += 1
        app['mysql_write_count'] += 1
        # 没100次写数据库，需要关闭连接
        if app['mysql_write_count'] > app['mysql_max_count']:
            toolkit.mysql_close_connection(conn)
            app['mysql_connection'] = None
    else:
        # 每当写数据库失败时，关闭连接，重新刷新连接
        app['fail_count'] += 1
        toolkit.mysql_close_connection(conn)
        app['mysql_connection'] = None


# =============
# Global Vars
# 所有的全局变量放在 app 这个字典里面

app = {
    'mysql_connection': None,
    'mysql_write_count': 0,
    'mysql_max_count': 100,
    'success_count': 0,
    'fail_count': 0,
    'total': 0,
    'cpu_time': 0,
    'real_time': 0,
}


def main_loop(date_string):
    data = get_content(date_string, date_string)
    # print data
    buff = StringIO.StringIO(data)
    # 2016-10-28
    pattern = r"\d{4}-\d{2}-\d{2}"
    rg = re.compile(pattern)
    for r in csv.reader(buff):
        date = r[0]
        if not date or not rg.match(date.strip()):
            continue
        save_data(r)


def main_days(before_days=1):
    today = datetime.datetime.now()
    for i in xrange(before_days):
        delt = datetime.timedelta(days=i)
        start = today - delt
        start_str = "%04d-%02d-%02d" % (start.year, start.month, start.day)
        main_loop(start_str)


def main():
    if len(sys.argv) > 1:
        arg_time = sys.argv[1]
        if arg_time == "today":
            main_days(2)
        elif arg_time == "7days":
            main_days(7)
        elif arg_time == "30days":
            main_days(30)
        elif arg_time == "60days":
            main_days(60)
        elif arg_time == "90days":
            main_days(90)
        else:
            print "Usage:\n%s [ today | 7days | 30days | 90days ]" % sys.argv[0]
    else:
        main_days(2)


def init_logging():
    admob_sum_logging = config.admob_sum_logging
    debug_mode = config.debug_mode
    current_date_string = toolkit.get_current_string_for_file_name(True)[0:10]
    log_file = "%s_%s.log" % (admob_sum_logging.get("detail_log_file"), current_date_string)
    toolkit.init_log(log_file, debug_mode)


if __name__ == "__main__":
    init_logging()
    start_time = time.time()
    start_clock = time.clock()
    main()
    stop_time = time.time()
    stop_clock = time.clock()
    app['cpu_time'] = stop_clock - start_clock
    app['real_time'] = stop_time - start_time
    statics_log_file = config.admob_sum_logging.get("statics_log_file")
    toolkit.report_statics(statics_log_file, app)
