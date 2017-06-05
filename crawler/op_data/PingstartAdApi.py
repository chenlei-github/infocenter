#!/usr/bin/python
# -*- coding:utf-8 -*-

import requests


class PingstartAdApi(object):
    def __init__(self, cookie, ssl_cert=None):
        if isinstance(cookie, dict):
            self.cookie = cookie
        elif isinstance(cookie, (str, unicode)):
            self.cookie = {'S': cookie}
        # print self.cookie
        self.ssl_cert = ssl_cert
        self.status = True  # True:success
        self.message = None

    def fetch(self, start_date, end_date, date_or_country="date", slot=""):
        js = self.get_data(start_date, end_date, date_or_country, slot)
        # print js
        if not js:
            self.status = False
            self.report_error("fetch():get_data(%s, %s, %s, %s):Get None." % (
                start_date, end_date, date_or_country, slot))
            return None
        return self.parse_data(js)

    def get_data(self, start_date, end_date, date_or_country="date", slot=""):
        post_data = '{"start":"%s","end":"%s","slot":"%s","date_or_country":"%s"}' % (
            start_date, end_date, slot, date_or_country)
        url = "https://portal.pingstart.com/report/stats"
        json_data = None
        # print "post_data", post_data
        try:
            if self.ssl_cert:
                r = requests.post(url, data=post_data, cookies=self.cookie, verify=self.ssl_cert)
            else:
                r = requests.post(url, data=post_data, cookies=self.cookie)
            if r.status_code != 200:
                self.status = False
                self.report_error(
                    "fetch_data():FAIL:[status,%s]headers:%s:post-data:[%s]" % (r.status_code, r.headers, post_data))
                # print r.text
                # print r.request
            else:
                json_data = r.json()
        except Exception as e:
            self.report_error(":get_data():Exception:%s" % e)
        finally:
            return json_data

    def parse_data(self, js):
        res_list = []
        try:
            table_data = js['table_data']
            for m in table_data:
                model = {}
                model["date"] = m.get("createdTime", "")
                model["platform"] = "pingstart"
                model["country"] = m.get("country", "")
                model["appname"] = m.get("", "")
                model["placement"] = m.get("name", "")
                model["request"] = m.get("request", "0")
                model["filled"] = m.get("fill", "0")
                model["impression"] = m.get("impression", "0")
                model["click"] = m.get("show_click", "0")
                model["filled_rate"] = m.get("FillRate", "0")
                model["ctr"] = m.get("CTR", "0")
                model["ecpm"] = m.get("eCPM", "0")
                model["revenue"] = m.get("show_revenue", "0")
                for key in model.keys():
                    val = model[key]
                    if isinstance(val, (str, unicode)) and r'*' in val:
                        model[key] = "0"
                res_list.append(model)
        except Exception as e:
            self.status = False
            self.report_error("parse_data():Error:%s" % e)
        finally:
            return res_list

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
    pingstart = PingstartAdApi('1045.rxxLGDyICUAemQm7', "gd_bundle-g2-g1.crt")
    x = pingstart.fetch("2016-11-25", "2016-11-25", "country", "1000080")
    print x
