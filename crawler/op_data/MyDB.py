#!/usr/bin/python
# -*- coding:utf-8 -*-


import MySQLdb
import logging
import toolkit
import MySQLdb.constants.CLIENT


class MyDB(object):
    def __init__(self, host='127.0.0.1', port=3306, user=None, passwd=None, db=None, compress=False):
        self.mysql_config = {
            "host": host,
            "port": port,
            "user": user,
            "passwd": passwd,
            "db": db,
            "dbname": db,
            "compress": compress,

        }
        self.status = True
        self.message = []
        self.total_count = 0
        self.success_count = 0
        self.batch_size = 0
        self.connection = None
        self.open_connection()

    def set_batch_size(self, size):
        self.batch_size = size

    def open_connection(self):
        if not self.connection:
            self.close_connection()
        try:
            self.connection = MySQLdb.connect(host=self.mysql_config['host'],
                                              port=self.mysql_config['port'],
                                              user=self.mysql_config['user'],
                                              passwd=self.mysql_config['passwd'],
                                              client_flag=MySQLdb.constants.CLIENT.MULTI_STATEMENTS,
                                              db=self.mysql_config['db'])
        except Exception as e:
            self.report_error("[MyDB]:mysql_open_connection:%s" % e)
        finally:
            pass

    def close_connection(self):
        success = False
        try:
            self.connection.close()
            success = True
        except Exception as e:
            self.report_error("[%s]:mysql_close_connection:%s" % e)
        finally:
            return success

    def report_error(self, msg):
        self.status = False
        if not isinstance(self.message, list):
            self.message = []
        self.message.append(msg)
        logging.error(msg)

    def save(self, table, data, field_list, unique_key):
        model_list = None

        if isinstance(data, dict):
            model_list = [data]
        elif isinstance(data, (list, tuple)):
            model_list = data
        else:
            return 0

        sql_list = []
        for model in model_list:
            self.total_count += 1
            sql = self.gen_save_sql(table, model, field_list, unique_key)
            sql_list.append(sql)
        big_sql = " ".join(sql_list)
        print "big_sql", big_sql

        # conn = toolkit.mysql_open_connection(self.mysql_config)
        # count = toolkit.mysql_execute(conn, big_sql)
        # toolkit.mysql_close_connection(conn)
        count = self.execute(big_sql)
        self.success_count += count
        print "count", count
        return count

    def gen_save_sql(self, table, model, field_list, unique_key):

        if not table or not isinstance(table, str):
            self.report_error("save_one().table[%s] should be str()" % table)
            return None
        if not model or not isinstance(model, dict):
            self.report_error("save_one().data[%s] should be dict()" % model)
            return None
        if not field_list or not isinstance(field_list, (list, tuple)):
            self.report_error("save_one().field_list[%s] should be list()" % field_list)
            return None
        if not unique_key or not isinstance(unique_key, (list, tuple)):
            self.report_error("save_one().unique_key[%s] should be list()" % unique_key)
            return None

        # 拼接 insert 从句
        column_sql = "  "
        value_sql = "  "
        for field in field_list:
            if field not in model.keys():
                self.report_error("save_one().filed[%s] not in [%s]" % (field, model))
                return 0
            column_sql += " `%s`," % field
            value_sql += " '%s' ," % model[field]
        column_sql = ",".join(column_sql.split(",")[:-1])
        value_sql = ",".join(value_sql.split(",")[:-1])

        # 拼接 UPDATE 从句
        where_sql = " "
        for key in unique_key:
            if key not in model.keys():
                self.report_error("save_one().filed[%s] not in [%s]" % (key, model))
                return 0
            where_sql += " `%s` = '%s' , " % (key, model[key])
        where_sql = ",".join(where_sql.split(",")[:-1])
        return " INSERT INTO `%s` ( %s ) VALUES ( %s ) ON DUPLICATE KEY UPDATE %s ;" % \
               (table, column_sql, value_sql, where_sql)

    def execute(self, execute_sql, log_tag=""):
        rowcount = 0
        try:
            # self.connection.autocommit = False
            cur = self.connection.cursor()
            print "124"
            cur.execute(execute_sql, multi=True)
            print "126"
            self.connection.commit()
            print "128"
            rowcount = cur.rowcount
            print "130"
            cur.close()
            print "132"
        except MySQLdb.Error as e:
            self.report_error("[%s]:mysql_execute:Error:%s" % (log_tag, e))
        except MySQLdb.DatabaseError as e:
            self.report_error("[%s]:mysql_execute:DatabaseError:%s" % (log_tag, e))
        except:
            self.report_error("[%s]:mysql_execute:Error:%s" % log_tag)
        finally:
            return rowcount

    def query(self, query_sql, log_tag=""):
        result = None
        success = False
        try:
            cursor = self.connection.cursor()
            cursor.execute(query_sql)
            result = cursor.fetchall()
            cursor.close()
            success = True
        except MySQLdb.Error, e:
            self.report_error("[%s]:mysql_execute:Error:%s" % (log_tag, e))
        except MySQLdb.DatabaseError as e:
            self.report_error("[%s]:mysql_execute:DatabaseError:%s" % (log_tag, e))
        except:
            self.report_error("[%s]:mysql_execute:Error:%s" % log_tag)
        finally:
            return result, success


# test
def init_log(log_file, debug=False):
    # set up logging to file - see previous section for more details
    logging.basicConfig(level=logging.DEBUG,
                        format='%(asctime)s %(name)-12s %(levelname)-8s %(message)s',
                        datefmt='%m-%d %H:%M',
                        filename=log_file,
                        filemode='a')
    if debug:
        # define a Handler which writes INFO messages or higher to the sys.stderr
        console = logging.StreamHandler()
        console.setLevel(logging.INFO)
        # set a format which is simpler for console use
        formatter = logging.Formatter('%(name)-12s: %(levelname)-8s %(message)s')
        # tell the handler to use this format
        console.setFormatter(formatter)
        # add the handler to the root logger
        logging.getLogger('').addHandler(console)


if __name__ == "__main__":
    init_log("MyDB.log", True)
    data = [

        {
            "ka": "wang",
            "kb": "aaaa",
            "va": "a000",
            "vb": "b00",
            "vc": "c000",
        },

        {
            "ka": "wang-1",
            "kb": "zzzz",
            "va": "a11",
            "vb": "b111",
            "vc": "c111",
        },

        {
            "ka": "wang-2",
            "kb": "yyyy",
            "va": "a222",
            "vb": "b222",
            "vc": "c222",
        },

    ]

    if "ka" not in data[0]:
        print "ka not in data"
        exit(0)

    db = MyDB(host="127.0.0.1",
              port=3306,
              user="000",
              passwd="000",
              db="000"
              )
    db.open_connection()
    db.save("test", data, ["ka", "kb", "va", "vb", "vc"], ["ka", "kb"])
    db.close_connection()
    print "status", db.status
    print "message", db.message
    print "success", db.success_count
    print "fail", (db.total_count - db.success_count)
