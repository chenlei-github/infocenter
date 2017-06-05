#! /usr/bin/env python
# -*- coding:utf-8 -*-
import json
import os
import time
from collections import deque
import xlrd
import sys
import hashlib
import utils
from ez_dump import EzDumper
import socket
import warnings
import config
from GooglePlaySearchAPI import GooglePlaySearchAPI

warnings.filterwarnings("ignore")

socket.setdefaulttimeout(5)
reload(sys)
sys.setdefaultencoding('utf-8')


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
        return {
            'http': p,
            'https': p
        }

    def move_to_first(self):
        self.proxy_index = 0
        self.next()


def file_len(file_name):
    i = 0
    with open(file_name, 'r') as f:
        for line in f:
            if line.strip():
                i += 1
    return i


def worker(proxy, search, country, lang, dtime, task_type='', keyword_id=0, exec_type=0):
    success = False
    sql_list = []
    table_name = config.result_data_tb
    field_list = ['exec_type', 'date', 'type', 'country', 'lang', 'search',
                  'package', 'developer', 'developer_name', 'rank', 'hash']
    score_field = ['date', 'search', 'lang', 'country', 'score']

    try:
        api = GooglePlaySearchAPI(proxy)
        data = api.search(search, lang, country)
        score = 0
        for i, v in enumerate(data):
            rank = i + 1
            developer_name = v.get('developer_name', '')[0:100].replace("'", "''")
            v['keyword_id'] = keyword_id
            v['exec_type'] = exec_type
            v['country'] = country
            v['lang'] = lang
            v['date'] = dtime
            v['type'] = task_type[0:100]
            v['search'] = search[0:100].replace("'", "''")
            v['rank'] = rank
            v['package'] = v.get('package', '')[0:100].replace("'", "''")
            v['developer'] = v.get('developer', '')[0:100].replace("'", "''")
            v['developer_name'] = developer_name
            v['hash'] = hashlib.sha1(json.dumps(v, ensure_ascii=False).encode('utf-8')).hexdigest()

            if i < 10 and developer_name in config.my_developer:
                score += config.rank_score.get(str(rank), 0)

            if i >= 20:
                break
            sql = utils.mysql_gen_insert_sql(table_name, v, field_list, {})
            sql_list.append(sql)

        if not sql_list:
            score = -1
            success = False
        else:
            success = True

        print "KEYWORD: [%s]  SCORE:[%s]" % (search, score)
        row = {
            'date': dtime,
            'search': search[0:100].replace("'", "''"),
            'lang': lang,
            'country': country,
            'score': str(score)
        }
        score_sql = utils.mysql_gen_insert_sql(config.keyword_score_tb, row, score_field, {})
        sql_list.append(score_sql)
    except Exception as e:
        print e
        success = False
    finally:
        return success, sql_list


def main(dtime, country, lang, search_list, out_file, task_type='', tag='', keyword_id=0, exec_type=0):
    total = len(search_list)
    ez_dump = EzDumper(out_file, total, '%s : %s : %s : developer_rank' % (tag, country, lang))

    fail_counter = FailCounter(50)
    proxy_iterator = ProxyIterator(config.proxy_list)
    proxy = proxy_iterator.next()

    current = 0
    for line in search_list:
        request = line.strip()
        if not request:
            continue
        current += 1
        ez_dump.dump(current, "KEYWORD:[%s]" % request)

        for i in xrange(len(config.proxy_list)):
            stat, ret_sql = worker(proxy, request, country, lang, dtime, task_type, keyword_id, exec_type)
            # print stat
            ez_dump.dump(None, "Count:%s" % len(ret_sql))
            if stat or (ret_sql and i == len(config.proxy_list) - 1):
                with open(out_file, 'a') as f:
                    f.write('\n'.join(ret_sql))
                    f.write('\n')
                fail_counter.append(True)
                break
            else:
                fail_counter.append(False)
                ez_dump.dump(None, "fail_rate:[%s]" % fail_counter.fail_rate())
                proxy = proxy_iterator.next()
                if fail_counter.fail_rate() > 0.7:
                    ez_dump.dump(None, 'sleep for %s seconds ...' % 60)
                    time.sleep(60)
                    fail_counter.reset()
                    proxy_iterator.move_to_first()
                    proxy = proxy_iterator.next()


def load_xls(xls_file):
    country_lang_list = []
    search_list = []
    xl = xlrd.open_workbook(xls_file)
    sheet = xl.sheets()[0]
    nrows = sheet.nrows
    for r in xrange(nrows):
        row = sheet.row_values(r)
        new_row = []
        for m in row:
            if isinstance(m, unicode):
                new_row.append(m.encode('utf-8'))
            else:
                new_row.append(m)
        row = new_row
        print row
        print row[0]
        if row[0] == '@':
            country = row[1]
            lang = row[2]
            if not country.isalpha() or not lang.isalpha():
                print 'Bad Param Country,Lang :', row
                exit(1)
            if country == '':
                country = None
            country_lang_list.append({
                'country': country.lower(),
                'lang': lang.lower()
            })
        elif row[0]:
            if row[0] == '关键词':
                print "SKip 关键词"
                continue
            search_list.append(row[0])
    return country_lang_list, search_list


def read_txt(txt_file):
    result = []
    fd = file(txt_file, "r")
    for line in fd.readlines():
        result.append(str(line).replace("\n", ""))
    return result


def eachFile(keyword_path):
    data = {}
    pathDir = os.listdir(keyword_path)
    for fileName in pathDir:
        file_path = os.path.join('%s/%s' % (keyword_path, fileName))
        txt_list = read_txt(file_path.decode('gbk'))
        data.update({str(fileName)[:-4]: txt_list})
    return data


def load_sql_into_mysql(sql_file):
    if not os.path.exists(sql_file):
        print "ERROR: load_sql_into_mysql:[%s]" % (sql_file)
        return False
    db_config = config.mysql_config
    utils.execute_mysql_sql(
        db_config['host'],
        db_config['user'],
        db_config['passwd'],
        db_config['dbname'],
        sql_file)


def exec_main(data, out_sql_path, exec_type):
    print "Start..."
    total = len(data)
    current_num = 0
    dtime = time.strftime("%Y-%m-%d %H:%M:%S")
    sql_file = '%s/out_%s.sql' % (out_sql_path, exec_type)
    if os.path.exists(sql_file):
        os.remove(sql_file)

    for key in data.keys():
        res = key.split('_', 2)
        country, lang, task_type = res
        search_list = data[key]
        tag = '%s/%s]' % (current_num, total)
        current_num = current_num + 1
        main(dtime, country, lang, search_list, sql_file, task_type, tag, 0, exec_type)
    print "Fetch Data Done!"
    print "load sql to database ..."
    load_sql_into_mysql(sql_file)
    print "All Done!"


def exec_by_manual(keyword_path, out_sql_path):
    data = eachFile(keyword_path)
    exec_main(data, out_sql_path, 1)


def exec_by_auto(out_sql_path):
    sql = 'SELECT search,lang,country,type FROM `gp_keyword`'
    res = utils.query_sql(sql)
    data = {}
    for i, row in enumerate(res):
        _country, _lang, _search, _type = row[2], row[1], row[0], row[3]
        key = '%s_%s_%s' % (_country, _lang, _type)
        search_list = data.get(key, [])
        search_list.append(_search)
        data.update({key: search_list})
    exec_main(data, out_sql_path, 2)


# '''
# 使用示例：
# python main.py 加任意参数代表自动运行，不加代表手动运行

# 手动运行：gp_developer_rank 表字段exec_type会标记为1
# 自动运行：gp_developer_rank 表字段exec_type会标记为2
# '''
if __name__ == '__main__':
    _config = config.keyword_config.get('dir')
    if len(sys.argv) > 1:
        print 'auto start.......'
        exec_by_auto(_config['out_sql'])
    else:
        exec_by_manual(_config['keyword_list'], _config['out_sql'])
