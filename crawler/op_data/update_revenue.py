#!/usr/bin/python
# -*- coding:utf-8 -*-


import toolkit
import config
import time
import logging
import sys


def sql_get_platform_list():
    TAG = "SQL_GET_PLATFORM_LIST"
    platform_list = []

    sql = "SELECT DISTINCT platform FROM ad_summary;"
    mysql_config = config.mysql_config

    conn = toolkit.mysql_open_connection(mysql_config, TAG)
    if not conn:
        logging.error("SQL_GET_PLATFORM_LIST:Error:mysql_open_connection")
        return None
    result, success = toolkit.mysql_query(conn, sql, TAG)
    toolkit.mysql_close_connection(conn)
    # print "result", result
    if not success:
        logging.error("SQL_GET_PLATFORM_LIST:FAIL:mysql_query")
        return None
    for r in result:
        platform_list.append(r[0])
    print "platform_list", platform_list
    return platform_list


def sql_get_date_list():
    TAG = "SQL_GET_DATE_LIST"
    date_list = []

    sql = "SELECT DISTINCT DATE FROM ad_summary;"
    mysql_config = config.mysql_config

    conn = toolkit.mysql_open_connection(mysql_config, TAG)
    if not conn:
        logging.error("SQL_GET_DATE_LIST:Error:mysql_open_connection")
        return None
    result, success = toolkit.mysql_query(conn, sql, TAG)
    toolkit.mysql_close_connection(conn)
    if not success:
        logging.error("SQL_GET_DATE_LIST:Fail:mysql_query")
        return None
    for r in result:
        date_list.append(r[0])
    print "date_list", date_list
    return date_list


# return revenue , success

def sql_get_revenue(date, platform):
    TAG = "SQL_GET_DATE_LIST"

    sql = "SELECT sum(revenue) FROM ad_summary WHERE `date` = '%s' AND `platform` = '%s';" % (date, platform)
    mysql_config = config.mysql_config

    conn = toolkit.mysql_open_connection(mysql_config, TAG)
    if not conn:
        logging.error("SQL_GET_DATE_LIST:Error:mysql_open_connection")
        return None, False
    result, success = toolkit.mysql_query(conn, sql, TAG)
    logging.info("sql_get_revenue,result:%s" % result)
    toolkit.mysql_close_connection(conn)
    if not success or len(result) < 1:
        logging.error("SQL_GET_DATE_LIST:Fail:mysql_query")
        return None, False
    else:
        return result[0][0], True


def save_to_db(conn, date, platform, revenue):
    logging.info("=" * 40)
    logging.info([date, platform, revenue])

    now_string = toolkit.mysql_now()

    update_sql = "  UPDATE revenue SET \t"
    update_sql += " `revenue` = '%s' ,\t" % revenue
    update_sql += " `updated_at` = '%s'\t" % now_string
    update_sql += ' WHERE\t'
    update_sql += " `platform` = '%s' AND\t" % platform
    update_sql += " `date` = '%s' ;" % date

    stat = toolkit.mysql_execute(conn, update_sql)
    if stat > 0:
        logging.info("saveToDb:SUCCESS:UPDATE_SQL:[%s][%s]" % (update_sql, stat))
        return True
    else:
        insert_sql = "INSERT INTO revenue (`date`, `platform`, `revenue`)" \
                     " VALUES('%s','%s','%s');" \
                     % (date, platform, revenue)
        stat = toolkit.mysql_execute(conn, insert_sql)
        if stat > 0:
            logging.info("saveToDb:SUCCESS:INSERT_SQL:[%s][%s]" % (insert_sql, stat))
            return True
        else:
            logging.error("saveToDb:FAIL:INSERT_SQL:[%s][%s]" % (insert_sql, stat))
            return False


def save_revenue(date, platform, revenue):
    global app
    conn = app['mysql_connection']
    if not conn:
        conn = toolkit.mysql_open_connection(config.mysql_config)
    if not conn:
        logging.error("save_data:Error:mysql_open_connection:[%s,%s,%s]"
                      % (date, platform, revenue))
        return
    stat = save_to_db(conn, date, platform, revenue)
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


# =================
# Global

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
    platform_list = sql_get_platform_list()
    date_list = None
    if len(sys.argv) == 1:
        date_list = sql_get_date_list()
    elif len(sys.argv) == 2:
        days = int(sys.argv[1])
        date_list = toolkit.mysql_gen_recent_days(days)
    elif len(sys.argv) == 3:
        from_str = sys.argv[1]
        to_str = sys.argv[2]
        date_list = toolkit.mysql_gen_time_list_between(from_str, to_str)
    # print  date_list
    # return
    for date in date_list:
        for platform in platform_list:
            revenue, success = sql_get_revenue(date, platform)
            if success and revenue:
                logging.info("main.revenue:%s" % revenue)
                save_revenue(date, platform, revenue)


def init_logging():
    update_revenue_logging = config.update_revenue_logging
    debug_mode = config.debug_mode
    current_date_string = toolkit.get_current_string_for_file_name(True)[0:10]
    log_file = "%s_%s.log" % (update_revenue_logging.get("detail_log_file"), current_date_string)
    toolkit.init_log(log_file, debug_mode)


def update_revenue_app():
    update_revenue_app = "update_revenue_app.sql"

    mysql_config = config.mysql_config
    toolkit.execute_mysql_sql(mysql_config["user"], mysql_config["passwd"], mysql_config["dbname"], update_revenue_app)


if __name__ == "__main__":
    init_logging()
    start_time = time.time()
    start_clock = time.clock()
    # main()
    update_revenue_app()
    stop_time = time.time()
    stop_clock = time.clock()
    print "CPU  time:", (stop_clock - stop_clock)
    print "Real time:", (stop_time - start_time)
    # app['cpu_time'] = stop_clock - start_clock
    # app['real_time'] = stop_time - start_time
    # statics_log_file = config.pingstart_logging.get("statics_log_file")
    # toolkit.report_statics(statics_log_file, app)
