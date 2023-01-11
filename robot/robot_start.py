#!/usr/bin/env python3

# Launcher for robot.
# Ensures no data has been inserted on today's date.

import os
import sys
import mysql.connector
from mysql.connector import errorcode
from urllib.parse import urlparse

from database import Database
from config import Config

def main(bot_id):
    ret = 1
    config = Config(bot_id)
    config.read_ini()

    dbh = Database(config);

    SQL = """
    SELECT time_stamp FROM robot_data WHERE bot_id = %s
    AND DATE(time_stamp) = DATE(NOW())
    """
    cursor = dbh.cnx.cursor()
    try:
        cursor.execute(SQL, (bot_id,))
        rows = cursor.fetchall()
    except mysql.connector.Error as e:
        print("Error: ({}) STATE: ({}) Message: ({})" . format(e.errno, e.sqlstate, e.msg), file=sys.stderr)
        sys.exit(2)

    scan_count = len(rows)
    if scan_count == 0:
        os.environ['ROBOT_START'] = "1"
        ret = os.system("python3 main.py {}" . format(bot_id))

    cursor.close()
    dbh.close()

    return ret

if __name__ == '__main__':
    if len(sys.argv) != 2:
        print("ERR: argument count", file=sys.stderr)
        sys.exit(4)

    sys.exit(main(sys.argv[1]))
