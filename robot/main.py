#!/usr/bin/env python3

import os
import sys
import re
import urllib.robotparser
import hashlib
import time
import logging
from datetime import datetime
from urllib.parse import urljoin, urlparse
from urllib import error

import logs
from config import Config
from database import Database
from pages import PageList
from download import Download
from sitemaps import SiteMaps
from hypertext import Http

class Robot:
    def __init__(self, bot_id):
        self.save_count = 0
        self.launch_id = None
        self.is_running = False
        self.has_error = False
        self.wanted_content = "text/html|text/plain|application/json|text/css|application/xml|text/xml"
        self.wanted_content += "|image/png|image/jpeg|image/gif|image/svg+xml|image/webp";
        self.compile_regexes()
        self.bot_id = bot_id
        self.config = Config(self.bot_id);
        self.config.read_all()
        self.dbh = Database(self.config)
        self.retry_count = 0
        self.has_error = False
        self.url = self.address = "{}://{}" . format(self.config.scheme, self.config.domain_name)
        self.page_list = PageList()

        logging.basicConfig(level=logging.INFO, stream=sys.stdout, format="%(message)s")
        self.log = logging.getLogger(self.config.domain_name)
        self.log.addHandler(logs.DatabaseHandler(self))

    def compile_regexes(self):
        try:
            self.wanted = re.compile(self.wanted_content,
                                     re.IGNORECASE)
            self.charset = re.compile('charset=([a-zA-Z0-9-_]+)',
                                      re.IGNORECASE)
            self.hrefs = re.compile("href=[\"\'](.*?)[\"\']",
                                    re.IGNORECASE)
        except re.error as e:
            print("Regex compilation failed: {}" . format(e), file=sys.stderr)
            sys.exit(1)

    def get_robots_txt(self):
        """
        Initialise our robot parser and populate our sitemaps.
        """
        self.sitemaps = []
        url = self.address + '/robots.txt';
        self.page_list.append(url)
        try:
            rp = urllib.robotparser.RobotFileParser()
            rp.set_url(url)
            rp.read()
        except urllib.error.URLError as e:
            self.log.warning("unable to read robots.txt: %s", e.reason)
            self.rp = None
            return

        self.rp = rp
        if rp.site_maps() is not None:
            for address in rp.site_maps():
                self.sitemaps.append(address)
                self.page_list.append(address)

    def domain_parse(self, url):
        domain = urlparse(url).netloc
        if len(domain) == 0:
            return None
        return domain

    def scheme_parse(self, url):
        scheme = urlparse(url).scheme
        if len(scheme) == 0:
            return None
        return scheme

    def valid_link(self, link):
        """
        Check link is valid and against robot.txt rules.
        """

        if len(link) == 0:
            return False

        if link[0] != '/':
            return False

        return self.rp.can_fetch(self.config.user_agent, self.config.domain_name + link)

    def metadata_extract(self, headers):
        """
        Extract a copy of our HTTP headers and create a string duplicating
        them.
        """
        metadata = ""
        for name, value in headers.items():
            metadata = metadata + name + ': ' + value + '\n'
        return metadata

    def started(self):
        """
        Set up health state and record the starting times of our robot.
        """
        everything_is_fine = True
        self.is_running = True

        SQL = """
        INSERT INTO robot_launches (bot_id, start_time) VALUES (%s, %s)
        """
        cursor = self.dbh.cnx.cursor()
        try:
            cursor.execute(SQL, (self.bot_id, datetime.now()))
            self.dbh.cnx.commit()
        except mysql.connector.Error as e:
            self.log.critical("Failed to save launches to database (%i):(%s)", e.errno, e.msg)
            self.has_error = True
            everything_is_fine = False

        self.launch_id = cursor.lastrowid
        cursor.close()

        self.log.info("starting")

        return everything_is_fine

    def finished(self):
        """
        Crawl finished, record our state and finishing time of our robot.
        """
        everything_is_fine = True
        self.is_running = False
        now = datetime.now()

        SQL = """
        UPDATE robot_settings SET end_time = %s, is_running = %s, has_error = %s WHERE id = %s
        """
        cursor = self.dbh.cnx.cursor()
        try:
            cursor.execute(SQL, (now, self.is_running, self.has_error, self.bot_id))
            self.dbh.cnx.commit()
        except mysql.connector.Error as e:
            self.log.critical("Failed to store finished state to robot_settings (%i):(%s)", e.errno, e.msg)
            everything_is_fine = False
        cursor.close()

        SQL = """
        UPDATE robot_launches SET end_time = %s WHERE id = %s
        """
        cursor = self.dbh.cnx.cursor()
        try:
            cursor.execute(SQL, (now, self.launch_id))
            self.dbh.cnx.commit()
        except mysql.connector.Error as e:
            self.log.critical("Failed to store finished state to robot_launches (%i):(%s)", e.errno, e.msg)
            everything_is_fine = False
        cursor.close()

        self.log.info("finished")

        return everything_is_fine

    def save_results(self, res):
        """
        Save a record of a single crawl.
        """

        everything_is_fine = True

        SQL = """
        INSERT INTO robot_data (bot_id, launch_id, time_stamp, link_source, modified,
        status_code, content_type, headers, url, path, checksum, encoding, length, data)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        val = (res['bot_id'], res['launch_id'], res['time_stamp'], res['link_source'],
               res['modified'], res['status_code'], res['content_type'], res['headers'],
               res['url'], res['path'], res['checksum'], res['encoding'], res['length'],
               res['data'])

        cursor = self.dbh.cnx.cursor()
        try:
            cursor.execute(SQL, val)
            self.dbh.cnx.commit()
        except mysql.connector.Error as e:
            self.log.critical("failed to save record to database (%i): %s", e.errno, e.msg)
            self.has_error = True
            everything_is_fine = False

        cursor.close()
        if everything_is_fine:
            self.save_count += 1

        return everything_is_fine

    def fetch_all(self):
        """
        Main loop of our crawler.
        """
        self.started()
        self.get_robots_txt()

        if self.config.import_sitemaps:
            sm = SiteMaps(self)
            sm.parse()

        self.page_list.append(self.url)

        # Walk the page list, appending as we go.
        for page in self.page_list:
            self.url = page.url

            # Check our URL against robots text rules.
            if self.rp is not None and not self.rp.can_fetch(self.config.user_agent, self.url):
                self.log.warning("ignoring %s due to robots txt rule", self.url)
                continue

            parsed_url = urlparse(self.url)
            (scheme, path, query) = (parsed_url.scheme, parsed_url.path,
                                     parsed_url.query)
            # Ignore any URL with a query string.
            if len(query):
                continue
            try:
                downloader = Download(self.url, self.config.user_agent)
                (response, code) = downloader.get()
            except error.HTTPError as e:
                self.log.warning("failed to download %s (%i)", self.url, e.code)
                break
            except error.URLError as e:
                self.log.error("failed to connect %s (%s)", self.url, e.reason)
                self.retry_count += 1
                if self.retry_count > self.config.retry_max:
                    self.has_error = True
                    self.log.critical("retry count limit reached (%i)", self.config.retry_max)
                    break
                else:
                    self.page_list.again()
                    self.log.warning("retrying %s", self.url)
                    continue
            except Exception as e:
                pass
            else:
                self.retry_count = 0
                # Ignore redirects outside of the domain.
                if self.config.domain_name.upper() != self.domain_parse(response.url).upper():
                    continue
                # Ignore redirects to a different scheme.
                if self.config.scheme.upper() != self.scheme_parse(response.url).upper():
                    continue

                self.url = response.url

                # Extract HTTP header values of interest.
                content_type = Http.string(response.headers['Content-Type'])
                length = Http.int(response.headers['content-length'])
                modified = Http.date(response.headers['last-modified'])

                # Ensure the content type is wanted.
                matches = self.wanted.search(content_type)
                if matches:
                    # Attempt to extract a precise character encoding.
                    content_type = matches.group(0)
                    encoding = 'iso-8859-1'
                    matches = self.charset.search(response.headers['content-type'])
                    if matches:
                        encoding = matches.group(1)

                    data = response.read()
                    metadata = self.metadata_extract(response.headers)
                    checksum = hashlib.md5(data)

                    res = {
                        'bot_id': self.bot_id,
                        'launch_id': self.launch_id,
                        'time_stamp': datetime.now(),
                        'link_source': page.link_source,
                        'modified': modified,
                        'status_code': code,
                        'content_type': content_type,
                        'headers': metadata,
                        'url': self.url,
                        'path': path,
                        'checksum': checksum.hexdigest(),
                        'encoding': encoding,
                        'length': length or len(data),
                        'data': data
                    }
                else:
                    continue

                if not self.save_results(res):
                    self.log.critical("could not save record to database table.")
                    self.has_error = True
                    break

                self.log.info("saved %s", self.url)

                count = 0

                # Extract URLs and append to our page list if the current page is not a sitemap source or
                # config.import_sitemaps is not set.
                try:
                    content = data.decode(encoding)
                except UnicodeDecodeError as e:
                    content = data.decode('iso-8859-1')
                links = self.hrefs.findall(content)
                for link in links:
                    if self.valid_link(link):
                        url = urljoin(self.url, link)
                        domain = self.domain_parse(url)
                        scheme = self.scheme_parse(url)
                        if (domain.upper() == self.config.domain_name.upper()) and (scheme.upper() == self.config.scheme.upper()):
                            if self.page_list.append(url, link_source=page.url):
                                count += 1
                if count:
                    self.log.info("found %i links on %s", count, self.url)
                response.close()
                time.sleep(self.config.scan_delay)

        self.log.info("Saved total of %i records.", self.save_count)
        self.finished()

def main(bot_id):
    robot = Robot(bot_id)
    robot.fetch_all()
    sys.exit(0)

if __name__ == '__main__':
    ROBOT_START = os.getenv('ROBOT_START')
    if ROBOT_START is None:
        print("This program should not be launched directly.", file=sys.stderr)
        sys.exit(1)

    if len(sys.argv) != 2:
        print("Missing argument.", file=sys.stderr)
        sys.exit(1)

    main(int(sys.argv[1]));
