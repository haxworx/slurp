#!/usr/bin/env python3

import sys
import mysql.connector
from mysql.connector import errorcode

class Database:
    cnx = None

    def __init__(self, config):
        try:
            self.cnx = mysql.connector.connect(user=config.db_user,
                                               password=config.db_pass,
                                               host=config.db_host,
                                               database=config.db_name)
        except mysql.connector.Error as e:
            print("Unable to connect ({}): {}" . format(e.errno, e.msg),
                  file=sys.stderr)
            sys.exit(1);

    def close(self):
        if self.cnx is not None:
            self.cnx.close()
