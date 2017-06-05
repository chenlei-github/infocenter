#! /usr/bin/env python2
# -*- coding:utf-8 -*-

import os
import config
import time
from collections import deque
from config import *
from googleplay import GooglePlayAPI
import socket
import warnings
import re
import codecs
import json
import sys
import datetime

reload(sys)
sys.setdefaultencoding('utf-8')

warnings.filterwarnings("ignore")

socket.setdefaulttimeout(5)


class FailCounter(object):
    def __init__(self, size):
        self.deque = deque([], size)

    def append(self, val):
        if val:
            self.deque.append(True)
        else:            self.deque.append(False)

    def fail_rate(self):
        if self.deque.__len__() == 0:
            return 0
        else:
            return 1.0 * self.deque.count(False) / self.deque.maxlen

    def reset(self):
        self.deque.clear()


class ProxyIterator(object):
    def __init__(self, proxy_list):
        if isinstance(proxy_list, (list, tuple, set)):
            self.proxy_list = proxy_list
        else:
            self.proxy_list = []
        self.proxy_index = 0

    def next(self):
        if not self.proxy_list:
            return
        if self.proxy_index > len(self.proxy_list) - 1:
            self.proxy_index = 0
        p = self.proxy_list[self.proxy_index]
        os.environ['http_proxy'] = p
        os.environ['https_proxy'] = p
        print "Change Proxy:%s" % p
        self.proxy_index += 1

    def move_to_first(self):
        self.proxy_index = 0
        self.next()


def file_len(file_name):
    i = 0
    with open(file_name) as f:
        for line in f:
            if line.strip():
                i += 1
    return i


def list_top_downloads(request, api, out_file, progress):
    try:

        desc_str = ''
        res = api.details(request)
        dr = re.compile(r'<[^>]+>',re.S)
        desc_str = dr.sub('', res.docV2.descriptionHtml)
        print '[%s] 描述数据-获取start...\n' % (request)

        review_str = ''
        for offset in xrange(5):
            print '     [%s] 第%s页 评论数据-获取start...\n' % (request, offset)
            res = api.reviews(request, False, 1, 20, offset*20)
            review_list = res.getResponse.review
            for i in xrange(len(review_list)):
                review_str += review_list[i].comment + '\n'

    except Exception as e:
        print '[%s/%s]--(%s) 数据获取失败\n' % (progress['current'], progress['total'], request)
        return False

    if desc_str == '' or review_str=='':
        print '[%s/%s] Google Play未查询到(%s)...\n' % (progress['current'], progress['total'], request)
        return False

    with codecs.open(out_file,'a','utf-8') as f:
         f.write('==============\n')
         f.write(request)
         f.write('\n== description:\n')
         f.write(desc_str)
         f.write('\n== reviews\n')
         f.write(review_str)
    print '[%s/%s]--(%s) 数据查询完成...\n' % (progress['current'], progress['total'], request)
    return True


def main(input_file, out_dir):
    total = file_len(input_file)
    fail_counter = FailCounter(50)
    proxy_iterator = ProxyIterator(config.proxy_list)
    proxy_iterator.next()
    file_object = open(input_file)
    try:
         input_text = file_object.read()
    finally:
         file_object.close()

    try:
        data = json.loads(input_text)
        assert data[0]['hl']
    except Exception:
        print '读取json输入文件[ %s ]出错...\n' % (input_file)
        exit(0)

    lang = data[0]['hl']
    api = GooglePlayAPI(ANDROID_ID, lang)
    api.login(GOOGLE_LOGIN, GOOGLE_PASSWORD, AUTH_TOKEN)

    now = datetime.datetime.now()
    out_file = '%s%s_reviewDesc-%s.txt' % (out_dir,
        lang,
        now.strftime('%Y%m%d-%H:%M:%S'))

    progress = {'total' : len(data), 'current' : 0}
    for i in xrange(len(data)):
        progress['current'] = progress['current'] + 1
        request = data[i]['app_name'].strip()
        print '[ %s ] starting...' % (request)
        if not request:
            continue

        for i in xrange(len(config.proxy_list)):
            stat = list_top_downloads(request, api, out_file, progress)
            if not stat:
                print '切换代理重试中...'
                fail_counter.append(False)
                proxy_iterator.next()
                if fail_counter.fail_rate() > 0.7:
                    time.sleep(60)
                    fail_counter.reset()
                    proxy_iterator.move_to_first()
                    api = GooglePlayAPI(ANDROID_ID, lang=lang)
                    api.login(GOOGLE_LOGIN, GOOGLE_PASSWORD, AUTH_TOKEN)
            else:
                fail_counter.append(True)
                break


if __name__ == '__main__':
    _dir = config.config_dir
    main(_dir['input_file'], _dir['output_dir'])
