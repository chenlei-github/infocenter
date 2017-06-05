#!/usr/bin/python
# -*- coding:utf-8 -*-

import config
import toolkit
from GooglePlayStaticsApi import GooglePlayStaticsApi
import logging
import json
import os


def init_logging(logfile):
    debug_mode = config.debug_mode
    current_date_string = toolkit.get_current_string_for_file_name(True)[0:10]
    log_file = "%s_%s.log" % (logfile, current_date_string)
    toolkit.init_log(log_file, debug_mode)


def main():
    detail_log_file = 'google_text.log'
    init_logging(detail_log_file)
    default_task = config.google_play_default_task
    proxies = default_task["proxies"]
    auth_map = config.google_play_statics_auth_list

    sql_file = 'google_text.sql'
    if os.path.exists(sql_file):
        os.remove(sql_file)

    field_list = ['package', 'lang', 'title', 'short_description', 'full_description']
    for auth_name in auth_map.keys():
        auth = auth_map[auth_name]
        account = auth["account"]
        dev_acc = auth["dev_acc"]
        xsrf = auth["xsrf"]
        cookie = auth["cookie"]
        x_client_data = auth["x_client_data"]
        g = GooglePlayStaticsApi(account, dev_acc, cookies=cookie, x_client_data=x_client_data, xsrf=xsrf,
                                 proxies=proxies)

        package_list = g.fetch_package_list()
        if not package_list:
            logging.error("get package list fail:%s" % account)
            continue
        for package in package_list:
            text_list = g.fetch_app_text(package)
            if not text_list:
                logging.error("get text list fail : %s" % package)
                continue
            sql_list = []
            for text in text_list:
                text['title'] = text['title'].replace(r"'", r"''").replace('\n', '').replace('\r', '')
                text['short_description'] = text['short_description'].replace(r"'", r"''").replace('\n', '').replace(
                    '\r', '')
                text['full_description'] = text['full_description'].replace(r"'", r"''").replace('\n', '').replace('\r',
                                                                                                                   '')

                sql = toolkit.mysql_gen_save_sql('google_text', text, field_list, field_list)
                if isinstance(sql, unicode):
                    sql = sql.encode('utf-8')
                sql_list.append(sql)
            if not sql_list:
                continue
            with open(sql_file, 'a') as f:
                f.write('\n'.join(sql_list))
                f.write('\n')
                # exit()


if __name__ == "__main__":
    main()
