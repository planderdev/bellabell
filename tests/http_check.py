"""Read-only HTTP smoke test for all language routes and local resources."""
import re
import urllib.request
import urllib.error
from html.parser import HTMLParser

BASE = "http://127.0.0.1:8080"
class Page(HTMLParser):
    def __init__(self):
        super().__init__(); self.assets=set(); self.lang=None; self.h1=0; self.canonical=None; self.alternates=[]
    def handle_starttag(self, tag, attrs):
        a=dict(attrs)
        if tag=="html": self.lang=a.get("lang")
        if tag=="h1": self.h1+=1
        if tag=="link" and a.get("rel")=="canonical": self.canonical=a.get("href")
        if tag=="link" and a.get("rel")=="alternate": self.alternates.append(a.get("hreflang"))
        for name in ("src","href","poster","data-src"):
            if a.get(name,"").startswith("/assets/"): self.assets.add(a[name])

assets=set()
for lang in ("ko","en","zh-CN"):
    for route in ("","company","business","technology","sustainability","brands","news","contact","privacy","faq"):
        url=f"{BASE}/{lang}/{route}"
        with urllib.request.urlopen(url) as response:
            assert response.status==200,url
            text=response.read().decode()
            assert "Warning:" not in text and "Fatal error" not in text,url
            assert 'class="motion-toggle"' not in text and 'class="icon-button theme-toggle"' not in text,url
            assert '/assets/images/bellabelfarm-s.svg' in text,url
            assert response.headers.get("X-Content-Type-Options")=="nosniff"
            if route=="contact": assert "no-store" in response.headers.get("Cache-Control","")
        p=Page();p.feed(text)
        assert p.lang==lang and p.h1==1,(url,p.lang,p.h1)
        assert p.canonical==url,(url,p.canonical)
        assert set(p.alternates)=={"ko","en","zh-CN","x-default"},url
        assert "patents" not in text,url
        assets|=p.assets
for path in sorted(assets):
    request=urllib.request.Request(BASE+path,method="HEAD")
    with urllib.request.urlopen(request) as response: assert response.status==200,path
for path in ("/not-a-page","/en/not-a-page","/.env","/data/ko.php","/includes/bootstrap.php","/vendor/autoload.php","/docs/design-system.md","/composer.json"):
    try: urllib.request.urlopen(BASE+path)
    except urllib.error.HTTPError as error: assert error.code==404,(path,error.code)
    else: raise AssertionError(f"Private or unknown route exposed: {path}")
for path in ("/admin","/admin/login"):
    try: urllib.request.urlopen(BASE+path)
    except urllib.error.HTTPError as error:
        assert error.code==503,(path,error.code)
        assert error.headers.get("X-Robots-Tag","").startswith("noindex") and "no-store" in error.headers.get("Cache-Control",""),path
        assert "ADMIN_PASSWORD_HASH" in error.read().decode(),path
    else: raise AssertionError(f"Unconfigured admin exposed: {path}")
try: urllib.request.urlopen(BASE+"/admin/nope")
except urllib.error.HTTPError as error: assert error.code in (404,503),error.code
else: raise AssertionError("Unknown admin route exposed")
for path in ("/robots.txt","/sitemap.xml"):
    with urllib.request.urlopen(BASE+path) as response: assert response.status==200
for cookie, expected in (("bellabell_language=en","en"),("bellabell_language=zh-CN","zh-CN"),("bellabell_language=invalid","ko"),("bellabell_language[]=en","ko")):
    request=urllib.request.Request(BASE+"/?utm_source=qa",headers={"Cookie":cookie})
    with urllib.request.urlopen(request) as response:
        assert response.geturl()==f"{BASE}/{expected}/",(cookie,response.geturl())
request=urllib.request.Request(BASE+"/ko/company",headers={"Cookie":"bellabell_language=en"})
with urllib.request.urlopen(request) as response:
    assert '<html lang="ko"' in response.read().decode()
print(f"30 localized routes, {len(assets)} assets, private-path protection and SEO checks passed.")
print("5 cookie and URL-priority routing checks passed; admin desk hidden until configured.")
