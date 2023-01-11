#!/usr/bin/env python3

import sys
import json
from logging import StreamHandler
import mysql.connector
from mysql.connector import errorcode
from datetime import datetime

class DatabaseHandler(StreamHandler):
    ERROR_FILE = 'something_really_bad_happened.txt'

    def __init__(self, robot):
        StreamHandler.__init__(self)
        self.robot = robot
        self.cnx = robot.dbh.cnx

    def emit(self, record):
        msg = self.format(record)
        bot_id = self.robot.bot_id
        launch_id = self.robot.launch_id
        now = datetime.now()
        m = {
            'level': record.levelname,
            'score': record.levelno,
            'message': msg,    
        }
        txt = json.dumps(m)

        SQL = """
        INSERT INTO robot_log(bot_id, launch_id, time_stamp, message)
        VALUES (%s, %s, %s, %s)
        """

        cursor = self.cnx.cursor()
        data = (bot_id, launch_id, now, txt)
        try:
            cursor.execute(SQL, data)
            self.cnx.commit()
        except mysql.connector.Error as e:
            print("Logging failed: see {}" . format(self.ERROR_FILE))
            with open(self.ERROR_FILE, "w+") as f:
                f.write("Logging failed at {}\n"
                        "Error code: {}\n"
                        "SQLSTATE: {}\n"
                        "Message: {}\n\n"
                        . format(now, e.errno, e.sqlstate, e.msg))
            sys.exit(1)
        
        cursor.close()
