#!/usr/bin/python
# -*- coding:utf-8 -*-

import requests
import time
import random
import toolkit
import StringIO
import csv
import re


class AdmobApi(object):
    def __init__(self, client_id, client_secret, refresh_token):
        self.dimensions_list = [
            "AD_CLIENT_ID",
            "AD_FORMAT_CODE",
            "AD_UNIT_CODE",
            "AD_UNIT_NAME",
            "APP_ID",
            "APP_NAME",
            "APP_PLATFORM",
            "BID_TYPE_CODE",
            "COUNTRY_CODE",
            "DATE",
            "PLATFORM_TYPE_CODE",
        ]
        self.metrics_list = [
            "AD_REQUESTS",
            "AD_REQUESTS_COVERAGE",
            "AD_REQUESTS_CTR",
            "AD_REQUESTS_RPM",
            "CLICKS",
            "COST_PER_CLICK",
            "EARNINGS",
            "INDIVIDUAL_AD_IMPRESSIONS",
            "INDIVIDUAL_AD_IMPRESSIONS_CTR",
            "INDIVIDUAL_AD_IMPRESSIONS_RPM",
            "MATCHED_AD_REQUESTS",
            "MATCHED_AD_REQUESTS_CTR",
            "MATCHED_AD_REQUESTS_RPM",
            "REACHED_AD_REQUESTS_MATCH_RATE",
            "REACHED_AD_REQUESTS_RPM",
            "REACHED_AD_REQUESTS_SHOW_RATE"
        ]
        # 查询字段到数据库字段的映射
        self.map_to_db = {
            "DATE": 'date',
            "COUNTRY_CODE": 'country',
            "APP_ID": 'appname',
            "AD_UNIT_CODE": 'placement',
            "AD_REQUESTS": 'request',  # request
            "AD_REQUESTS_COVERAGE": 'filled_rate',  # filled_rate
            "CLICKS": 'click',  # click
            "EARNINGS": 'revenue',
            "INDIVIDUAL_AD_IMPRESSIONS": 'impression',  # impression
            "INDIVIDUAL_AD_IMPRESSIONS_CTR": 'ctr',  # ctr
            "INDIVIDUAL_AD_IMPRESSIONS_RPM": 'ecpm',  # ecpm
            "MATCHED_AD_REQUESTS": 'filled',  # filled
        }
        self.adsense_config = {
            "CLIENT_ID": client_id,
            "CLIENT_SECRET": client_secret,
            "REFRESH_TOKEN": refresh_token
        }
        self.status = True  # True:success
        self.message = None

    def trans_metrics_list(self, field_list):
        my_metrics_list = []
        for k in self.map_to_db.keys():
            v = self.map_to_db.get(k)
            if k in self.metrics_list and v in field_list:
                my_metrics_list.append(k)
        return my_metrics_list

    def trans_dimensions_list(self, field_list):
        my_dimensions_list = []
        for k in self.map_to_db.keys():
            v = self.map_to_db.get(k)
            if k in self.dimensions_list and v in field_list:
                my_dimensions_list.append(k)
        return my_dimensions_list

    def fetch(self, start_date, end_date, field_list):
        if not field_list:
            return None
        my_dimensions_list = self.trans_dimensions_list(field_list)
        my_metrics_list = self.trans_metrics_list(field_list)
        print my_dimensions_list
        print my_metrics_list
        res_list = []
        data = self.get_content(start_date, end_date, my_dimensions_list, my_metrics_list)
        if not data:
            return None
        # print data
        buff = StringIO.StringIO(data)
        # 2016-10-28
        pattern = r"\d{4}-\d{2}-\d{2}"
        rg = re.compile(pattern)

        column_list = my_dimensions_list + my_metrics_list
        for r in csv.reader(buff):
            date = r[0]
            if not date or not rg.match(date.strip()):
                continue
            # 重新整理数据
            model = {}
            # print r
            for i in xrange(len(r)):
                name = column_list[i]
                db_name = self.map_to_db.get(name, "")
                if name == "":
                    continue
                val = r[i]
                if val == '':
                    val = '0'
                model[db_name] = val
            model["platform"] = "Admob"
            res_list.append(model)
        return res_list

    def get_token(self):
        data = {
            "Content-Type": "application/x-www-form-urlencoded",
            'client_id': self.adsense_config["CLIENT_ID"],
            'client_secret': self.adsense_config["CLIENT_SECRET"],
            'refresh_token': self.adsense_config['REFRESH_TOKEN'],
            'grant_type': 'refresh_token'
        }
        headers = {
            'X-Origin': 'https://developers.google.com',
            'user-agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)'
                          ' Chrome/47.0.2526.80 Safari/537.36 Core/1.47.933.400 QQBrowser/9.4.8699.400',
            'accept-language': 'en-US,en;q=0.8',
        }
        r = requests.post('https://www.googleapis.com/oauth2/v4/token', data=data, headers=headers)
        return r.json()

    def get_content(self, start_date="", end_date="", dimensions_list=[], metrics_list=[]):
        url = self.get_url(start_date, end_date, dimensions_list, metrics_list)
        access_token = self.get_token()
        # print "access_token", access_token
        headers = {
            'Authorization': 'Bearer ' + access_token['access_token'],
            'X-Origin': 'https://developers.google.com',
            'user-agent': 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko)'
                          ' Chrome/47.0.2526.80 Safari/537.36 Core/1.47.933.400 QQBrowser/9.4.8699.400',
            'accept-language': 'zh-CN,zh;q=0.8',
            'cache-control': 'no-cache',
            'pragma': 'no-cache',
        }
        # print "url", url
        # print "headers", headers
        return toolkit.url_get(url, headers=headers)

    def get_url(self, start_date="", end_date="", dimensions_list=[], metrics_list=[]):
        url = "https://content.googleapis.com/adsense/v1.4/accounts/pub-3935620297880745/reports?alt=csv"
        url += "&startDate=" + start_date
        url += "&endDate=" + end_date
        url += "&currency=USD"
        url += "&filter=PRODUCT_CODE%3D%3DGMOB"
        for d in dimensions_list:
            url += "&dimension=" + d
        for m in metrics_list:
            url += "&metric=" + m
        url += "&_=" + str(random.random()) + str(time.time())
        # print "url:", url
        return url

    def report_error(self, msg):
        self.status = False
        if not isinstance(self.message, list):
            self.message = []
        self.message.append("{%s}:%s" % (self.__class__.__name__, msg))

    def status(self):
        return self.status

    def message(self):
        return self.message


if __name__ == "__main__":
    import config
    import json

    admob_auth = config.adsense_config

    client_id = admob_auth['CLIENT_ID']
    client_secret = admob_auth['CLIENT_SECRET']
    refresh_token = admob_auth['REFRESH_TOKEN']

    admob = AdmobApi(client_id, client_secret, refresh_token)
    dimensions_list = [
        "DATE",
        "COUNTRY_CODE",
        "APP_ID",
        "AD_UNIT_CODE"
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
    x_list = admob.fetch("2016-12-28", "2017-01-03",
                         ["date", "placement", "revenue", "request", "filled", "impression", "click", ])
    # print json.dumps(x_list, indent=4, sort_keys=True, ensure_ascii=False).encode('utf-8')
    revenue = {}
    for g in x_list:
        date = g['date']
        val = g['revenue']
        print date, val
        val = revenue.get(date, 0.0) + float(val)
        revenue[date] = val
        print val
    print json.dumps(revenue, indent=4, sort_keys=True, ensure_ascii=False).encode('utf-8')
