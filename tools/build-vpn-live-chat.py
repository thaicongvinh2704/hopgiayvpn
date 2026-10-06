"""Build the installable plugin only; no runtime, fake credentials, mocks or test data."""
from pathlib import Path
import hashlib
import json
import re
import zipfile

root = Path(__file__).resolve().parents[1]
plugin = root / 'wp-content' / 'plugins' / 'vpn-live-chat'
output = root / 'artifacts' / 'vpn-live-chat'
output.mkdir(parents=True, exist_ok=True)
files = sorted(p for p in plugin.rglob('*') if p.is_file())
allowed = {'.php', '.js', '.css', '.md', '.png'}
assert all(p.suffix in allowed for p in files), 'Unexpected release artifact'
version = re.search(r"define\('VPN_CHAT_VERSION', '([^']+)'\)", (plugin / 'vpn-live-chat.php').read_text(encoding='utf-8')).group(1)
package = output / f'vpn-live-chat-{version}.zip'
manifest = {}
with zipfile.ZipFile(package, 'w', compression=zipfile.ZIP_DEFLATED) as archive:
    for file in files:
        name = 'vpn-live-chat/' + file.relative_to(plugin).as_posix()
        data = file.read_bytes()
        info = zipfile.ZipInfo(name, date_time=(2026, 10, 5, 0, 0, 0))
        info.compress_type = zipfile.ZIP_DEFLATED
        archive.writestr(info, data)
        manifest[name] = hashlib.sha256(data).hexdigest()
digest = hashlib.sha256(package.read_bytes()).hexdigest()
(output / 'manifest.json').write_text(json.dumps({'version': version, 'zip_sha256': digest, 'files': manifest}, indent=2) + '\n', encoding='utf-8')
with zipfile.ZipFile(package) as archive:
    assert archive.testzip() is None
    assert archive.read('vpn-live-chat/vpn-live-chat.php') == (plugin / 'vpn-live-chat.php').read_bytes()
print(json.dumps({'package': str(package), 'files': len(files), 'bytes': package.stat().st_size, 'sha256': digest}))
