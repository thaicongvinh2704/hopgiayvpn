<?php
/** Internal include; called only by the fixed batch importer. */
defined('ABSPATH') || exit;
    $specs = ['Feature' => $p['feature'], 'Industrial Use' => $p['use'], 'Paper Type' => $p['paper'],
        'Box Type' => 'Custom paper carrier bag', 'Shape' => 'Gusseted body with folded top and glued base',
        'Place of Origin' => 'Vietnam', 'Model Number' => $p['sku'], 'Brand Name' => 'VPN', 'Province' => 'Ho Chi Minh City',
        'Accessories' => 'Tissue, gift tags, closure and inserts quoted only when requested', 'Custom Order' => 'Accept',
        'Liner Type' => 'No specific liner or barrier assumed; confirmed for the agreed use', 'Logo Printing' => 'Custom logo; print or foil method reviewed with the artwork',
        'Printing Handling' => 'Colors, process and finishing confirmed with selected paper and sample', 'Color' => $p['color'],
        'Size' => 'Customized size',
        'Thickness' => 'Custom paper grade and thickness; confirmed in the specification',
        'Single Piece Price' => 'Quoted against the specification and quantity',
        'Minimum Order Quantity (MOQ)' => '1,000 pieces general minimum; confirm by size, artwork and construction',
        'Product Name' => $p['title'], 'Design' => 'Customer-specific artwork and construction based on an approved sample'];
    $formatted = []; foreach ($specs as $label => $value) { $formatted[] = ['label' => $label, 'value' => $value]; }
    $wc->update_meta_data('_custom_box_product_specs', $formatted);
    $wc->update_meta_data('_custom_box_product_hero_bullets', $p['bullets']);
    $wc->update_meta_data('_custom_box_hide_auto_description_heading', true);
    $wc->update_meta_data('_vpn_visual_status', 'Owner-supplied AI-generated product design illustrations');
    $wc->update_meta_data('_vpn_luxury_body_hash', hash('sha256', $body)); $wc->save();
