#!/usr/bin/python
# -*- coding: utf-8 -*-
import sys
import json


def convert_cookie_to_dict(cookie):
    ret_dict = {}
    items = cookie.split(";")
    for item in items:
        key, value = item.split("=", 1)
        key = key.strip()
        value = value.strip()
        ret_dict[key] = value
    return ret_dict


if __name__ == '__main__':
    if len(sys.argv) < 2:
        print "Usage:%s ' cookie '" % sys.argv[0]
        exit(1)
    cookies = convert_cookie_to_dict(sys.argv[1])
    print json.dumps(cookies, indent=4, sort_keys=True, ensure_ascii=False).encode('utf-8')
