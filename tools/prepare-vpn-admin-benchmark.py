"""Read the 1.8.3 release into the isolated test runtime for before/after benchmarks."""
from pathlib import Path
import zipfile

root = Path(__file__).resolve().parents[1]
runtime = root / 'tests/live-chat/runtime'
runtime.mkdir(parents=True, exist_ok=True)
with zipfile.ZipFile(root / 'artifacts/vpn-live-chat/vpn-live-chat-1.8.3.zip') as archive:
    main = archive.read('vpn-live-chat/vpn-live-chat.php').decode('utf-8')
    assert "define('VPN_CHAT_VERSION', '1.8.3')" in main
    (runtime / 'admin-before.js').write_bytes(archive.read('vpn-live-chat/assets/admin.js'))
    rest = archive.read('vpn-live-chat/includes/rest.php').decode('utf-8')
    (runtime / 'rest-before.php').write_text(rest.replace('final class VPN_Chat_REST', 'final class VPN_Chat_Before_REST', 1), encoding='utf-8')
print('Prepared isolated 1.8.3 baseline from the release ZIP.')
