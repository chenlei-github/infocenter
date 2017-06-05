#!/usr/bin/python
# -*- coding:utf-8 -*-

import config
import toolkit
import time
import urllib2
import sys
import json
import logging


def get_token(appid, secret):
    url = 'https://graph.facebook.com/oauth/access_token'
    url += '?client_id=' + appid
    url += '&client_secret=' + secret
    url += '&grant_type=client_credentials'
    try:
        f = urllib2.urlopen(url)
        token = f.read()
    except Exception as e:
        print "getTokenId:Exception:%s" % e
    return token


def get_data(appid, token, event_name, aggregate, time_range=None):
    if not time_range:
        time_start, time_stop = toolkit.timestamp_pair_of_today()
    else:
        time_start, time_stop = time_range
    time_start += config.facebook_graph_time_delt
    time_stop += config.facebook_graph_time_delt
    url = 'https://graph.facebook.com/v2.5/' + appid
    url += '/app_insights/app_event/?since=%s' % time_start
    url += '&until=%s' % time_stop
    url += '&summary=true&event_name=' + event_name
    url += '&aggregateBy=' + aggregate
    # url += '&breakdowns[0]=placement'
    url += '&breakdowns[0]=country'
    url += '&' + token
    logging.info("getData:URL:'%s'" % url)
    data = None
    try:
        f = urllib2.urlopen(url)
        data = f.read()
    except Exception as e:
        logging.exception("get_data:[%s,%s,%s,%s]Exception:%s" % (appid, token, event_name, aggregate, e))
    finally:
        return data


def parse_data(data_json, dest_time_str, appname, field_name):
    try:
        js = json.loads(data_json)
        lst = js.get('data')
        for rec in lst:
            timestr = rec.get("time", None)
            if not timestr:  # or timestr[:len('2016-10-25')] != dest_time_str:
                logging.error("WrongData:WrongTime:[%s]%s:" % (dest_time_str, json.dumps(rec)))
            timestr = timestr[:len('2016-10-25')]
            value = rec.get("value", None)
            if not value:
                logging.error("WrongData:WrongNumberValue:%s:" % json.dumps(rec))
            breakdowns = rec.get("breakdowns", None)
            placement = "unknown"
            country = "00"
            if breakdowns:
                placement = breakdowns.get("placement", None)
                country = breakdowns.get("country", None)
                if country == "REDACTED":
                    country = "00"
            save_data(timestr, appname, field_name, country, placement, value)
    except Exception as e:
        logging.exception("parse_data:Exception:%s:data:[%s]" % (e, data_json))
    finally:
        return


def save_data(date, appname, field_name, country, placement, value):
    global app
    conn = app['mysql_connection']
    if not conn:
        conn = toolkit.mysql_open_connection(config.mysql_config)
    if not conn:
        logging.error("save_data:Error:mysql_open_connection:[%s,%s,%s,%s,%s,%s]"
                      % (date, appname, field_name, country, placement, value))
        return
    stat = save_to_db(conn, date, appname, country, placement, field_name, value)
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


def save_to_db(conn, date, appname, country, placement, field_name, value):
    logging.info("=" * 40)
    logging.info((date, appname, country, placement, field_name, value))

    now_string = toolkit.mysql_now()

    UPDATE_SQL = "UPDATE ad_summary SET %s = %s ,updated_at = '%s' " \
                 "WHERE date = '%s' AND platform = '%s' AND app = '%s'" \
                 " AND country = '%s' ;" % \
                 (field_name, value, now_string, date, 'facebook', appname, country)
    # logging.info("saveToDb:INFO:UPDATE_SQL:[%s]" % (UPDATE_SQL))
    stat = toolkit.mysql_execute(conn, UPDATE_SQL)
    if stat > 0:
        logging.info("saveToDb:SUCCESS:UPDATE_SQL:[%s][%s]" % (UPDATE_SQL, stat))
        return True
    elif field_name == 'revenue':
        INSERT_SQL = "INSERT INTO ad_summary (date,app,platform,country,%s)" \
                     " VALUES('%s','%s','facebook','%s',%s);" \
                     % (field_name, date, appname, country, value)
        stat = toolkit.mysql_execute(conn, INSERT_SQL)
        if stat > 0:
            logging.info("saveToDb:SUCCESS:INSERT_SQL:[%s][%s]" % (INSERT_SQL, stat))
            return True
        else:
            logging.error("saveToDb:FAIL:INSERT_SQL:[%s][%s]" % (INSERT_SQL, stat))
            return False
    else:
        logging.info("saveToDb:FAIL:UPDATE_SQL:[%s][%s]" % (UPDATE_SQL, stat))
        return False


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

field_list = [
    {
        'field_name': 'revenue', 'event_name': 'fb_ad_network_revenue', 'aggregate': 'SUM'
    },
    {
        'field_name': 'request', 'event_name': 'fb_ad_network_request', 'aggregate': 'COUNT'
    },
    {
        'field_name': 'filled', 'event_name': 'fb_ad_network_request', 'aggregate': 'SUM'
    },
    {
        'field_name': 'impression', 'event_name': 'fb_ad_network_imp', 'aggregate': 'COUNT'
    },
    {
        'field_name': 'click', 'event_name': 'fb_ad_network_click', 'aggregate': 'COUNT'
    },
]


# 入口


def main_loop(time_pair):
    global app
    global field_list
    start, stop = time_pair
    dest_time_str = time.strftime("%Y-%m-%d", time.gmtime(start))
    appname_list = config.facebook_appid_config.keys()
    for appname in appname_list:
        appid = config.facebook_appid_config[appname]['appid']
        secret = config.facebook_appid_config[appname]['secret']
        for field in field_list:
            field_name = field['field_name']
            evt = field['event_name']
            agr = field['aggregate']
            data = get_data(appid, get_token(appid, secret), evt, agr, time_pair)
            if data:
                logging.info("main_loop:getData:success:len:%s" % (len(data)))
                parse_data(data, dest_time_str, appname, field_name)
            else:
                logging.info(
                    "main_loop:getData:fail:[%s,%s,%s,%s,%s]:len:[%s]" % (start, stop, appname, evt, agr, data))


def main_today():
    time_pair = toolkit.timestamp_pair_of_today()
    main_loop(time_pair)


def main_days(before_days=1):
    time_pair = toolkit.timestamp_pair_of_today()
    start_today, stop_today = time_pair
    for i in xrange(before_days):
        start = start_today - 24 * 3600 * i
        stop = stop_today - 24 * 3600 * i
        main_loop((start, stop))


def main():
    if len(sys.argv) > 1:
        arg_time = sys.argv[1]
        if arg_time == "today":
            main_today()
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
        main_today()


def init_logging():
    facebook_sum_logging = config.facebook_sum_logging
    debug_mode = config.debug_mode
    current_date_string = toolkit.get_current_string_for_file_name(True)[0:10]
    log_file = "%s_%s.log" % (facebook_sum_logging.get("detail_log_file"), current_date_string)
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
    statics_log_file = config.facebook_sum_logging.get("statics_log_file")
    toolkit.report_statics(statics_log_file, app)
