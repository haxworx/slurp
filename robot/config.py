#!/usr/bin/env python3

import sys
import configparser
import string
from database import Database
import mysql.connector

class Config:
    """
    Basic configuration for our robot.
    """
    CONFIG_FILE = 'config.ini';

    def __init__(self, bot_id):
        self.bot_id = bot_id
        self.read_ini()
        self.read_settings()

    def read_ini(self):
        """
        Read our database credentials from the config.ini file.
        """

        try:
            with open(self.CONFIG_FILE, "r") as f:
                content = f.read()
                parser = configparser.ConfigParser()
                parser.read_string(content)
                keys = ('host', 'name', 'user', 'pass')
                if not all(key in parser['database'] for key in keys):
                    raise Exception("Missing database config field.")

                self.db_host = parser['database']['host']
                self.db_name = parser['database']['name']
                self.db_user = parser['database']['user']
                self.db_pass = parser['database']['pass']
        except OSError as e:
            print("Unable to open '{}' => {}" . format(self.CONFIG_FILE, e),
                  file=sys.stderr)
            sys.exit(1)
        except Exceptions as e:
            print("Error reading config => {}" . format(e),
                  file=sys.stderr)
            sys.exit(1)
    
    def read_settings(self):
        """
        Read configuration from database tables.
        """

        try:
            dbh = Database(self)

            SQL = """
            SELECT domain_name, scheme, user_agent, scan_delay, import_sitemaps,
            retry_max FROM robot_settings WHERE id = %s
            """

            cursor = dbh.cnx.cursor()
            cursor.execute(SQL, [self.bot_id,])
            rows = cursor.fetchall()
            cursor.close()
            if len(rows) != 1 or len(rows[0]) != 6:
                raise Exception("Unable to retrieve settings for bot id: {}"
                                . format(self.bot_id))

            row = rows[0]

            self.domain_name = row[0]
            self.scheme = row[1]
            self.user_agent = row[2]
            self.scan_delay = int(row[3])
            self.import_sitemaps = bool(row[4])
            self.retry_max = int(row[5])

            dbh.cnx.close()

        except mysql.connector.Error as e:
            print(e.msg)
        except Exception as e:
            print("Error reading config from database -> {}" . format(e),
                  file=sys.stderr)
            sys.exit(1)
