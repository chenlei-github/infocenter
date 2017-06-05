#! /usr/bin/env python2
# -*- coding:utf-8 -*-

import urllib
import sys
import random
import requests
from ez_dump import EzDumper

reload(sys)
sys.setdefaultencoding('utf-8')


def get_associate_word_for(ez_dump, country, lang, word, proxy_list=None):
    if not proxy_list:
        proxies = None
    else:
        if not isinstance(proxy_list, (list, tuple, set)):
            proxy_list = [proxy_list]
        _p = random.choice(proxy_list)
        proxies = {
            'https': _p,
            'http': _p,
        }

    if isinstance(word, unicode):
        word = word.encode('utf-8')
    word = urllib.quote_plus(word, safe='+.')
    if country:
        url = "https://market.android.com/suggest/SuggRequest?json=1&c=3&gl=%s&hl=%s&query=%s" % (country, lang, word)
    else:
        url = "https://market.android.com/suggest/SuggRequest?json=1&c=3&hl=%s&query=%s" % (lang, word)
    ez_dump.dump(None, "url:%s" % url)

    results = []
    try:
        headers = {
            'User-Agent': "Mozilla/5.0 (Windows NT 10.0; WOW64) "
                          "AppleWebKit/537.36 (KHTML, like Gecko) "
                          "Chrome/49.0.2623.87 Safari/537.36",
            'Pragma': 'No-Cache',
            'Expires': '0'
        }
        ca_certs = '/etc/pki/tls/certs/ca-bundle.trust.crt'
        r = requests.get(url, headers=headers, proxies=proxies, verify=ca_certs)
        if not r:
            return []
        js = r.json()
        for j in js:
            word = j.get('s', None)
            if not word:
                continue
            results.append(word)
    except Exception as e:
        ez_dump.dump(None, "[Exception]get_associate_word_for:[%s]:[%s]" % (word, e))
    finally:
        return results


def worker(word_list, ez_dump, country='US', lang='en', reversed_suffix=False, proxies=None):
    if isinstance(word_list, (tuple, list, set)):
        if reversed_suffix:
            word_list = reversed(word_list)
        word = ''.join(word_list).encode('utf-8')
    else:
        word = word_list.encode('utf-8')

    new_word_list = []
    try:
        ez_dump.dump(None, "KEYWORD:[%s]" % word)
        new_word_list = get_associate_word_for(ez_dump, country, lang, word, proxies)
    except Exception as e:
        ez_dump.dump(None, "[Exception]Get Associate Word:[%s]" % e)

    if not new_word_list:
        ez_dump.dump(None, "[ FAIL  ]Get Associate Word:(%s)" % ([country, lang, word]))
    else:
        ez_dump.dump(None, "[SUCCESS]Get Associate Word:\n", new_word_list)
    return new_word_list


def compute_list_size(d2_list):
    if not isinstance(d2_list, (list, tuple, set)):
        return 1
    total = 1
    for m in d2_list:
        if not m or not isinstance(m, (list, tuple, set)):
            print "continue", m
            continue
        total *= len(m)
    return total


def main(task, output_file):
    proxy_list = task['proxy_list']
    word_list = task['word_list']
    country = task['country']
    lang = task['lang']
    suffix_1st_list = task['suffix_1st_list']
    suffix_2nd_list = task['suffix_2nd_list']
    suffix_3rd_list = task['suffix_3rd_list']
    reversed_suffix = task['reversed']

    long_word_list = [word_list, suffix_1st_list, suffix_2nd_list, suffix_3rd_list]

    total = compute_list_size(long_word_list)

    level_1_size = compute_list_size(long_word_list[1:])
    level_2_size = compute_list_size(long_word_list[2:])
    level_3_size = compute_list_size(long_word_list[3:])

    print total, level_1_size, level_2_size, level_3_size
    # exit()

    ez_dump = EzDumper(output_file, total, '%s : suffix' % task.get('task_tag', ''))

    for i, word in enumerate(word_list):
        current = i * level_1_size
        input_word = [word]
        ez_dump.dump(current, "Level 1:%s" % input_word)
        if not worker(input_word, ez_dump, country, lang, reversed_suffix, proxy_list):
            ez_dump.dump(None, "Level 1 continue")
            continue

        for j, suffix_1st in enumerate(suffix_1st_list):
            current = i * level_1_size + j * level_2_size
            input_word = [word, suffix_1st]
            ez_dump.dump(current, "Level 2:%s" % input_word)
            if not worker(input_word, ez_dump, country, lang, reversed_suffix, proxy_list):
                ez_dump.dump(None, "Level 2 continue")
                continue

            for k, suffix_2nd in enumerate(suffix_2nd_list):
                current = i * level_1_size + j * level_2_size + k * level_3_size
                input_word = [word, suffix_1st, suffix_2nd]
                ez_dump.dump(current, "Level 3:%s" % input_word)
                if not worker(input_word, ez_dump, country, lang, reversed_suffix, proxy_list):
                    ez_dump.dump(None, "Level 3 continue")
                    continue

                for m, suffix_3rd in enumerate(suffix_3rd_list):
                    current = i * level_1_size + j * level_2_size + k * level_3_size + m + 1
                    input_word = [word, suffix_1st, suffix_2nd, suffix_3rd]
                    ez_dump.dump(current, "Level 4:%s" % input_word)
                    if not worker(input_word, ez_dump, country, lang, reversed_suffix, proxy_list):
                        ez_dump.dump(None, "Level 4 continue")
                        continue

    ez_dump.dump(total, 'Done!')


if __name__ == "__main__":
    task = {'keywords_method': 'suffix', 'reversed': False, 'suffix_3rd_list': set(['3c', '3b', '3a', '4d', '']),
            'lang': 'en', 'suffix_2nd_list': set(['', '2a', '2b', '2c']), 'suffix_1st_list': set(['1a', '', '1b']),
            'level': 2, 'country': 'US', 'do_top_downloads': False, 'proxy_list': ['http://127.0.0.1:8118'],
            'word_list': ['weather', 'widget']}
    main(task, 'suffix.txt')
