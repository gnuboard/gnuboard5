"""외부 PG 대신 로컬 수신기로 실제 cURL 요청의 키 왕복을 확인한다."""
import json
from http.server import BaseHTTPRequestHandler, HTTPServer
from pathlib import Path
import subprocess
import threading
from urllib.parse import quote

class Capture(BaseHTTPRequestHandler):
    def do_POST(self):
        self.server.seen.append((self.path, json.loads(self.rfile.read(int(self.headers['Content-Length'])))))
        self.send_response(200)
        self.end_headers()
        self.wfile.write(b'{}')
    def log_message(self, *args):
        pass

with HTTPServer(('127.0.0.1', 0), Capture) as server:
    server.seen = []
    thread = threading.Thread(target=server.serve_forever, daemon=True)
    thread.start()
    keys = ["Az./+#%?&<>\"'\\", '0', '.', '..', 'A'*200]
    php = """require $argv[1]; $client = new TossPayments('', '', '');
    $client->cancelUrl = $argv[2].'/payments/{paymentKey}/cancel';
    foreach(json_decode($argv[3], true) as $key) {
        $client->cancelData = array('paymentKey'=>$key,'cancelReason'=>'fixture');
        if (!$client->cancelPayment()) exit(1);
    }"""
    subprocess.run(['php', '-r', php, str(Path(__file__).resolve().parents[1]/'shop/toss/toss.inc.php'),
                    'http://127.0.0.1:'+str(server.server_port), json.dumps(keys)], check=True)
    server.shutdown()
    expected = [('/payments/'+(quote(k, safe='') if k not in ('.','..') else '%2E'*len(k))+'/cancel',dict(paymentKey=k,cancelReason='fixture')) for k in keys]
    assert server.seen == expected, (server.seen, expected)
print('통과: 취소 URL과 JSON 본문의 식별자 왕복')
