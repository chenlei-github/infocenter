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
from datetime import datetime
import decimal

column_list = [
    'Description',
    'TransactionDate',
    'TransactionTime',
    'TaxType',
    'TransactionType',
    'RefundType',
    'ProductTitle',
    'ProductId',
    'ProductType',
    'SkuId',
    'Hardware',
    'BuyerCountry',
    'BuyerState',
    'BuyerPostal Code',
    'BuyerCurrency',
    'Amount(BuyerCurrency)',
    'CurrencyConversionRate',
    'MerchantCurrency',
    'Amount(MerchantCurrency)',
]


def save_to_db(conn, mydata):
    logging.info("=" * 40)
    logging.info(mydata)

    now_string = toolkit.mysql_now()
    platform = 'googleplay'

    update_sql = "UPDATE ad_summary SET\t"
    update_sql += "`revenue` = '%s' ,\t" % mydata['revenue']
    update_sql += "`updated_at` = '%s' \t" % now_string
    update_sql += "WHERE\t"
    update_sql += "`date` = '%s' AND \t" % mydata['date']
    update_sql += "`app` = '%s' AND \t" % mydata['app']
    update_sql += "`country` = '%s' AND\t" % mydata['country']
    update_sql += "`platform` = '%s' ;" % platform

    stat = toolkit.mysql_execute(conn, update_sql)
    if stat > 0:
        logging.info("saveToDb:SUCCESS:UPDATE_SQL:[%s][%s]" % (update_sql, stat))
        return True
    else:
        logging.info("saveToDb:FAIL:UPDATE_SQL:[%s]" % update_sql)
        insert_sql = "INSERT INTO ad_summary (`platform`,`country`,`app`,`date`,`revenue`)" \
                     " VALUES('%s','%s','%s','%s','%s');" \
                     % (platform, mydata['country'], mydata['app'], mydata['date'], mydata['revenue'])
        stat = toolkit.mysql_execute(conn, insert_sql)
        if stat > 0:
            logging.info("saveToDb:SUCCESS:INSERT_SQL:[%s][%s]" % (insert_sql, stat))
            return True
        else:
            logging.error("saveToDb:FAIL:INSERT_SQL:[%s][%s]" % (insert_sql, stat))
            return False


def save_to_db_new(conn, model):
    table_name = config.googleplay_task["table_name"]
    field_list = ["date", "platform", "revenue"]
    unique_key = ["date", "platform", ]
    sql = toolkit.gen_save_sql(table_name, model, field_list, unique_key)

    status = False
    try:
        toolkit.mysql_execute(conn, sql)
        status = True
    except Exception as e:
        logging.error("save_to_db_new():FAIL:[%s][%s]" % (sql, e))
        status = False
    finally:
        return status


def save_data(mydata):
    global app
    conn = app['mysql_connection']
    if not conn:
        conn = toolkit.mysql_open_connection(config.mysql_config)
    if not conn:
        logging.error("save_data:Error:mysql_open_connection:[%s]"
                      % mydata)
        return
    stat = save_to_db_new(conn, mydata)
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


def parse_csv_row(row):
    dct = {}
    for i in xrange(len(column_list)):
        name = column_list[i]
        val = row[i]
        dct[name] = val
    return dct


def load_data(infile):
    data_list = []
    # Oct 1, 2016
    pattern = r"\w{3} \d{1,2}, \d{4}"
    rg = re.compile(pattern)
    with open(infile, 'r') as f:
        rows = csv.reader(f)
        for r in rows:
            data = parse_csv_row(r)
            date = data['TransactionDate']
            if not date or not rg.match(date.strip()):
                logging.info("load_data:bad date:%s" % date)
                continue

            # 修正日期
            # Oct 1, 2016
            date_str = ""
            try:
                date = datetime.strptime(date, "%b %d, %Y")
                date_str = date.strftime("%Y-%m-%d")
            except:
                logging.error("error strptime:%s" % date)

            data['TransactionDate'] = date_str

            # 修正数字
            revenue = data['Amount(MerchantCurrency)']
            revenue = float(revenue.replace(',', ''))
            data['Amount(MerchantCurrency)'] = revenue
            data_list.append(data)

    return data_list


def compute_data(raw_data):
    revenue_dict = {}
    for g in raw_data:
        # app = g['ProductTitle']
        transactiondate = g['TransactionDate']
        revenue = g['Amount(MerchantCurrency)']
        # key_name = "\0".join((app, transactiondate))
        val = revenue_dict.get(transactiondate, decimal.Decimal(0))
        val += decimal.Decimal(revenue)
        revenue_dict[transactiondate] = val
    return revenue_dict


def main():
    if len(sys.argv) < 2:
        print " Usage:\n%s <input-file.csv> " % sys.argv[0]
        sys.exit(-1)
    infile = sys.argv[1]
    if not os.path.exists(infile):
        logging.error("File not exits:%s", infile)
        sys.exit(-2)
    data = load_data(infile)
    # print "load_data:", data
    revenue_dict = compute_data(data)
    # print "compute_data:", revenue_dict, key_dict

    for date in revenue_dict.keys():
        # appname, date = key.split("\0")
        revenue = revenue_dict[date]
        # 港币转换为美元
        revenue *= decimal.Decimal(0.09)
        model = {
            "date": date,
            "platform": "googleplay",
            # "appname": appname,
            "revenue": revenue,
        }
        save_data(model)
        #     sql = toolkit.gen_save_sql(talbe_name, model, field_list, unique_key)
        #     sql_list.append(sql)
        # sql_file_name = toolkit.gen_sql_file_name()
        # with open(sql_file_name, "a") as f:
        #     big_sql = "\n".join(sql_list)
        #     f.write(big_sql)
        #
        # # 执行 SQL
        # mysql_config = config.mysql_config
        # # toolkit.execute_mysql_sql(mysql_config["user"], mysql_config["passwd"], mysql_config["dbname"], sql_file_name)
        #
        # # END
        # if config.googleplay_clean_sql_file:
        #     os.remove(sql_file_name)
        # else:
        #     logging.info("keep the tmp sql file @ %s" % sql_file_name)
        # return


def init_logging():
    pingstart_logging = config.googleplay_logging
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
    statics_log_file = config.googleplay_logging.get("statics_log_file")
    toolkit.report_statics(statics_log_file, app)
