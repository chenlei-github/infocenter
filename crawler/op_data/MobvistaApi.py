#!/usr/bin/python
# -*- coding:utf-8 -*-
import json
import logging
import copy
import urllib
import hashlib
import time
import requests
import datetime
import re


class MobvistaApi(object):
    def __init__(self, skey, secret, version='1.0'):
        self.skey = skey
        self.secret = secret
        self.version = version
        self.field_list = ['country', 'date', 'app_id', 'unit_id']
        self.field_map = {
            'country': 'country',
            'date': 'date',
            'placement': 'unit_id',
        }

    def fetch_report(self, time_start=None, time_stop=None, fields=None, app_id=None, unit_id=None):
        ret_list = []

        page = 1
        limit = 100
        remain = 0
        rgx = r'^\d{8}$'
        rx = re.compile(rgx)
        while True:
            try:
                # print '(page,limit,remain)=', (page, limit, remain)
                js = self.net_get_report(time_start=time_start, time_stop=time_stop, fields=fields, page=page,
                                         limit=limit, app_id=app_id, unit_id=unit_id)

                # status = js['code']
                data = js['data']
                total = int(data['total'])
                lists = data['lists']
                if remain == 0:
                    remain = total - len(lists)
                else:
                    remain -= len(lists)
                for m in lists:
                    time_str = m.get('date', None)
                    if not time_str or not rx.match(time_start):
                        time_str = time_start
                    time_str = str(time_str)
                    # print "time_str:%s", time_str
                    date = datetime.datetime.strptime(time_str, '%Y%m%d')
                    m['date'] = date
                ret_list += lists
            except Exception as e:
                logging.error("MobvistaApi:fetch_report():Error:%s" % e)
            finally:
                if remain >= limit:
                    page += 1
                elif 0 < remain < limit:
                    page += 1
                    limit = remain
                else:
                    break
        return ret_list

    def convert_to_inner_fields(self, fields_list):
        inner_fields = []
        for f in fields_list:
            if f in self.field_map.keys():
                v = self.field_map[f]
                inner_fields.append(v)
            elif f in self.field_list:
                inner_fields.append(f)
        if 'placement' in fields_list:
            inner_fields.append('app_id')
            inner_fields.append('unit_id')
        return self.unique_list_item(inner_fields)

    def gen_signature(self, params):
        if not isinstance(params, (dict)):
            logging.error("MobvistaApi:gen_signature:Bad Params:%s" % params)
            return None
        _param = copy.deepcopy(params)
        _param['skey'] = self.skey
        _param['v'] = self.version
        keys = sorted(_param.keys())
        l = []
        for k in keys:
            v = _param[k]
            k = urllib.quote_plus(str(k))
            v = urllib.quote_plus(str(v))
            l.append(k + '=' + v)
        query = '&'.join(l)
        signature = hashlib.md5(hashlib.md5(query).hexdigest() + self.secret).hexdigest()
        return signature

    def net_get_country(self):
        url_pattern = 'http://oauth2.mobvista.com/m/data/get_country?{query}'
        params = {
            'skey': self.skey,
            'time': int(time.time()),
            'v': self.version
        }
        sign = self.gen_signature(params)
        params['sign'] = sign
        # print sign
        query = urllib.urlencode(params)
        # print query
        url = url_pattern.format(query=query)
        logging.info("MobvistaApi:net_get_country:url:%s" % url)
        r = requests.get(url)
        return r.json()

    def net_get_report(self, time_start=None, time_stop=None, page=1, limit=100,
                       fields=None, app_id=None, unit_id=None):
        '''
        :param time_start:start date (Format: YYYYmmdd) if empty, default as the previous day
        :param time_stop: end date (Format: YYYYmmdd) if empty, default as the previous day

        :param fields: fields can any of date,country,app_id,unit_id  , separated by comma
        :return: the json data
        '''
        inner_fields = self.convert_to_inner_fields(fields)
        url_pattern = 'http://oauth2.mobvista.com/m/report/offline_api_report?{query}'
        params = {
            'skey': self.skey,
            'time': int(time.time()),
            'v': self.version
        }
        if time_start:
            params['start'] = time_start
        if time_stop:
            params['end'] = time_stop
        if page < 1:
            page = 1
        params['page'] = page
        if limit < 10:
            limit = 10
        elif limit > 200:
            limit = 200
        params['limit'] = limit
        group_by_list = []
        if app_id:
            params['app_id'] = app_id
            group_by_list.append('app_id')
        if unit_id:
            params['unit_id'] = unit_id
            group_by_list.append('unit_id')
        if inner_fields and isinstance(inner_fields, (list, tuple)):
            for f in inner_fields:
                if f in self.field_list:
                    group_by_list.append(f)
        group_by_list = self.unique_list_item(group_by_list)
        if group_by_list:
            group_by = ','.join(group_by_list)
            params['group_by'] = group_by

        # print "params:%s" % params
        sign = self.gen_signature(params)
        params['sign'] = sign
        query = urllib.urlencode(params)
        url = url_pattern.format(query=query)
        logging.info("MobvistaApi:net_get_report:url:%s" % url)
        r = requests.get(url)
        return r.json()

    @staticmethod
    def unique_list_item(my_list):
        tmp_set = set()
        tmp_set_add = tmp_set.add
        for g in my_list:
            tmp_set_add(g)
        return list(tmp_set)


# ---- debug -----


if __name__ == '__main__':
    import config

    mobvista_account_list = config.mobvista_account_list
    skey = mobvista_account_list['mobvista_182c_6e2b']['skey']
    secret = mobvista_account_list['mobvista_182c_6e2b']['secret']
    api = MobvistaApi(skey, secret)

    # print api.net_get_country()
    fields_list = ['app_id', 'unit_id', 'date', 'country']
    # fields_list = ['country']
    fields_list = ["account", "date", "revenue", "request", "filled", "impression", "click", "country", "placement",
                       "filled_rate"]
    print  api.convert_to_inner_fields(fields_list)
    my_list = api.fetch_report('20170228', '20170228', fields=fields_list)
    # my_list=api.net_get_report('20170115', '20170115', fields=fields_list)
    # print json.dumps(my_list, indent=4, ensure_ascii=False)
    print my_list
    print "len:%s" % len(my_list)
