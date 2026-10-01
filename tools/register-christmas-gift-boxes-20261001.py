"""Register the new release in the existing scoped product deployment UI."""
from pathlib import Path
ROOT = Path(__file__).resolve().parent.parent
theme = ROOT / 'wp-content/themes/custom-box-theme/inc'
path = theme / 'admin-product-sample-deploy.php'
text = path.read_text(encoding='utf-8')
if "'marker'          => 'christmas-gift-boxes-20261001'" not in text:
    text = text.replace("2026-09-29-christmas-gift-boxes.1", "2026-10-01-christmas-gift-boxes.2")
    text = text.replace("'tools/verify-christmas-gift-boxes-20260929.php' ), true", "'tools/verify-christmas-gift-boxes-20260929.php', 'tools/import-christmas-gift-boxes-20261001.php', 'tools/verify-christmas-gift-boxes-20261001.php' ), true")
    start = text.index("\t\tarray(\n\t\t\t'name'            => 'Christmas gift-box concepts September 2026'")
    end = text.index('\n\t\tarray(',start+10)
    block=text[start:end]
    new=block.replace('20260929','20261001').replace('September 2026','October 1, 2026')
    text=text[:end]+'\n'+new+text[end:]
    start=text.index("\tif ( 'christmas_gift_boxes_20260929' === $scope )")
    end=text.index('\n\t// The default button',start)
    block=text[start:end]
    text=text[:end]+'\n'+block.replace('20260929','20261001')+text[end:]
    text=text.replace("\t\t\t\t\t\t'christmas-gift-boxes-20260929',", "\t\t\t\t\t\t'christmas-gift-boxes-20260929',\n\t\t\t\t\t\t'christmas-gift-boxes-20261001',")
    text=text.replace("'christmas_gift_boxes_20260929' );", "'christmas_gift_boxes_20260929', 'christmas_gift_boxes_20261001' );")
    text=text.replace("\t\t'verify-christmas-gift-boxes-20260929.php',", "\t\t'verify-christmas-gift-boxes-20260929.php',\n\t\t'import-christmas-gift-boxes-20261001.php',\n\t\t'verify-christmas-gift-boxes-20261001.php',")
    start=text.index('\t\t<h2>Christmas Gift Boxes from the September 29 package</h2>')
    end=text.index('\n\t\t<h2>',start+10)
    block=text[start:end]
    text=text[:end]+'\n'+block.replace('September 29','October 1').replace('20260929','20261001').replace('Sync 5 Christmas Gift Boxes','Sync 5 October Christmas Gift Boxes')+text[end:]
    start=text.index('\t\t\t<p>\n\t\t\t\t<label>\n\t\t\t\t\t<input type="radio" name="deploy_scope" value="christmas_gift_boxes_20260929">')
    end=text.index('\n\t\t\t<?php wp_nonce_field',start)
    block=text[start:end]
    text=text[:end]+'\n'+block.replace('20260929','20261001').replace('September 29','October 1')+text[end:]
    path.write_text(text,encoding='utf-8')

path=ROOT/'tools/build-product-sample-deploy-assets.php'
text=path.read_text(encoding='utf-8')
if 'import-christmas-gift-boxes-20261001.php' not in text:
    text=text.replace("\t$root . '/tools/verify-christmas-gift-boxes-20260929.php',", "\t$root . '/tools/verify-christmas-gift-boxes-20260929.php',\n\t$root . '/tools/import-christmas-gift-boxes-20261001.php',\n\t$root . '/tools/verify-christmas-gift-boxes-20261001.php',")
    path.write_text(text,encoding='utf-8')

path=ROOT/'tools/deploy-product-samples-all.php'
text=path.read_text(encoding='utf-8')
if 'import-christmas-gift-boxes-20261001.php' not in text:
    start=text.index("\tarray(\n\t\t'name'        => 'Christmas gift-box concepts September 29, 2026'")
    end=text.index('\n);',start)
    block=text[start:end]
    text=text[:end]+'\n'+block.replace('20260929','20261001').replace('September 29','October 1')+text[end:]
    path.write_text(text,encoding='utf-8')
    (theme/'product-sample-deploy-tools/deploy-product-samples-all.php').write_text(text,encoding='utf-8')
print('Registered October scope, source bundles and CLI deploy batch.')
