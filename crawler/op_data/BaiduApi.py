#!/usr/bin/python
# -*- coding:utf-8 -*-

import urllib2
import json
import re


class BaiduApi(object):
    def __init__(self):
        self.code = 0
        self.message = []
        self.status = True
        self.total = 0
        # error code meaning
        self.code_message = {
            "500": "system error",
            "1000": "system error",
            "1001": "username is required",
            "1002": "appid is required",
            "1003": "date is required",
            "1004": "source is required",
            "1005": "date format is yyyy-mm-dd",
            "1006": "source must be facebook,dap,all",
        }

    def fetch(self, date_str, username, appid, source="dap"):
        """
        :param username:
        :param appid:
        :param date_str: "2016-10-01"
        :param source:
        :return:
        """
        self.total = 0

        pattern = "\d{4}\-\d{2}\-\d{2}"
        if not re.match(pattern, date_str):
            self.status = False
            self.report("fetch():date_str must be yyyy-mm-dd")
            return None
        url = self.gen_url(username, appid, date_str, source=source)
        self.report("FETCH URL:%s" % url)
        data = None
        try:
            f = urllib2.urlopen(url)
            data = f.read()
            # print "url:", url
            # with open("./memo/baidu.json", "r") as f:
            #     data = f.read()
        except Exception as e:
            self.status = False
            self.report("fetch().Net Error;url[%s]:Exception:[%s]" % (url, e))
        if not data:
            return None
        # print data
        model_list = self.parse_data(data)
        if not model_list:
            self.status = False
            self.report("fetch():parse_data Error.")
            return None
        else:
            return model_list

    def gen_url(self, username, appid, date_str, source="dap"):
        """
        :param username:
        :param appid:
        :param date_str:
        :param source:
        :return: http://api.developers.duapps.com/report?username=xxx&appid=xxx&date=2016-11-01&source=all
        """
        url = "http://api.developers.duapps.com/report"
        url += "?username=" + username
        url += "&appid=" + appid
        url += "&date=" + date_str
        url += "&source=" + source
        return url

    def parse_data(self, data):
        model_list = []
        try:
            js = json.loads(data)
            code = js.get("code", None)
            if code != 0:
                message = js.get("message")
                self.code = code
                self.status = False
                self.report(message)
                return None
            else:
                self.report("success")
            res_list = js.get("data", None)
            if not res_list:
                self.status = False
                self.report("parse_data:get data is None")
                return None

            for res in res_list:
                model = {}
                model["app"] = res.get("app_pkg", "")
                model["click"] = res.get("click_num", "")
                model["country"] = res.get("country_code", "")
                model["date"] = res.get("data_date", "")
                model["filled"] = res.get("fill_num", "")
                model["placement"] = res.get("placement_id", "")
                model["request"] = res.get("request_num", "")
                model["revenue"] = res.get("revenue", "")
                model["impression"] = res.get("show_num", "")
                model_list.append(model)
                self.total += 1
        except Exception as e:
            self.status = False
            self.report("parse_data():Exception:%s" % e)
        finally:
            return model_list

    def get_status(self):
        return self.status

    def get_code(self):
        return self.code

    def get_message(self):
        return self.message

    def report(self, msg):
        if not isinstance(self.message, list):
            self.message = []
        else:
            self.message.append("{%s}:%s" % (self.__class__.__name__, msg))


# debug

if __name__ == "__main__":
    baidu = BaiduApi()
    # data = baidu.fetch("www", "gg", "2016-11-11")
    with open("./memo/baidu.json", "r") as f:
        data = f.read()
        data = baidu.parse_data(data)
        print baidu.status
        print baidu.message
        print baidu.code
        print data
