"""Python standard-library-only public-site HTTP smoke test.
Start `php -S 127.0.0.1:8000 -t public public/router.php` before running.
"""
import urllib.request
import urllib.error
from html.parser import HTMLParser
from urllib.parse import urlparse
from pathlib import Path
import re

HOST = 'http://127.0.0.1:8000'
class PageParser(HTMLParser):
    def __init__(self):
        super().__init__();self.links=[];self.images=[];self.h1=0;self.page_title=False
    def handle_starttag(self, tag, attrs):
        d=dict(attrs)
        if tag == 'a' and d.get('href'):self.links.append(d['href'])
        if tag == 'img' and d.get('src'):self.images.append(d['src'])
        if tag == 'h1':self.h1+=1
        if tag == 'title':self.page_title=True

def fetch(path):
    try:
        with urllib.request.urlopen(HOST+path,timeout=8) as r:return r.status,r.read().decode('utf-8',errors='replace')
    except urllib.error.HTTPError as e:return e.code,e.read().decode('utf-8',errors='replace')

def run():
    paths=['/','/about/','/services/','/insights/','/newsletter/','/contact/','/privacy/']
    import sys
    sys.path.append(str(Path(__file__).parents[1]))
    # PHP data records mirror the intended routes; test each publicly accessible link.
    pending=paths[:];checked=set();asset_paths=set();parsed=0
    while pending:
        path=pending.pop(0)
        if path in checked or path.startswith('/api/') or path.startswith('/#'):continue
        checked.add(path)
        status,html=fetch(path)
        assert status==200, f'Page {path} -> HTTP {status}'
        parser=PageParser();parser.feed(html)
        assert parser.page_title, f'No title: {path}'
        assert parser.h1==1, f'Expected exactly one h1: {path} -> {parser.h1}'
        assert 'noindex,nofollow' in html, f'Unexpected indexing of preview: {path}'
        parsed+=1
        for src in parser.images:asset_paths.add(src)
        for link in parser.links:
            if link.startswith('/'):
                parsed_path=urlparse(link).path
                if parsed_path.startswith('/assets/'):asset_paths.add(parsed_path)
                elif parsed_path not in checked and parsed_path not in pending:pending.append(parsed_path)
    assert parsed==19, f'Expected 19 content pages, got {parsed}; inspected {sorted(checked)}'
    for asset in sorted(asset_paths | {'/assets/js/main.js','/assets/css/style.css','/assets/images/favicon.svg'}):
        request=urllib.request.Request(HOST+asset,method='HEAD')
        with urllib.request.urlopen(request,timeout=8) as r:
            assert r.status==200, f'Missing asset: {asset}'
    for fail in ['/missing-page/','/services/nope/','/newsletter/edition-09/','/private.php']:
        status,_=fetch(fail)
        assert status==404, f'Expected 404 {fail}: {status}'
    status,robots=fetch('/robots.txt');assert status==200 and 'Disallow: /' in robots
    status,_=fetch('/sitemap.xml');assert status==404
    status,form=fetch('/contact/');assert status==200 and re.search('name="csrf"[^>]+value="[a-f0-9]{48}"',form)
    print(f'PASS: {parsed} public pages, {len(asset_paths)} page-referenced assets, 404s, preview robots and contact CSRF')

if __name__=='__main__':run()
