#!/usr/bin/env python3

import os
import sys
import re
import urllib.robotparser
import hashlib
import time
from urllib.parse import urljoin, urlparse
from urllib import error

from config import Config
from database import Database
from pages import PageList
from download import Download
from sitemaps import SiteMaps
from hypertext import Http

class Robot:
    def __init__(self, bot_id):
        self.wanted_content = "text/html|text/plain|application/json|text/css|application/xml|text/xml"
        self.compile_regexes()
        self.bot_id = bot_id
        self.config = Config(self.bot_id);
        self.dbh = Database(self.config)
        self.retry_count = 0
        self.has_error = False
        self.url = self.address = "{}://{}" . format(self.config.scheme, self.config.domain_name)
        self.page_list = PageList()

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
        self.sitemaps = []
        url = self.address + '/robots.txt';
        self.page_list.append(url)
        rp = urllib.robotparser.RobotFileParser()
        rp.set_url(url)
        rp.read()
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

    def fetch_all(self):
        self.get_robots_txt()

        if self.config.import_sitemaps:
            sm = SiteMaps(self)
            sm.parse()

        self.page_list.append(self.url)

        # Walk the page list, appending as we go.
        for page in self.page_list:
            self.url = page.url

            # Check our URL against robots text rules.
            if not self.rp.can_fetch(self.config.user_agent, self.url):
                continue
            print(self.url)


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
                break
            except error.URLError as e:
                self.retry_count += 1
                if self.retry_count > self.config.retry_max:
                    self.has_error = True
                    break
                else:
                    self.page_list.again()
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

                count = 0

                if not self.config.import_sitemaps or (self.config.import_sitemaps and not page.is_sitemap_source):
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
                        print("found {}" . format(count))
                response.close()
                time.sleep(self.config.scan_delay)

def main(bot_id):
    robot = Robot(bot_id)
    robot.fetch_all()

if __name__ == '__main__':
    if len(sys.argv) != 2:
        print("Missing argument.", file=sys.stderr)
        sys.exit(1)
 
    main(int(sys.argv[1]));
