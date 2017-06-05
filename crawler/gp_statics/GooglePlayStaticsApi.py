#!/usr/bin/python
# -*- coding: utf-8 -*-

import json
import datetime
import time
import requests
import os.path


def check_ca_cert():
    # 可能存在的路径
    ca_certs = [
        "/etc/pki/tls/certs/ca-bundle.crt",
        "/etc/ssl/certs/ca-certificates.crt",
    ]
    for f in ca_certs:
        if os.path.exists(f):
            return f
    return False


class GooglePlayStaticsApi(object):
    def __init__(self, account, dev_acc, cookies, x_client_data, xsrf="", proxies=None, timeout=60):
        self.account = account
        self.dev_acc = dev_acc

        if isinstance(cookies, (str, unicode)):
            self.cookies = self.parse_cookies(cookies)
        elif isinstance(cookies, dict):
            self.cookies = cookies
        else:
            self.log('__init__:use cookie = {}.')
            self.cookies = {}

        self.x_client_dataa = x_client_data
        self.xsrf = xsrf
        self.proxies = proxies
        self.timeout = timeout
        # error message
        self.status = True
        self.messages = []
        self.outer_source_map = {
            "1": "google_play_nature",
            "2": "AdWords",
            "3": "channel",
            "4": "google_search",
            "5": "third_party"
        }
        self.ca_certs = check_ca_cert()

    @staticmethod
    def parse_cookies(cookie_text):
        cookies = {}
        items = cookie_text.split(";")
        for item in items:
            key, value = item.split("=", 1)
            key = key.strip()
            value = value.strip()
            cookies[key] = value
        return cookies

    def refresh_token(self, xsrf=None):
        self.status = True
        if xsrf and isinstance(xsrf, (str, unicode)):
            self.xsrf = xsrf
        url = "https://play.google.com/apps/publish/r?dev_acc={dev_acc}".format(dev_acc=self.dev_acc)
        headers = {
        }
        body = {"method": "q",
                "params": {"1": "Rs"},
                "xsrf": self.xsrf}
        res = self.net_post(url, headers=headers, body=body)
        if not res:
            self.status = False
            self.log("refresh_token():Get None.")
            return None
        _xsrf = None
        try:
            _xsrf = res.get("xsrf", None)
            if not _xsrf or not isinstance(_xsrf, (str, unicode)):
                self.log("refresh_token():refresh token failed.")
            else:
                self.xsrf = _xsrf
        except Exception as e:
            self.status = False
            self.log("refresh_token():Exception:{e}".format(e=e))
        finally:
            return _xsrf

    def fetch_package_list(self):
        self.status = True
        url = "https://play.google.com/apps/publish/androidapps?dev_acc={dev_acc}".format(dev_acc=self.dev_acc)
        headers = {

        }
        _xsrf = self.refresh_token()
        body = {"method": "fetch",
                "params": {"2": 1, "3": 7},
                "xsrf": _xsrf}
        # Get
        res = self.net_post(url, headers=headers, body=body)
        if not res:
            self.status = False
            self.log("fetch_package_list():Get None.")
            return None
        # Parse
        package_list = []
        try:
            xlist = res["result"]["1"]
            for m in xlist:
                pkg_name = None
                try:
                    pkg_name = m["1"]["1"]
                    # detail = m["1"]["2"]["1"][0]["2"]
                except Exception as e:
                    self.log("fetch_package_list():Exception:%s" % e)
                finally:

                    pass
                if isinstance(pkg_name, (str, unicode)):
                    package_list.append(pkg_name)

        except Exception as e:
            self.status = False
            self.log("fetch_package_list():Exception:{e}".format(e=e))
        finally:
            return package_list

    def fetch_inner_users(self, package_name, start_date, stop_date=None):
        return self.fetch_users(package_name, start_date, stop_date, is_inner=True)

    def fetch_outer_users(self, package_name, start_date, stop_date=None):
        return self.fetch_users(package_name, start_date, stop_date, is_inner=False)

    def fetch_users(self, package_name, start_date, stop_date=None, is_inner=True):
        inner_payload_count = 1
        outer_payload_count = 2
        payload_count = 0
        if is_inner:
            payload_count = inner_payload_count
        else:
            payload_count = outer_payload_count
        for i in xrange(payload_count):
            res = self.fetch_users_internal(package_name, start_date, stop_date, is_inner=is_inner, which_payload=i)
            if self.status:
                return res

    def fetch_users_internal(self, package_name, start_date, stop_date=None, is_inner=True, which_payload=0):
        """
        :param package_name: the package name to query
        :param start_date: format : 20161128
        :param stop_date:  format : 20161128 可选;不存在是等于start_date
        :param is_inner : boolean : True 内部流量;False 外部流量
        :return: list(recorder); None:失败
        recorder={
            "source":"widget",      # 流量来源
            "views":100,            # 访问数量
            "installed":10          # 安装数量
        }
        """
        self.status = True
        # print "which_payload=", which_payload

        url = "https://play.google.com/apps/publish/statistics?dev_acc={dev_acc}".format(dev_acc=self.dev_acc)
        headers = {}
        # _xsrf = self.refresh_token()
        if not stop_date:
            stop_date = start_date
        body = None
        if is_inner:
            body_0 = {"method": "fetchStats",
                      "params": {"1": [
                          {"1": {"1": package_name, "2": 1}, "2": int(start_date), "3": int(stop_date),
                           "6": [{"1": 35, "2": ["1"]}, {"1": 37, "2": ["3"]}], "7": [38], "8": [52],
                           "9": [{"2": 52, "3": 2}]},
                          {"1": {"1": package_name, "2": 1}, "2": int(start_date), "3": int(stop_date),
                           "6": [{"1": 36, "2": ["180"]}, {"1": 35, "2": ["1"]}, {"1": 37, "2": ["3"]}],
                           "7": [38], "8": [61]}]},
                      "xsrf": self.xsrf}
            if which_payload == 0:
                body = body_0
            else:
                return None
        else:
            body_0 = {"method": "fetchStats",
                      "params": {"1": [
                          {"1": {"1": package_name, "2": 1}, "2": int(start_date), "3": int(stop_date),
                           "6": [{"1": 35, "2": ["1"]}, {"1": 37, "2": ["1", "2", "3", "4", "5"]}], "7": [37],
                           "8": [52]},
                          {"1": {"1": package_name, "2": 1}, "2": int(start_date), "3": int(stop_date),
                           "6": [{"1": 36, "2": ["180"]}, {"1": 35, "2": ["1"]},
                                 {"1": 37, "2": ["1", "2", "3", "4", "5"]}], "7": [37], "8": [61]},
                          {"1": {"1": package_name, "2": 1}, "2": int(start_date), "3": int(stop_date),
                           "6": [{"1": 36, "2": ["180"]}, {"1": 35, "2": ["1"]}], "7": [44, 45],
                           "8": [74, 75, 76, 77, 79]}]},
                      "xsrf": self.xsrf}
            body_1 = {"method": "fetchStats",
                      "params": {"1": [
                          {"1": {"1": package_name, "2": 1}, "2": int(start_date), "3": int(stop_date),
                           "6": [{"1": 35, "2": ["1"]}, {"1": 37, "2": ["1", "2", "3", "4", "5"]}], "7": [37],
                           "8": [52]},
                          {"1": {"1": package_name, "2": 1}, "2": int(start_date), "3": int(stop_date),
                           "6": [{"1": 36, "2": ["180"]}, {"1": 35, "2": ["1"]},
                                 {"1": 37, "2": ["1", "2", "3", "4", "5"]}],
                           "7": [37], "8": [61]}]},
                      "xsrf": self.xsrf}
            if which_payload == 0:
                body = body_0
            elif which_payload == 1:
                body = body_1
            else:
                return None

        # print json.dumps(body, sort_keys=True, ensure_ascii=False).encode('utf-8')
        # Get
        res = self.net_post(url, headers=headers, body=body)
        if not res:
            self.status = False
            self.log("fetch_users():Net Get None.")
            return None

        # Parse
        users_count_list = None
        try:
            _dict = {}
            view_list = res["result"]["1"][0]["1"]
            install_list = res["result"]["1"][1]["1"]
            # 浏览量
            for v in view_list:
                name = v["1"][0]
                if name in self.outer_source_map.keys():
                    name = self.outer_source_map[name]
                value = v["2"][0]["1"]
                d = {
                    "views": value,
                    "installed": 0,
                    "package": package_name[0:100]
                }
                if isinstance(name, (str, unicode)):
                    name = name[0:100]
                if is_inner:
                    d["c1"] = "channel"
                    d["c2"] = name
                else:
                    d["c1"] = name
                    d["c2"] = ""
                # print d
                _dict[name] = d
            # 安装量
            for v in install_list:
                name = v["1"][0]
                if name in self.outer_source_map.keys():
                    name = self.outer_source_map[name]
                value = v["2"][0]["1"]
                d = _dict.get(name, None)
                if not d:
                    continue
                d["installed"] = value
                # print d
            users_count_list = _dict.values()
        except Exception as e:
            self.status = False
            self.log("fetch_users():Exception:{e}:RawData:[{raw_data}]".format(e=e, raw_data=res))
        finally:
            return users_count_list

    def fetch_country(self, package_name, start_date, stop_date=None):
        self.status = True
        # print "which_payload=", which_payload

        url = "https://play.google.com/apps/publish/statistics?dev_acc={dev_acc}".format(dev_acc=self.dev_acc)
        headers = {}
        # _xsrf = self.refresh_token()
        if not stop_date:
            stop_date = start_date
        body = {"method": "fetchStats",
                "params": {"1": [
                    {"1": {"1": package_name, "2": 1}, "2": start_date, "3": stop_date,
                     "6": [{"1": 35, "2": ["1"]}, {"1": 37, "2": ["1", "2", "3", "4", "5"]}], "7": [3], "8": [52],
                     "9": [{"2": 52, "3": 2}]},
                    {"1": {"1": package_name, "2": 1}, "2": start_date, "3": stop_date,
                     "6": [{"1": 36, "2": ["180"]}, {"1": 35, "2": ["1"]}, {"1": 37, "2": ["1", "2", "3", "4", "5"]}],
                     "7": [3], "8": [61]}]},
                "xsrf": self.xsrf}
        res = self.net_post(url, headers=headers, body=body)
        if not res:
            self.status = False
            self.log("fetch_country(%s,%s,%s):Net Get None." % (package_name, start_date, stop_date))
            return None
        country_data_list = []
        # print res
        try:
            _dict = {}
            view_list = res["result"]["1"][0]["1"]
            install_list = res["result"]["1"][1]["1"]
            # view
            for v in view_list:
                cc = v['1'][0][:2]
                value = v['2'][0]['1']
                country_name = v['4'][0]
                d = {
                    'type': 'common',
                    'cc': cc,
                    'country': country_name[0:100],
                    "package": package_name[0:100],
                    'views': value,
                    'installed': 0,
                }
                _dict[cc] = d
            # installed
            for v in install_list:
                cc = v['1'][0][:2]
                value = v['2'][0]['1']
                d = _dict.get(cc, None)
                if not d:
                    continue
                d['installed'] = value
            country_data_list = _dict.values()
        except Exception as e:
            self.status = False
            self.log("fetch_country():Exception:{e}:RawData:[{raw_data}]".format(e=e, raw_data=res))
        finally:
            return country_data_list

    def fetch_country_organic(self, package_name, start_date, stop_date=None):
        self.status = True
        # print "which_payload=", which_payload

        url = "https://play.google.com/apps/publish/statistics?dev_acc={dev_acc}".format(dev_acc=self.dev_acc)
        headers = {}
        # _xsrf = self.refresh_token()
        if not stop_date:
            stop_date = start_date
        body = {"method": "fetchStats",
                "params": {"1": [
                    {"1": {"1": package_name, "2": 1}, "2": start_date, "3": stop_date,
                     "6": [{"1": 35, "2": ["1"]}, {"1": 37, "2": ["1"]}], "7": [3], "8": [52],
                     "9": [{"2": 52, "3": 2}]},
                    {"1": {"1": package_name, "2": 1}, "2": start_date, "3": stop_date,
                     "6": [{"1": 36, "2": ["180"]}, {"1": 35, "2": ["1"]}, {"1": 37, "2": ["1"]}],
                     "7": [3], "8": [61]},
                ]},
                "xsrf": self.xsrf}
        res = self.net_post(url, headers=headers, body=body)
        if not res:
            self.status = False
            self.log("fetch_country_organic(%s,%s,%s):Net Get None." % (package_name, start_date, stop_date))
            return None
        country_data_list = []
        # print res
        try:
            _dict = {}
            view_list = res["result"]["1"][0]["1"]
            install_list = res["result"]["1"][1]["1"]
            # print "conversion_list", conversion_list
            # view
            for v in view_list:
                cc = v['1'][0][:2]
                value = v['2'][0]['1']
                country_name = v['4'][0]
                d = {
                    'type': 'organic',
                    'cc': cc,
                    'country': country_name[0:100],
                    "package": package_name[0:100],
                    'views': value,
                    'installed': 0,

                }
                _dict[cc] = d
            # installed
            for v in install_list:
                cc = v['1'][0][:2]
                value = v['2'][0]['1']
                d = _dict.get(cc, None)
                if not d:
                    continue
                d['installed'] = value
            country_data_list = _dict.values()
        except Exception as e:
            self.status = False
            self.log("fetch_country_organic():Exception:{e}:RawData:[{raw_data}]".format(e=e, raw_data=res))
        finally:
            return country_data_list

    def fetch_conversion(self, package_name, start_date, duration='day'):
        self.status = True
        # print "which_payload=", which_payload

        url = "https://play.google.com/apps/publish/statistics?dev_acc={dev_acc}".format(dev_acc=self.dev_acc)
        headers = {}
        # duration : day, week, month
        payload = {
            "method": "fetchStats",
            "xsrf": self.xsrf
        }
        if duration == 'day':
            payload['params'] = {"1": [
                {"1": {"1": package_name, "2": 1}, "2": start_date, "3": start_date,
                 "6": [{"1": 36, "2": ["180"]}, {"1": 35, "2": ["1"]}], "7": [44, 45, 3],
                 "8": [74, 75, 76, 77, 79]
                 },
                {"1": {"1": package_name, "2": 1}, "2": start_date, "3": start_date,
                 "6": [{"1": 36, "2": ["180"]}, {"1": 35, "2": ["1"]}], "7": [44, 45],
                 "8": [74, 75, 76, 77, 79]
                 }
            ]}
        elif duration == 'week':
            _tm = time.strptime(str(start_date), '%Y%m%d')
            _date = datetime.date(_tm.tm_year, _tm.tm_mon, _tm.tm_mday)
            _weekday = _date.weekday()
            # print "weekday", _weekday
            _start_date = _date - datetime.timedelta(days=_weekday)
            start_date = "%04d%02d%02d" % (_start_date.year, _start_date.month, _start_date.day)
            payload['params'] = {"1": [
                {"1": {"1": package_name, "2": 1}, "2": start_date, "3": start_date,
                 "6": [{"1": 36, "2": ["180"]}, {"1": 35, "2": ["7"]}], "7": [44, 45, 3],
                 "8": [74, 75, 76, 77, 79]
                 },
                {"1": {"1": package_name, "2": 1}, "2": start_date, "3": start_date,
                 "6": [{"1": 36, "2": ["180"]}, {"1": 35, "2": ["7"]}], "7": [44, 45],
                 "8": [74, 75, 76, 77, 79]
                 }
            ]}
            # print payload
        elif duration == 'month':
            _tm = time.strptime(str(start_date), '%Y%m%d')
            start_date = "%04d%02d%02d" % (_tm.tm_year, _tm.tm_mon, 1)
            payload['params'] = {"1": [
                {"1": {"1": package_name, "2": 1}, "2": start_date, "3": start_date,
                 "6": [{"1": 36, "2": ["180"]}, {"1": 35, "2": ["30"]}], "7": [44, 45, 3],
                 "8": [74, 75, 76, 77, 79]
                 },
                {"1": {"1": package_name, "2": 1}, "2": start_date, "3": start_date,
                 "6": [{"1": 36, "2": ["180"]}, {"1": 35, "2": ["30"]}], "7": [44, 45],
                 "8": [74, 75, 76, 77, 79]
                 }
            ]}
            # print payload
        else:
            self.log("fetch_conversion():Bad Param `duration`:[%s]" % duration)
            return None
        res = self.net_post(url, headers=headers, body=payload)
        if not res:
            self.status = False
            self.log("fetch_conversion(%s,%s,%s):Net Get None." % (package_name, start_date, duration))
            return None
        conversion_res_list = []
        try:
            conversion_list = res["result"]["1"][0]["1"]
            total_conversion_list = res["result"]["1"][1]["1"]

            for v in conversion_list:
                cc = v['1'][2][:2]
                p25 = v['2'][1]['1']
                p50 = v['2'][2]['1']
                p75 = v['2'][3]['1']
                country_name = v['4'][2]
                d = {
                    'type': 'conversion',
                    'date': start_date,
                    'duration': duration,
                    'cc': cc,
                    'country': country_name[0:100],
                    "package": package_name[0:100],
                    'p25': "0." + p25,
                    'p50': "0." + p50,
                    'p75': "0." + p75,
                }
                conversion_res_list.append(d)
            for v in total_conversion_list:
                cc = '99'
                p25 = v['2'][1]['1']
                p50 = v['2'][2]['1']
                p75 = v['2'][3]['1']
                country_name = 'TOTAL'
                d = {
                    'type': 'conversion',
                    'date': start_date,
                    'duration': duration,
                    'cc': cc,
                    'country': country_name[0:100],
                    "package": package_name[0:100],
                    'p25': "0." + p25,
                    'p50': "0." + p50,
                    'p75': "0." + p75,
                }
                conversion_res_list.append(d)
        except Exception as e:
            self.status = False
            self.log("fetch_conversion():Exception:{e}:RawData:[{raw_data}]".format(e=e, raw_data=res))
        finally:
            return conversion_res_list

    def fetch_app_text(self, package_name):
        self.status = True
        # print "which_payload=", which_payload

        url = "https://play.google.com/apps/publish/androidapps?dev_acc={dev_acc}".format(dev_acc=self.dev_acc)
        headers = {}
        # duration : day, week, month
        payload = {"method": "fetch",
                   "params": {"1": [package_name], "3": 0},
                   "xsrf": self.xsrf}
        res = self.net_post(url, headers=headers, body=payload)

        results_list = []

        try:
            data = res['result']['1'][0]['1']
            package = data['1']
            text_list = data['2']['1']
            for text in text_list:
                lang = text['1']
                title = text['2']
                short_description = text['4']
                full_description = text['3']
                model = {
                    'package': package,
                    'lang': lang,
                    'title': title,
                    'short_description': short_description,
                    'full_description': full_description
                }
                results_list.append(model)
        except Exception as e:
            self.status = False
            self.log("fetch_app_text():Exception:{e}:RawData:[{raw_data}]".format(e=e, raw_data=res))
        finally:
            return results_list

    def net_post(self, url, headers=None, body=None):
        default_headers = {
            "accept": "*/*",
            "accept-encoding": "gzip, deflate, br",
            "accept-language": "zh-CN,zh;q=0.8",
            "cache-control": "no-cache",
            "content-type": "application/javascript; charset=UTF-8",
            "origin": "https://play.google.com",
            "pragma": "no-cache",
            "referer": "https://play.google.com/apps/publish/?account={account}&dev_acc={dev_acc}".format(
                account=self.account,
                dev_acc=self.dev_acc),
            "user-agent": "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) "
                          "Chrome/55.0.2883.87 Safari/537.36",
            "x-chrome-uma-enabled": "1",
            "x-client-data": self.x_client_dataa,
            "x-gwt-module-base": "https://play.google.com/apps/publish/fox/gwt/",
            "x-gwt-permutation": "9C66E406A448B8382C5277897EFA717F",
        }
        if headers and isinstance(headers, dict):
            default_headers.update(headers)
        if not isinstance(body, dict):
            body = {}
        if not url or not isinstance(url, (str, unicode)):
            self.log("net_post():url is bad.[{url}]".format(url=url))
            return None
        res = None
        r_header = None

        # print "header:", json.dumps(default_headers, indent=4, sort_keys=True, ensure_ascii=False).encode('utf-8')
        # print "body:", json.dumps(body, indent=4, sort_keys=True, ensure_ascii=False).encode('utf-8')
        # print json.dumps(body, sort_keys=True)
        try:
            json_body = json.dumps(body)
            r = requests.post(url, headers=default_headers, cookies=self.cookies,
                              data=json_body, proxies=self.proxies, timeout=self.timeout, verify=self.ca_certs)
            r_header = r.headers
            # print r.text
            res = r.json()
            # auto refresh token
            _xsrf = res.get("xsrf", None)
            if _xsrf:
                self.xsrf = _xsrf
        except Exception as e:
            self.status = False
            self.log("net_post():Exception:{e}:response headers:[{r_header}]".format(e=e, r_header=r_header))
        finally:
            return res

    def log(self, message):
        if not isinstance(self.messages, list):
            self.messages = []
        msg = "{class_name}:{message}".format(class_name=self.__class__.__name__,
                                              message=message)
        self.messages.append(msg)

    def get_message(self):
        return self.messages


# -- debug --

def debug():
    # # These two lines enable debugging at httplib level (requests->urllib3->http.client)
    # # You will see the REQUEST, including HEADERS and DATA, and RESPONSE with HEADERS but without DATA.
    # # The only thing missing will be the response.body which is not logged.
    requests_debug = False
    test_package_list = False
    test_refresh_token = True
    extra_test = True

    account_auth_list = [
        "amberweatherapp@gmail.com",  # 0
        'luoluo@amberweather.com',  # 1
        'mars@amberweather.com',  # 2
        'amberwidgetthemes@gmail.com',  # 3
        'amberutilapps@gmail.com',  # 4
    ]
    which_account = 1

    if requests_debug:
        import logging

        try:
            import http.client as http_client
        except ImportError:
            # Python 2
            import httplib as http_client
        http_client.HTTPConnection.debuglevel = 1

        # You must initialize logging, otherwise you'll not see debug output.
        logging.basicConfig()
        logging.getLogger().setLevel(logging.DEBUG)
        requests_log = logging.getLogger("requests.packages.urllib3")
        requests_log.setLevel(logging.DEBUG)
        requests_log.propagate = True
    # start test
    from config import google_play_statics_auth_list

    auth = google_play_statics_auth_list[account_auth_list[which_account]]

    print json.dumps(auth, indent=4, separators=(',', ': '))

    account = auth["account"]
    dev_acc = auth["dev_acc"]
    xsrf = auth["xsrf"]
    cookie = auth["cookie"]
    x_client_data = auth["x_client_data"]

    proxies = None

    proxies = {
        "https": "http://127.0.0.1:8118",
        "http": "http://127.0.0.1:8118"
    }

    g = GooglePlayStaticsApi(account, dev_acc,
                             cookies=cookie,
                             x_client_data=x_client_data,
                             xsrf=xsrf,
                             proxies=proxies)

    if test_refresh_token:
        print "=" * 40
        print "test refresh_token()"
        token = g.refresh_token()
        print "success:", g.status
        print "message:", "\n".join(g.get_message())
        print "New Token:", token

    if test_package_list:
        print "=" * 40
        print "test fetch_package_list()"
        pkg_list = g.fetch_package_list()
        print "success:", g.status
        print "message:", "\n".join(g.get_message())
        print  "\n".join(pkg_list)

    if not extra_test:
        exit()

    # package = pkg_list[0]
    # package = 'mobi.infolife.ezweather.widget.window'
    # package = 'mobi.infolife.ezweather.widget.sonyzstyle'
    # package = 'mobi.infolife.ezweather.widget.newyear2015'
    # package = 'mobi.infolife.ezweather.widget.leagoo'
    # for package in pkg_list:
    package = 'mobi.infolife.ezweather'
    # package = 'mobi.infolife.ezweatherlite'
    # package = 'mobi.infolife.ezweatherlite'
    # package = 'mobi.infolife.ezweather.widget.goldenweather'
    # package = 'mobi.infolife.ezweather.widget.bible'
    # package = 'mobi.infolife.ezweather.widget.photos'
    # package = 'mobi.infolife.ezweather'
    # package = 'mobi.infolife.ezweather.widget.royal_blue'
    # package = 'com.amberweather.muiltifunctionwidget.clockweathertaichi'
    package = 'mobi.infolife.ezweather.widget.leagoo'
    print "package", package

    print "=" * 40
    # print "test fetch_outer_users()"
    # data_list = g.fetch_outer_users(package, "20170102")
    # data_list = g.fetch_inner_users(package, '20170109')
    # data_list = g.fetch_country(package, '20170201')
    # data_list = g.fetch_country_organic(package, '20170201')

    data_list = g.fetch_conversion(package, '20170206', 'month')
    print "success:", g.status
    print "message:", "\n".join(g.get_message())
    print json.dumps(data_list, indent=4, separators=(',', ': '))

    # data_list = g.fetch_app_text(package)
    # print "success:", g.status
    # print "message:", "\n".join(g.get_message())
    # print json.dumps(data_list, indent=4, separators=(',', ': '))

    print "=" * 40
    print "Done."


if __name__ == '__main__':
    debug()
