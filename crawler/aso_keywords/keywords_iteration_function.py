#! /usr/bin/env python2
# -*- coding:utf-8 -*-

from keywords_suffix_function import get_associate_word_for
from ez_dump import EzDumper


def step_level(word_list, level, max_level, ez_dump, country='US', lang='en', proxy_list=None):
    if level > max_level:
        return

    new_word_list = []
    for i, word in enumerate(word_list):
        current = 5 ** (level - 1) / 4 + 1 + i
        ez_dump.dump(current, "KEYWORD:[%s]" % word)

        word_list = get_associate_word_for(ez_dump, country, lang, word, proxy_list)
        if word_list:
            ez_dump.dump(None, "[SUCCESS]Get Associate Word", word_list)
        else:
            ez_dump.dump(None, "[ FAIL  ]Get Associate Word(%s)" % ([country, lang, word]))
        new_word_list += word_list
    step_level(new_word_list, level + 1, max_level, ez_dump, country, lang, proxy_list)


def main(task, output_file):
    lang = task['lang']
    country = task['country']
    max_level = task['level']
    word_list = task['word_list']
    proxy_list = task['proxy_list']

    if not word_list:
        print "Empty key words ,Exit."
        return
    total = len(word_list) * (5 ** max_level - 1) / 4
    ez_dump = EzDumper(output_file, total, '%s : iteration' % task.get('task_tag', ''))

    step_level(word_list, 1, max_level, ez_dump, country, lang, proxy_list)


if __name__ == "__main__":
    task = {'keywords_method': 'iteration', 'reversed': False, 'suffix_3rd_list': set(['3c', '3b', '3a', '4d', '']),
            'lang': 'en', 'suffix_2nd_list': set(['', '2a', '2b', '2c']), 'suffix_1st_list': set(['1a', '', '1b']),
            'level': 2, 'country': 'US', 'do_top_downloads': False, 'proxy_list': ['http://127.0.0.1:8118'],
            'word_list': ['weather']}
    main(task, 'iteration')
