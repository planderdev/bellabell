"""End-to-end contact tests. Mail never leaves the loopback-only SMTP fixture."""
import http.cookiejar
import os
import re
import socket
import socketserver
import subprocess
import tempfile
import threading
import time
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path

ROOT=Path(__file__).resolve().parents[1]
messages=[]
class SMTP(socketserver.StreamRequestHandler):
    def send(self,line): self.wfile.write((line+"\r\n").encode());self.wfile.flush()
    def handle(self):
        self.send("220 localhost test")
        while True:
            raw=self.rfile.readline()
            if not raw: break
            command=raw.decode(errors="replace").strip()
            verb=command.split(" ",1)[0].upper()
            if verb in ("EHLO","HELO"): self.send("250-localhost");self.send("250 8BITMIME")
            elif verb in ("MAIL","RCPT","RSET","NOOP"): self.send("250 OK")
            elif verb=="DATA":
                self.send("354 Send data")
                data=[]
                while True:
                    line=self.rfile.readline()
                    if line==b".\r\n" or not line: break
                    data.append(line)
                messages.append(b"".join(data));self.send("250 Queued locally")
            elif verb=="QUIT": self.send("221 Bye");break
            else: self.send("500 Unsupported")

smtp=socketserver.ThreadingTCPServer(("127.0.0.1",0),SMTP)
threading.Thread(target=smtp.serve_forever,daemon=True).start()
with socket.socket() as sock:
    sock.bind(("127.0.0.1",0));port=sock.getsockname()[1]
rate=Path(tempfile.mkdtemp(prefix="bellabell-integration-")).resolve()
assert rate.parent==Path(tempfile.gettempdir()).resolve() and rate.name.startswith("bellabell-integration-")
env=os.environ.copy()
env.update(APP_ENV="test",MAIL_TRANSPORT="smtp",SMTP_HOST="127.0.0.1",SMTP_PORT=str(smtp.server_address[1]),SMTP_ENCRYPTION="none",SMTP_USERNAME="",SMTP_PASSWORD="",MAIL_FROM="website@example.test",MAIL_TO="recipient@example.test",CONTACT_RATE_DIR=str(rate))
process=subprocess.Popen(["php","-S",f"127.0.0.1:{port}","router.php"],cwd=ROOT,env=env,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL,creationflags=getattr(subprocess,"CREATE_NO_WINDOW",0))
base=f"http://127.0.0.1:{port}"
opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
def request(path,data=None):
    body=urllib.parse.urlencode(data).encode() if data is not None else None
    try:
        with opener.open(base+path,body,timeout=20) as response: return response.status,response.read().decode(),response.headers
    except urllib.error.HTTPError as error: return error.code,error.read().decode(),error.headers
try:
    for _ in range(30):
        try:
            code,body,headers=request("/en/contact")
            if code==200: break
        except (urllib.error.URLError,ConnectionError): time.sleep(.1)
    assert code==200
    assert 'data-ready="true"' in body
    token=re.search(r'name="csrf" value="([a-f0-9]+)"',body).group(1)
    payload=dict(csrf=token,website="",name="Synthetic QA",company="Example Test",email="qa@example.test",type="global",message="Synthetic integration test only. No external delivery.",consent="1")
    assert request("/en/contact",payload)[0]==419,"Timing defence"
    time.sleep(3.2)
    assert request("/en/contact",{**payload,"csrf":"bad"})[0]==419,"CSRF defence"
    assert request("/en/contact",{**payload,"website":"spam"})[0]==419,"Honeypot defence"
    code,body,_=request("/en/contact",{**payload,"name":"<script>alert(1)</script>","email":"invalid"})
    assert code==422 and "&lt;script&gt;" in body and "<script>alert(1)</script>" not in body,"Escaped error rendering"
    assert not messages
    code,body,headers=request("/en/contact",payload)
    assert code==200 and "Your enquiry email has been sent" in body,"SMTP success + PRG"
    assert len(messages)==1 and b"recipient@example.test" in messages[0] and b"Synthetic integration test" in messages[0],"Mail sink receipt"
    assert request("/en/contact",payload)[0]==419,"Successful token replay"
    code,body,_=request("/zh-CN/contact")
    token=re.search(r'name="csrf" value="([a-f0-9]+)"',body).group(1)
    time.sleep(3.2)
    code,body,_=request("/zh-CN/contact",{**payload,"csrf":token,"message":"short"})
    assert code==422 and "20至5,000字" in body,"Localized server validation"
    assert len(messages)==1,"Invalid forms delivered mail"
    print("9 end-to-end contact checks passed; 1 synthetic message received by loopback-only SMTP.")
finally:
    process.terminate();process.wait(timeout=10);smtp.shutdown();smtp.server_close()
    for file in rate.glob("rate-*.json"): file.unlink()
    rate.rmdir()
