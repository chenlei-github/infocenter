#! /usr/bin/env python2
# -*- coding:utf-8 -*-

import requests


def check(proxy):
    success = False
    proxies = {
        'https': proxy,
        'http': proxy
    }
    try:
        headers = {
            "cache-control": "no-cache",
            "pragma": "no-cache",
            "user-agent": "Mozilla/5.0 (X11; Linux x86_64) "
                          "AppleWebKit/537.36 (KHTML, like Gecko) "
                          "Chrome/57.0.2987.98 Safari/537.36"
        }
        r = requests.get('https://www.google.co.jp/', headers=headers, proxies=proxies)
        print "code:", r.status_code
        if r.status_code == 200:
            success = True
        else:
            success = False
    except Exception as e:
        print e
        success = False
    finally:
        return success


def main(proxy_list):
    good_list = []
    bad_list = []
    for p in proxy_list:
        if check(p):
            good_list.append(p)
        else:
            bad_list.append(p)
    return good_list, bad_list


if __name__ == '__main__':
    _proxy_list = [
        '',
        # 'http://127.0.0.1:8118',
        'http://172.31.6.22:8118',
        'http://172.31.17.241:8118',
        'http://172.31.6.22:8118',
        'http://172.31.26.127:8118',
        'http://172.31.20.188:8118',
        'http://172.31.25.43:8118',
        'http://172.31.18.132:8118',
        'http://172.31.22.215:8118',
        'http://172.31.16.212:8118',
        'http://172.31.16.211:8118',
        'http://172.31.31.95:8118',
        'http://172.31.31.96:8118',
        'http://172.31.31.187:8118',
    ]

    good, bad = main(_proxy_list)
    print "Good:", good
    print "Bad:", bad
