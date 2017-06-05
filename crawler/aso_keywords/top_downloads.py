#! /usr/bin/env python2
# -*- coding:utf-8 -*-

import os
import config
import time
from collections import deque
from config import *
from googleplay import GooglePlayAPI
from ez_dump import EzDumper
import socket
import warnings

warnings.filterwarnings("ignore")

socket.setdefaulttimeout(5)


class FailCounter(object):
    def __init__(self, size):
        self.deque = deque([], size)

    def append(self, val):
        if val:
            self.deque.append(True)
        else:
            self.deque.append(False)

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


def list_top_downloads(request, ez_dump, api, nb_res=10, offset=None):
    try:
        message = api.search(request, nb_res, offset)
    except Exception as e:
        ez_dump.dump(None, "[Exception]list_top_downloads():[%s]" % e)
        return False

    if len(message.doc) > 0:
        doc = message.doc[0]
        top_downloads = []
        for c in doc.child:
            top_downloads.append(c.details.appDetails.numDownloads.replace(",", ""))
        ez_dump.dump(None, '[SUCCESS]',
                     "%s,%s" % (request, ",".join(unicode(i).encode('utf8') for i in top_downloads))
                     )
        return True
    else:
        ez_dump.dump(None, "[ FAIL ]%s" % request)
        return False


def main(lang, input_file, out_file, task_tag=''):
    total = file_len(input_file)
    ez_dump = EzDumper(out_file, total, '%s : download_num' % task_tag)

    fail_counter = FailCounter(50)
    proxy_iterator = ProxyIterator(config.proxy_list)
    proxy_iterator.next()

    api = GooglePlayAPI(ANDROID_ID, lang=lang)
    api.login(GOOGLE_LOGIN, GOOGLE_PASSWORD, AUTH_TOKEN)

    with open(input_file, 'r') as f:
        current = 0
        for line in f:
            current += 1
            request = line.strip()
            if not request:
                continue
            ez_dump.dump(current, "KEYWORD:[%s]" % request)

            for i in xrange(len(config.proxy_list)):
                stat = list_top_downloads(request, ez_dump, api)
                if stat:
                    fail_counter.append(True)
                    break
                else:
                    fail_counter.append(False)
                    ez_dump.dump(None, "fail_rate:[%s]" % fail_counter.fail_rate())
                    proxy_iterator.next()
                    if fail_counter.fail_rate() > 0.7:
                        ez_dump.dump(None, 'sleep for %s seconds ...' % 60)
                        time.sleep(60)
                        fail_counter.reset()
                        proxy_iterator.move_to_first()
                        api = GooglePlayAPI(ANDROID_ID, lang=lang)
                        api.login(GOOGLE_LOGIN, GOOGLE_PASSWORD, AUTH_TOKEN)


if __name__ == '__main__':
    # main('en', )
    # print file_len('input.txt')
    main('en', 'keyword.txt', 'keyword.out.txt')
