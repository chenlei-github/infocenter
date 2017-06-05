#! /usr/bin/env python
# -*- coding:utf-8 -*-
# author: Tiger <DropFan@Gmail.com>

from logging import CRITICAL  # 50
from logging import ERROR  # 40
from logging import WARNING  # 30
from logging import INFO  # 20
from logging import DEBUG  # 10
from logging import NOTSET  # 0
import logging
import json
import urllib2
import email_utils


class LogHelper(object):
    """docstring for LogHelper"""

    config = {
        'filename': 'log.log',
        'filemode': 'a',
        'encoding': None,
        'format': '%(asctime)s %(name)s %(levelname)-8s | %(message)s',
        'datefmt': '%Y-%m-%d %H:%M:%S',
        'level': INFO,
        'mailto': 'admin@test.com',
        'mailtoken': ''
    }

    def __init__(self, config):
        super(LogHelper, self).__init__()

        if config and isinstance(config, dict):
            self.setConfig(config)
        c = self.config

        logging.basicConfig(
            filename=c['filename'],
            filemode=c['filemode'],
            format=c['format'],
            datefmt=c['datefmt'],
            level=c['level'])
        self.setLogger()

    def setConfig(self, config):
        for k in config.keys():
            self.config[k] = config[k]

    def setLogger(self, loggerName=''):
        self.logger = logging.getLogger(loggerName)
        self.logger.setLevel(self.config['level'])

    def getLogger(self, loggerName=''):
        return logging.getLogger(loggerName)

    def setLevel(self, level=None):
        return self.logger.setLevel(level)

    def getLevel(self):
        return self.config['level']

    def log(self, level=None, msg=''):
        if not level:
            level = self.getLevel()
        return self.logger.log(level, msg)

    def critical(self, msg):
        # self.notifyToMail(msg)
        self.notifyToMailNew(msg)
        return self.log(CRITICAL, msg)

    def crit(self, msg):
        return self.critical(msg)

    def error(self, msg):
        return self.log(ERROR, msg)

    def err(self, msg):
        return self.error(msg)

    def warning(self, msg):
        return self.log(WARNING, msg)

    def info(self, msg):
        return self.log(INFO, msg)

    def debug(self, msg):
        return self.log(DEBUG, msg)

    def notset(self, msg):
        return self.log(NOTSET, msg)

    def setConsoleHandler(self, logger=None):
        console = logging.StreamHandler()
        console.setLevel(self.config['level'])
        console_fmt = logging.Formatter(
            fmt=self.config['format'], datefmt=self.config['datefmt'])
        console.setFormatter(console_fmt)
        if logger:
            return logger.addHandler(console)
        else:
            return self.logger.addHandler(console)

    def setHandler(self):
        pass

    def notifyToMail(self, msg):
        # print 'noticyToMail %s' % self.config['mailto']
        data = {
            'to_emails': self.config['mailto'],
            'subject': 'ALERT ! ' + msg[:100],
            'content': msg,
            'token': self.config['mailtoken']
        }
        req = urllib2.Request('http://mail.amberweather.com/api/am.php')
        req.add_header('Content-Type', 'application/json')
        try:
            response = urllib2.urlopen(req, json.dumps(data))

            return response
        except urllib2.HTTPError, e:
            self.error('notifyToMail HTTPError: %s' % e)
        except Exception, e:
            self.error('notifyToMail exception: %s' % e)
        return None

    def notifyToMailNew(self, msg):
        auth_config = {
            'smtp_server_host': 'mail.amberweather.com',
            'smtp_server_port': '25',
            'user': 'python',
            'password': 'python&mail',
            'sender': 'root@mail.amberweather.com'
        }
        to_emails = self.config.get('mailto', '')
        subject = 'ALERT ! ' + msg.split(']')[0] + ']'
        content = msg
        recv_list = to_emails.split(',')
        cnt = email_utils.sendmail(auth_config, recv_list, subject, content)
        # print "send:%s" % cnt


import config

log_file = config.log_config
log = LogHelper(log_file)


