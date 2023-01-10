import xml.dom.minidom
from download import Download

class SiteMaps:
    def __init__(self, robot):
        self.robot = robot

    def parse(self):
        for sitemap in self.robot.sitemaps:
            download = Download(sitemap, self.robot.config.user_agent)
            contents = download.contents()
            if contents is None:
                continue
            
            dom = xml.dom.minidom.parseString(contents)
            sitemap_index = dom.getElementsByTagName('sitemap')
            if not sitemap_index:
                self.read_sitemap(contents)
            else:
                for url in sitemap_index:
                    nodes = url.getElementsByTagName('loc')
                    for node in nodes:
                        self.robot.page_list.append(node.firstChild.nodeValue, sitemap_url=True);

    def read_sitemap(self, contents):
        dom = xml.dom.minidom.parseString(contents)
        urls = dom.getElementsByTagName('url')
        for url in urls:
            nodes = url.getElementsByTagName('loc')
            for node in nodes:
                self.robot.page_list.append(node.firstChild.nodeValue, sitemap_url=True);

