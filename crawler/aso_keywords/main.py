#! /usr/bin/env python2
# -*- coding:utf-8 -*-

import os
import sys
import time
import copy
import csv
import re
import StringIO
import traceback

from xlsxwriter.workbook import Workbook

self_dir = os.path.dirname(os.path.abspath(__file__))
sys.path.append(self_dir)

import logging
import uuid

import config
# def fetch_keywords_via_suffix(task, out_file):
#     pass
from keywords_suffix_function import main as fetch_keywords_via_suffix

# def fetch_keywords_via_iteration(task, out_file):
#     pass
from keywords_iteration_function import main as fetch_keywords_via_iteration

# def fetch_top_downloads(lang, input_file, out_file):
#     pass
from top_downloads import main as fetch_top_downloads


def load_input(input_file):
    task_list = []
    _task_default = {
        'country': None,
        'lang': 'en',
        'keywords_method': '',
        'do_top_downloads': False,
        'level': 1,
        'suffix_1st_list': [' ', ],
        'suffix_2nd_list': [' ', ],
        'suffix_3rd_list': [' ', ],
        'reversed': False,
        'word_list': [],
        'task_name': '',
        'maillist': [],
    }
    _task = copy.deepcopy(_task_default)
    print _task
    with open(input_file, 'r') as f:
        for line in f:
            line = line.strip()
            if not line:
                continue
            if line.startswith('@--'):
                print "New task"
                task_list.append(_task)
                _task = copy.deepcopy(_task_default)
                print _task
            elif line.startswith('@'):
                line = line[1:]
                x_list = line.split(None, 1)
                if not x_list or len(x_list) < 2:
                    continue
                param = x_list[0]
                if param in ['lang', 'country', 'keywords_method', 'task_name']:
                    _task[param] = x_list[1]
                elif param in ['suffix_1st_list', 'suffix_2nd_list', 'suffix_3rd_list']:
                    val = x_list[1]
                    if ',' in val:
                        _task[param] = set(val.split(','))
                    else:
                        _task[param] = set([c for c in unicode(val, encoding='utf-8')])
                elif param in ['level']:
                    _task[param] = int(x_list[1])
                elif param in ['maillist']:
                    val = x_list[1]
                    if '@' not in val:
                        continue
                    mail_list = val.split(',')
                    _task[param] = [mail.strip() for mail in mail_list]
                elif param in ['reversed', 'do_top_downloads']:
                    val = x_list[1]
                    if val in ['true', 'True']:
                        _task[param] = True
                    else:
                        _task[param] = False
                else:
                    logging.warning("Not Support Lex `%s`" % line)
            elif line.startswith('#'):
                continue
            else:
                _task['word_list'].append(line)
        task_list.append(_task)
    return task_list


def csv_2_xls(input_file, output_file):
    workbook = Workbook(output_file)
    worksheet = workbook.add_worksheet()
    with open(input_file, 'rb') as f:
        reader = csv.reader(f)
        for r, row in enumerate(reader):
            for c, col in enumerate(row):
                worksheet.write(r, c, col)
    workbook.close()


def write_email(mail_input_file, task, results_file_list):
    with open(mail_input_file, 'w') as f:
        try:
            f.write('\n'.join(results_file_list))
        except Exception as e:
            print e
        f.write('\n')
        f.write('\n@task_name %s' % task.get('task_name', ''))
        f.write('\n@country %s' % task.get('country', ''))
        f.write('\n@lang %s' % task.get('lang', ''))
        f.write('\n@keywords_method %s' % task.get('keywords_method', ''))
        f.write('\n@do_top_downloads %s' % task.get('do_top_downloads', ''))
        try:
            f.write('\n@suffix_1st_list %s' % ','.join([str(m) for m in task.get('suffix_1st_list', [])]))
            f.write('\n@suffix_2nd_list %s' % ','.join([str(m) for m in task.get('suffix_2nd_list', [])]))
            f.write('\n@suffix_3rd_list %s' % ','.join([str(m) for m in task.get('suffix_3rd_list', [])]))
        except Exception as e:
            print e
        f.write('\n@level %s' % task.get('level', ''))
        f.write('\n@reversed %s' % task.get('reversed', ''))
        f.write('\n@maillist %s' % task.get('maillist', ''))
        f.write('\n')
        word_list = task.get('word_list', [])
        f.write('\n'.join([str(m) for m in word_list]))


def send_mail(mail_list, input_file):
    cmd = " cat %s | mail -s KEYWORDS_DONE %s "
    for mail in mail_list:
        if not mail.endswith('@amberweather.com'):
            continue
        print cmd % (input_file, mail)
        os.system(cmd % (input_file, mail))


def worker(task):
    task_conf = config.task_list
    website_base_url = task_conf['website_base_url']
    website_dir = task_conf['website_dir']
    task_id = uuid.uuid4().hex.replace('-', '')
    print task_id
    print task

    task_name = task['task_name']
    country = task['country']
    lang = task['lang']

    check_pattern = r'[^-_\w]+'

    new_input_file = "%s.%s.%s.%s_%s" % (
        time.strftime('%Y%m%d'),
        re.sub(check_pattern, '', task_name, re.UNICODE),
        task_id,
        re.sub(check_pattern, '', lang, re.UNICODE),
        re.sub(check_pattern, '', country, re.UNICODE)
    )

    mail_input_file = "%s/%s.%s.txt" % (website_dir, new_input_file, 'mail')
    tmp_input_file = "%s/%s.%s.txt" % (website_dir, new_input_file, 'tmp')
    suffix_output_file = "%s/%s.%s.txt" % (website_dir, new_input_file, 'suffix')
    iteration_output_file = "%s/%s.%s.txt" % (website_dir, new_input_file, 'iteration')
    top_download_output_file = "%s/%s.%s.txt" % (website_dir, new_input_file, 'download')

    results_file_list = ['']
    last_output_file = ''

    if task['keywords_method'] == 'suffix':
        task['proxy_list'] = config.suffix_method_proxy_list
        # print "@file %s/%s.%s.txt" % (website_base_url, new_input_file, 'suffix')
        results_file_list.append("%s/%s.%s.txt" % (website_base_url, new_input_file, 'suffix'))
        fetch_keywords_via_suffix(task, suffix_output_file)
        input_file = suffix_output_file
        last_output_file = suffix_output_file
    elif task['keywords_method'] == 'iteration':
        task['proxy_list'] = config.iteration_method_proxy_list
        # print "@file %s/%s.%s.txt" % (website_base_url, new_input_file, 'iteration')
        results_file_list.append("%s/%s.%s.txt" % (website_base_url, new_input_file, 'iteration'))
        fetch_keywords_via_iteration(task, iteration_output_file)
        input_file = iteration_output_file
        last_output_file = iteration_output_file
    else:
        with open(tmp_input_file, 'w')as f:
            word_list = task.get('word_list', [])
            f.write('\n'.join(word_list))
        input_file = tmp_input_file

    if not os.path.exists(input_file) or os.path.getsize(input_file) < 5:
        print "[%s] FAIL :Get Associate Word." % task['task_name']
        return False

    if task['do_top_downloads']:
        # print "@file %s/%s.%s.txt" % (website_base_url, new_input_file, 'download')
        results_file_list.append("%s/%s.%s.txt" % (website_base_url, new_input_file, 'download'))
        if country:
            lang = '%s_%s' % (lang, country)
        fetch_top_downloads(lang, input_file, top_download_output_file, task.get('task_tag', ''))
        last_output_file = top_download_output_file

        if not os.path.exists(top_download_output_file):
            print "[%s] FAIL :Get Top Download." % task['task_name']
            return False

    # report results
    if os.path.exists(last_output_file):
        xls_file = last_output_file + '.xls'
        csv_2_xls(last_output_file, xls_file)

    last_result_file = results_file_list[-1]
    if last_result_file:
        last_result_file += '.xls'
    print "@file %s" % last_result_file
    write_email(mail_input_file, task, [last_result_file])
    mail_list = task.get('maillist', [])
    if mail_list:
        send_mail(list(set(mail_list)), mail_input_file)

    print "[%s]Send Mail Done!" % task['task_name']
    print "[%s]Done!" % task['task_name']
    return True


def main():
    task_conf = config.task_list
    website_dir = task_conf['website_dir']
    origin_input_file = task_conf['input_file']
    input_file = "%s/%s" % (website_dir, origin_input_file)
    mail_input_file = "%s.email.txt" % input_file
    task_list = load_input(input_file)
    print task_list
    task_num = len(task_list)
    for i, task in enumerate(task_list):
        print task
        task['task_tag'] = '%s / %s : %s' % (i + 1, task_num, task.get('task_name', ''))
        stat = False
        try:
            stat = worker(task)
        except Exception as e:
            print 'Exception:', e
        finally:
            if not stat:
                buff = StringIO.StringIO()
                traceback.print_exc(None, file=buff)
                error_msg = buff.getvalue()
                print "[%s] Fail!" % task['task_name']
                print error_msg
                write_email(mail_input_file, task, ['Fail', error_msg])
                mail_list = task.get('maillist', [])
                send_mail(mail_list, mail_input_file)
            else:
                print "[%s] Success!" % task['task_name']
    os.rename(input_file, '%s_%s' % (input_file, time.strftime('%Y%m%d_%H%M%S')))


if __name__ == '__main__':
    try:
        main()
    except Exception as e:
        print e
    print "All Task Done!"

    # task_list = load_input('input.txt')
    # print task_list
    # file_list = ['file_a', 'file_b']
    # print file_list
    # file_name = 'mail.txt'
    # write_email(file_name, task_list[0], file_list)

    # csv_2_xls('a.py.csv', 'input.xls')
