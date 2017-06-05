#! /usr/bin/env python2
# -*- coding:utf-8 -*-
import gzip
import sys

reload(sys)
sys.setdefaultencoding('utf-8')

import argparse
import datetime
import os
import re
import time
import config
import logging
import os.path
import sys
import json
import hashlib
import common

reload(sys)
sys.setdefaultencoding('utf-8')

from  S3Helper import S3Helper
from  LogHelper import log


def parse_args(task_list):
    """
    :return: datetime_from , type datatime : 起始时间
              datetime_end , type datetime : 结束时间
              task_type ,  string : 任务类型
    """
    parser = argparse.ArgumentParser(description='Ads Data Crawler.')
    parser.add_argument("--task", type=str, dest="task", required=True, choices=task_list, help="")
    parser.add_argument("--from", type=str, dest="from_date", required=False,
                        help="the datetime from which  crawler start;\nformatter 2016-01-01:02")
    parser.add_argument("--to", type=str, dest="to_date", required=False, default=None,
                        help="the datetime which  crawler end;\nformatter 2016-01-01:02")
    parser.add_argument("--days", type=int, dest="days", required=False, default=None,
                        help="the datetime which  crawler end;\nformatter 10")
    parser.add_argument("hours", type=int, nargs='?', default=0,
                        help="How many recently days or with --from <DATE> "
                             "means how many days after <date> \n")

    args = parser.parse_args()
    pattern = "\d{4}\-\d{2}\-\d{2}:\d{02}"

    m_list = []
    for m in [args.to_date, args.days, args.hours]:
        if m:
            m_list.append(m)
    if len(m_list) > 1:
        print "options `--to` `--days` `hours` cannot used together!"
        parser.print_help()
        exit(1)

    if args.from_date:
        if not re.match(pattern, args.from_date):
            print "--from in bad format,(%s);should be [ 2016-01-01:00 ] " % args.from_date
            parser.print_help()
            exit(3)
        else:
            datetime_start = datetime.datetime.strptime(args.from_date, "%Y-%m-%d:%H")
            datetime_end = datetime_start
            if args.to_date:
                if not re.match(pattern, args.to_date):
                    print "--to in bad format,(%s);should be [ 2016-01-01:00 ] " % args.from_date
                    parser.print_help()
                    exit(4)
                else:
                    datetime_end = datetime.datetime.strptime(args.to_date, "%Y-%m-%d:%H")
            elif args.hours:
                time_delta = datetime.timedelta(hours=args.hours)
                datetime_end = datetime_start + time_delta
            elif args.days:
                time_delta = datetime.timedelta(days=args.days)
                datetime_end = datetime_start + time_delta
            else:
                print "miss param `to`, `days`, `hours`"
                parser.print_help()
                exit(2)
            if datetime_start < datetime_end:
                return datetime_start, datetime_end, args.task
            else:
                return datetime_end, datetime_start, args.task
    else:
        datetime_end = datetime.datetime.utcnow()
        datetime_from = datetime_end
        if args.days:
            datetime_from = datetime_end - datetime.timedelta(days=args.days)
        elif args.hours:
            datetime_from = datetime_end - datetime.timedelta(hours=args.hours)
        print [datetime_from, datetime_end, args.task]
        return datetime_from, datetime_end, args.task


def convert_time(date_time):
    date_time.strftime("%Y%m%d")
    return date_time.strftime("%Y%m%d")


def download_from_s3(s3, base_path, tm, save_prefix):
    if not isinstance(tm, datetime.datetime):
        log.error("[download_from_s3]:Bad Param Type `tm`!")
        return None
    key_path = base_path + tm.strftime('/%Y/%m/%d/%H/')
    key_path = key_path.replace('//', '/')
    log.info("key_path:%s" % key_path)
    print key_path
    s3_key_list = s3.ls_dir(key_path)
    print s3_key_list
    save_file_list = []
    for s3_key in s3_key_list:
        file_name = os.path.basename(s3_key)
        save_file = '%s/%s' % (save_prefix, file_name)
        stat = s3.download(s3_key, save_file)
        if not stat:
            log.critical("[FAIL]S3 Download Key fail:[%s,%s]" % (s3_key, save_file))
            continue
        save_file_list.append(save_file)
    return save_file_list


def parse_json(json_file):
    logging.info("parse_json():%s" % json_file)
    event_list = []
    _default_value = {
        'event_type': '',
        'client_time': '1000',
        'arrival_time': '1000',
        'client_id': '',
        'uid': '',
        'country': '',
        'ip_country': '',
        'sim_country': '',
        'lang': '',
        'referrer': '',
        # 'ClientActiveTime': '',
        'package': '',
        'organic': -1,
        'hash': ''
    }
    # print _default_value.keys()
    try:
        with gzip.open(json_file, 'rb') as f:
            for line in f:
                line = line.strip()
                if not line:
                    continue
                m = _default_value.copy()
                m['hash'] = hashlib.md5(line).hexdigest()
                # print "md5:", hashlib.md5(line).hexdigest()
                # print "sha1sum:", hashlib.sha1(line).hexdigest()
                j = json.loads(line)
                m['event_type'] = j.get('event_type', '')
                m['client_time'] = j.get('event_timestamp', 1000)
                m['arrival_time'] = j.get('arrival_timestamp', 1000)
                application = j.get('application')
                if application:
                    m['package'] = application.get('package_name', '')
                client = j.get('client')
                if client:
                    m['client_id'] = client.get('client_id', '')
                device = j.get('device')
                if device:
                    locale = device.get('locale')
                    if locale:
                        m['country'] = locale.get('country', '')
                        m['lang'] = locale.get('language', '')
                attributes = j.get('attributes')
                if attributes:
                    m['uid'] = attributes.get('DeviceId', '')
                    m['referrer'] = attributes.get('Referrer', '')
                    m['ip_country'] = attributes.get('country', '')
                    m['sim_country'] = attributes.get('simCountry', '')
                    IsOrganic = attributes.get('IsOrganic', '')
                    if IsOrganic == 'true':
                        m['organic'] = 1
                    elif IsOrganic == 'false':
                        m['organic'] = 0
                    else:
                        m['organic'] = -1
                        # m['event_type'] = attributes.get('ClientActiveTime', 1000)
                for param in ['client_time', 'arrival_time']:
                    val = m[param]
                    if common.is_float(val):
                        val = float(val) / 1000
                    else:
                        val = 1
                    m[param] = common.format_utc_time(val)
                event_list.append(m)
    except Exception as e:
        log.err("parse_json:Exception:%s" % e)
    finally:
        return event_list


def get_table_name(table_name, t_time):
    tm = time.gmtime(t_time)
    return "%s_%s" % (table_name, time.strftime('%Y%m', tm))


def create_table(tmp_sql_file, table_name):
    create_sql = '''
        CREATE TABLE IF NOT EXISTS  `{table_name}` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `event_type` varchar(255) CHARACTER SET armscii8 NOT NULL,
            `uid` varchar(255) CHARACTER SET armscii8 NOT NULL,
            `client_id` varchar(255) CHARACTER SET armscii8 NOT NULL,
            `package` varchar(255) CHARACTER SET armscii8 NOT NULL,
            `client_time` timestamp NULL DEFAULT NULL COMMENT 'APP send time',
            `event_time` timestamp NULL DEFAULT NULL COMMENT 'SDK send time',
            `arrival_time` timestamp NULL DEFAULT NULL,
            `created_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `country` char(2) CHARACTER SET armscii8 NOT NULL,
            `ip_country` char(2) CHARACTER SET armscii8 NOT NULL COMMENT 'get country via IP',
            `sim_country` char(2) CHARACTER SET armscii8 NOT NULL COMMENT 'get country via SIM ',
            `lang` char(2) CHARACTER SET armscii8 NOT NULL,
            `organic` tinyint(2) NOT NULL,
            `referrer` text CHARACTER SET utf8mb4 NOT NULL,
            `hash` char(32) CHARACTER SET armscii8 NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `hash` (`hash`)
        ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
    '''.format(table_name=table_name)

    with open(tmp_sql_file, 'w') as f:
        f.write(create_sql)


def main_loop(s3, p, tmp_sql_file, table_name):
    global global_table_list
    global global_file_list
    task = config.task
    base_key_prefix = task['base_key_prefix']
    save_prefix = task['save_prefix']

    save_file_list = download_from_s3(s3, base_key_prefix, p, save_prefix)
    if not save_file_list or not isinstance(save_file_list, (list, tuple)):
        logging.error("[FAIL]main_loop():[%s]" % save_file_list)
        return None
    global_file_list += save_file_list
    logging.info("save_file_list:%s" % save_file_list)
    field_list = ['hash', 'event_type', 'arrival_time', 'client_id',
                  'uid', 'organic', 'lang', 'package', 'referrer',
                  'country', 'ip_country', 'sim_country', 'client_time']

    for save_file in save_file_list:
        event_list = parse_json(save_file)
        if not event_list or not event_list:
            logging.error("main_loop():parse_json:event_list:[%s]" % event_list)
        sql_list = []
        for m in event_list:
            # create table if not exits
            the_time = m.get('client_time')
            if common.is_float(the_time):
                the_time = float(the_time)
            else:
                the_time = m.get('arrival_time')
            if common.is_float(the_time):
                the_time = float(the_time)
            else:
                the_time = time.time()
            new_table_name = get_table_name(table_name, the_time)
            if new_table_name not in global_table_list:
                global_table_list.append(new_table_name)
                create_table(tmp_sql_file, new_table_name)

            sql = common.mysql_gen_save_sql(new_table_name, m, field_list, field_list)
            sql.encode('utf-8')
            sql_list.append(sql)
        with open(tmp_sql_file, 'a')as f:
            big_sql = '\n'.join(sql_list)
            f.write(big_sql)
            # print sql.encode('utf-8')
            # print sql_list


def main():
    global global_fail_count
    global global_success_count
    global global_file_list
    log_config = config.log_config
    task = config.task
    clean_sql_file = task['clean_sql_file']
    common.init_log(log_config['filename'])

    start_time, stop_time, _ = parse_args(['default'])

    # write create table sql
    table_name = task['table_name']
    tmp_sql_file_dir = task['tmp_sql_file_dir']
    tmp_sql_file = common.gen_tmp_file_name(tmp_sql_file_dir)

    s3 = S3Helper(**config.amazon_s3_config)
    p = start_time
    while p <= stop_time:
        main_loop(s3, p, tmp_sql_file, table_name)
        p += datetime.timedelta(hours=1)

    # 执行 SQL
    mysql_config = config.mysql_config
    common.execute_mysql_sql(mysql_config["user"], mysql_config["passwd"], mysql_config["dbname"], tmp_sql_file)

    # END
    if os.path.exists(tmp_sql_file):
        if clean_sql_file:
            try:
                os.remove(tmp_sql_file)
            except Exception as e:
                logging.error("main():Delete the tmp sql file: %s:[%s]" % (tmp_sql_file, e))
        else:
            logging.info("keep the tmp sql file @ %s" % tmp_sql_file)
    else:
        logging.warning("There is No tmp sql file:%s" % tmp_sql_file)

    if not task['keep_s3_file']:
        for _file in global_file_list:
            try:
                os.remove(_file)
            except Exception as e:
                logging.error("main():Delete local Tmp S3 File: %s:[%s]" % (_file, e))
    return


# --------------
global_success_count = 0
global_fail_count = 0
global_table_list = []
global_file_list = []

if __name__ == "__main__":
    main()
    # event_list = parse_json('test.json')
    # sql_list = []
    # field_list = ['hash', 'event_type', 'arrival_time', 'client_id',
    #               'uid', 'organic', 'lang', 'package', 'referrer',
    #               'country', 'client_time']
    # for m in event_list:
    #     sql = common.mysql_gen_save_sql('install_events', m, field_list, field_list)
    #     sql_list.append(sql)
    #     print sql.encode('utf-8')
    # print sql_list
