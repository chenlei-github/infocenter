#!/usr/bin/python
# -*- coding:utf-8 -*-

import csv
import toolkit
import config
import logging
import os.path
import sys
import re
import time

record = {
    "date": '',
    "Revenue": '',
    "Request": '',
    "Fill": '',
    "Impressions": '',
    "Click": '',
    "CPC": '',
    "ECPM": '',
    "CTR": '',
    "Fill Rate": '',
}


def save_to_db(conn, date, request, filled, impression, click, filled_rate, ctr, ecpm, revenue):
    now_string = toolkit.mysql_now()
    UPDATE_SQL = "UPDATE ad_summary " \
                 "SET request = %s , " \
                 "filled = %s ," \
                 "impression = %s ," \
                 "click = %s ," \
                 "filled_rate = %s ," \
                 "ctr = %s ," \
                 "ecpm = %s ," \
                 "revenue = %s ," \
                 "updated_at = '%s' " \
                 "WHERE date = '%s' AND platform = '%s' AND app = '%s' AND country = '%s'  ;" % (
                     request, filled, impression, click,
                     filled_rate, ctr, ecpm, revenue, now_string, date, 'pingstart', '99', '99')
    # logging.info("saveToDb:INFO:UPDATE_SQL:[%s]" % (UPDATE_SQL))
    stat = toolkit.mysql_execute(conn, UPDATE_SQL)
    if stat > 0:
        logging.info("saveToDb:SUCCESS:UPDATE_SQL:[%s][%s]" % (UPDATE_SQL, stat))
        return True
    else:
        INSERT_SQL = "INSERT INTO ad_summary (platform,country,app,date,request," \
                     "filled,impression,click,filled_rate,ctr,ecpm,revenue,updated_at)" \
                     " VALUES('pingstart','99','99','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s');" \
                     % (date, request, filled, impression, click, filled_rate, ctr, ecpm, revenue, now_string)
        stat = toolkit.mysql_execute(conn, INSERT_SQL)
        if stat > 0:
            logging.info("saveToDb:SUCCESS:INSERT_SQL:[%s][%s]" % (INSERT_SQL, stat))
            return True
        else:
            logging.error("saveToDb:FAIL:INSERT_SQL:[%s][%s]" % (INSERT_SQL, stat))
            return False


def save_data(date, request, filled, impression, click, filled_rate, ctr, ecpm, revenue):
    global app
    conn = app['mysql_connection']
    if not conn:
        conn = toolkit.mysql_open_connection(config.mysql_config)
    if not conn:
        logging.error("save_data:Error:mysql_open_connection:[%s,%s,%s,%s,%s,%s,%s,%s,%s]"
                      % (date, request, filled, impression, click, filled_rate, ctr, ecpm, revenue))
        return
    stat = save_to_db(conn, date, request, filled, impression, click, filled_rate, ctr, ecpm, revenue)
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


def main():
    if len(sys.argv) < 2:
        print " Usage:\n%s <input-file.csv> " % sys.argv[0]
        sys.exit(-1)
    infile = sys.argv[1]
    if not os.path.exists(infile):
        logging.error("File not exits:%s", infile)
        sys.exit(-2)
    # 2016-10-28
    pattern = r"\d{4}-\d{2}-\d{2}"
    rg = re.compile(pattern)
    with open(infile, 'r') as f:
        c = csv.reader(f)
        for r in c:
            date, revenue, request, fill, impressions, click, cpc, ecpm, ctr, fill_rate = r
            if not date or not rg.match(date.strip()):
                continue
            # 修正数字
            revenue = float(revenue.replace(',', ''))
            request = int(request.replace(',', ''))
            fill = int(fill.replace(',', ''))
            impressions = int(impressions.replace(',', ''))
            click = int(click.replace(',', ''))
            cpc = float(cpc.replace(',', ''))
            ecpm = float(ecpm.replace(',', ''))
            ctr = float(ctr.replace(',', ''))
            fill_rate = float(fill_rate.replace(',', ''))
            save_data(date, request, fill, impressions, click, fill_rate, ctr, ecpm, revenue)


def init_logging():
    pingstart_logging = config.pingstart_logging
    debug_mode = config.debug_mode
    current_date_string = toolkit.get_current_string_for_file_name(True)[0:10]
    log_file = "%s_%s.log" % (pingstart_logging.get("detail_log_file"), current_date_string)
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
    statics_log_file = config.pingstart_logging.get("statics_log_file")
    toolkit.report_statics(statics_log_file, app)
