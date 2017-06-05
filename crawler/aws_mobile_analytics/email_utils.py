#! /usr/bin/env python
# -*- coding:utf-8 -*-

import smtplib
from email.mime.text import MIMEText
from email.header import Header


def sendmail(auth_config, recv_list, subject, mail_content):
    count_success = 0

    smtp_server_host = auth_config['smtp_server_host']
    smtp_server_port = auth_config['smtp_server_port']
    user = auth_config['user']
    passwd = auth_config['password']
    default_sender = "root@amberweather.com"
    sender = auth_config.get('sender', default_sender)
    content_type = auth_config.get('content_type', 'plain')

    message = MIMEText(mail_content, content_type, 'utf-8')
    message['From'] = Header(sender, 'utf-8')
    message['Subject'] = Header(subject, 'utf-8')

    for recv in recv_list:
        message['To'] = Header(recv, 'utf-8')
        try:
            mailserver = smtplib.SMTP(smtp_server_host, smtp_server_port)
            mailserver.starttls()
            mailserver.login(user, passwd)
            mailserver.sendmail(sender, recv, message.as_string())
            count_success += 1
        except smtplib.SMTPException:
            pass
    return count_success


if __name__ == "__main__":
    auth_config = {
        'smtp_server_host': 'smtp_server_host',
        'smtp_server_port': '25',
        'user': 'user',
        'password': 'password',
        'sender': 'machine@mail.amberweather.com'
    }
    to_list = ['abc@abc.com', 'abc@abcabc.com']
    subject = "this is new test from python"
    mail_content = '''by TLS;http://www.abc.com'''
    cnt = sendmail(auth_config, to_list, subject, mail_content)
    print "success:%s" % cnt
