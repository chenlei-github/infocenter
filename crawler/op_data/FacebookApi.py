#!/usr/bin/python
# -*- coding:utf-8 -*-

import urllib2
import logging
import json


class FacebookApi(object):
    def __init__(self, appid=None, secret=None):
        self.appid = appid
        self.secret = secret
        self.status = True  # True:success
        self.message = None
        self.data = None
        self.dimension = []
        self.field_list = ["revenue", "request", "filled", "impression", "click", "placement", "country"]
        self.event_names = {
            "revenue": {'event_name': 'fb_ad_network_revenue', 'aggregate': 'SUM'},
            "request": {'event_name': 'fb_ad_network_request', 'aggregate': 'COUNT'},
            "filled": {'event_name': 'fb_ad_network_request', 'aggregate': 'SUM'},
            "impression": {'event_name': 'fb_ad_network_imp', 'aggregate': 'COUNT'},
            "click": {'event_name': 'fb_ad_network_click', 'aggregate': 'COUNT'},
        }
        self.has_country = False
        self.has_placement = False

    def fetch(self, time_start=None, time_stop=None, fields=None):
        # check fields
        for f in fields:
            if f not in self.field_list:
                self.report_error("FaceBookApi.fetch():%s not in field_list:[%s]" % (f, self.field_list))
                return
        if not time_start or not time_stop \
                or not isinstance(time_start, int) \
                or not isinstance(time_stop, int) or time_start > time_stop \
                or not isinstance(fields, (list, tuple)):
            self.report_error("FaceBookApi.fetch(%s,%s,%s):Bad Parameter" % (time_start, time_stop, fields))
            return

        self.data = {}
        self.has_country = False
        self.has_placement = False
        if "country" in fields:
            self.has_country = True
            # fields.__delitem__(fields.index("country"))
        if "placement" in fields:
            self.has_placement = True
            # fields.__delitem__(fields.index("placement"))

        for fff in fields:
            if fff not in self.event_names:
                continue
            event_name = self.event_names[fff]["event_name"]
            aggregate = self.event_names[fff]["aggregate"]
            token = self.get_token()
            text = self.get_data(token, time_start=time_start, time_stop=time_stop,
                                 event_name=event_name, aggregate=aggregate)
            if not text:
                self.report_error("FaceBookApi.fetch.get_data:NONE")
                continue
            self.parse_data(text, fff)

        # prepare data
        res = []
        unique_key_list = self.data.keys()
        for key in unique_key_list:
            dt = self.data.get(key, None)
            if not isinstance(dt, dict):
                continue
            time_str, country, placement = key.split("\0")

            dt["date"] = time_str
            # if self.has_country:
            dt["country"] = country
            # if self.has_placement:
            dt["placement"] = placement
            res.append(dt)
        return res

    def report_error(self, msg):
        self.status = False
        if not isinstance(self.message, list):
            self.message = []
        self.message.append(msg)
        logging.error(msg)

    def status(self):
        return self.status

    def message(self):
        return self.message

    def get_token(self):
        access_token = None
        url = 'https://graph.facebook.com/oauth/access_token'
        url += '?client_id=' + self.appid
        url += '&client_secret=' + self.secret
        url += '&grant_type=client_credentials'
        try:
            f = urllib2.urlopen(url)
            data = f.read()
            _js = json.loads(data)
            _token = _js['access_token']
            access_token = "access_token=%s" % _token
        except Exception as e:
            self.report_error("FaceBookApi.get_token():Exception:%s" % e)
        finally:
            return access_token

    def get_data(self, token, time_start, time_stop, event_name, aggregate):
        url = 'https://graph.facebook.com/v2.5/' + self.appid
        url += '/app_insights/app_event/?since=%s' % time_start
        url += '&until=%s' % time_stop
        url += '&summary=true&event_name=' + event_name
        url += '&aggregateBy=' + aggregate
        if self.has_placement and self.has_country:
            url += '&breakdowns[0]=placement'
            url += '&breakdowns[1]=country'
        elif self.has_placement:
            url += '&breakdowns[0]=placement'
        elif self.has_country:
            url += '&breakdowns[0]=country'
        url += '&' + token
        logging.info("FaceBookApi.get_data():URL:'%s'" % url)
        data = None
        try:
            f = urllib2.urlopen(url)
            data = f.read()
        except Exception as e:
            logging.exception(
                "FaceBookApi.get_data():[%s,%s,%s,%s]Exception:%s" % (self.appid, token, event_name, aggregate, e))
        finally:
            return data

    def parse_data(self, data_json, field_name):
        try:
            js = json.loads(data_json)
            lst = js.get('data')
            for rec in lst:
                time_str = rec.get("time", None)
                if not time_str:  # or timestr[:len('2016-10-25')] != dest_time_str:
                    logging.error("FaceBookApi:parse_data:{Time Is None}[%s]%s:" % (field_name, json.dumps(rec)))
                    continue
                time_str = time_str[:10]  # len('2016-10-25')
                value = rec.get("value", None)
                if not value:
                    logging.error("FaceBookApi:parse_data:{Value Is None}:%s:" % json.dumps(rec))
                    continue
                breakdowns = rec.get("breakdowns", None)
                placement = "unknown"
                country = "00"
                if breakdowns:
                    placement = breakdowns.get("placement", "none")
                    country = breakdowns.get("country", "none")
                    if country == "REDACTED":
                        country = "00"
                self.save_data(time_str, country, placement, field_name, value)
        except Exception as e:
            logging.exception("parse_data:Exception:%s:data:[%s]" % (e, data_json))
        finally:
            return

    def save_data(self, time_str, country, placement, field_name, value):
        # print (time_str, country, placement)
        unique_key = '\0'.join((time_str, country, placement))
        dt = self.data.get(unique_key, None)
        if not dt or not isinstance(dt, dict):
            self.data[unique_key] = {"%s" % field_name: value}
        else:
            dt["%s" % field_name] = value


# below are test
def init_log(log_file, debug=False):
    # set up logging to file - see previous section for more details
    logging.basicConfig(level=logging.DEBUG,
                        format='%(asctime)s %(name)-12s %(levelname)-8s %(message)s',
                        datefmt='%m-%d %H:%M',
                        filename=log_file,
                        filemode='a')
    if debug:
        # define a Handler which writes INFO messages or higher to the sys.stderr
        console = logging.StreamHandler()
        console.setLevel(logging.INFO)
        # set a format which is simpler for console use
        formatter = logging.Formatter('%(name)-12s: %(levelname)-8s %(message)s')
        # tell the handler to use this format
        console.setFormatter(formatter)
        # add the handler to the root logger
        logging.getLogger('').addHandler(console)


if __name__ == "__main__":
    init_log("facebook.log", True)
    # facebook = FacebookApi("00", "00")

    import config
    import os

    os.environ['http_proxy'] = 'http://127.0.0.1:8118'
    os.environ['https_proxy'] = 'http://127.0.0.1:8118'

    auth = config.facebook_appid_config['amber_weather']
    facebook = FacebookApi(auth['appid'], auth['secret'])
    token = facebook.get_token()
    data = facebook.fetch(1479600000, 1479772800,
                          ["revenue", "request", "filled", "impression", "click", "country", "placement"])
    print data
    print facebook.status
    print facebook.message
