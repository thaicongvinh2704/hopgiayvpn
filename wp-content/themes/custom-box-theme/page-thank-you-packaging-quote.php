<?php
/**
 * Template Name: Packaging Quote Thank You Page
 *
 * Google Ads conversion confirmation page for paper box manufacturer quote requests.
 */

defined('ABSPATH') || exit;

add_filter('language_attributes', function () {
    return 'lang="en-US"';
});

get_header();

$catalog_url = home_url('/catalog/');
$landing_url = function_exists('custom_box_get_paper_box_manufacturer_page_url')
    ? custom_box_get_paper_box_manufacturer_page_url()
    : home_url('/paper-box-manufacturer/');
$email = 'sales.vpn@hopgiayvpn.com';
$phone_display = '(+84) 933 102 653';
$phone_link = 'tel:+84933102653';

$next_steps = array(
    array(
        'number' => '01',
        'title'  => 'We Review Your Project',
        'text'   => 'Our team checks your box type, size, quantity, material, artwork, and delivery country.',
    ),
    array(
        'number' => '02',
        'title'  => 'We Suggest the Best Packaging Solution',
        'text'   => 'We recommend suitable paper, structure, printing, finishing, inserts, and export packing options.',
    ),
    array(
        'number' => '03',
        'title'  => 'We Send a Factory Quotation',
        'text'   => 'You will receive a direct factory quote or sample plan based on your project requirements.',
    ),
);

$trust_points = array(
    'Factory-direct custom paper box production',
    'Free design and dieline support',
    'Bulk B2B order support',
    'Export packing support for international buyers',
);
?>

<style>
    .vpn-thank-you-page { --vpn-ty-blue: #063f7a; --vpn-ty-blue-2: #0b62ad; --vpn-ty-ink: #102033; --vpn-ty-muted: #5b6675; --vpn-ty-line: #dfe7ef; --vpn-ty-soft: #f4f8fb; color: var(--vpn-ty-ink); font-family: inherit; }
    .vpn-thank-you-page * { box-sizing: border-box; }
    .vpn-ty-wrap { margin: 0 auto; width: min(1120px, calc(100% - 32px)); }
    .vpn-ty-hero { background: radial-gradient(circle at 24% 20%, rgba(60,153,215,.34), transparent 30%), linear-gradient(135deg, #052347 0%, #063f7a 56%, #0b62ad 100%); color: #fff; overflow: hidden; padding: 76px 0 58px; position: relative; }
    .vpn-ty-hero::after { background: linear-gradient(90deg, rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(180deg, rgba(255,255,255,.08) 1px, transparent 1px); background-size: 44px 44px; content: ""; inset: 0; opacity: .18; position: absolute; }
    .vpn-ty-hero > .vpn-ty-wrap { position: relative; z-index: 1; }
    .vpn-ty-confirm { max-width: 820px; }
    .vpn-ty-icon { align-items: center; background: rgba(220, 252, 231, .14); border: 1px solid rgba(187, 247, 208, .42); border-radius: 999px; color: #bbf7d0; display: inline-flex; font-size: 30px; font-weight: 900; height: 68px; justify-content: center; margin-bottom: 22px; width: 68px; }
    .vpn-ty-kicker { color: #cdeaff; display: block; font-size: 13px; font-weight: 850; letter-spacing: 0; margin-bottom: 12px; text-transform: uppercase; }
    .vpn-thank-you-page h1, .vpn-thank-you-page h2, .vpn-thank-you-page h3, .vpn-thank-you-page p { margin-top: 0; }
    .vpn-thank-you-page h1 { color: #fff; font-size: clamp(36px, 5vw, 58px); letter-spacing: 0; line-height: 1.04; margin-bottom: 18px; max-width: 780px; }
    .vpn-ty-lede { color: #eaf4fc; font-size: 18px; line-height: 1.68; margin-bottom: 14px; max-width: 760px; }
    .vpn-ty-private { color: #d7ebfa; font-size: 14px; font-weight: 750; line-height: 1.55; margin-bottom: 0; }
    .vpn-ty-section { padding: 68px 0; }
    .vpn-ty-soft { background: var(--vpn-ty-soft); }
    .vpn-ty-head { margin-bottom: 26px; max-width: 720px; }
    .vpn-ty-head h2 { color: var(--vpn-ty-ink); font-size: clamp(28px, 3vw, 42px); letter-spacing: 0; line-height: 1.14; margin-bottom: 10px; }
    .vpn-ty-head p { color: var(--vpn-ty-muted); line-height: 1.7; }
    .vpn-ty-steps { display: grid; gap: 18px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .vpn-ty-card { background: #fff; border: 1px solid var(--vpn-ty-line); border-radius: 12px; box-shadow: 0 18px 42px rgba(4,33,71,.08); padding: 24px; }
    .vpn-ty-card span { color: var(--vpn-ty-blue-2); display: block; font-size: 13px; font-weight: 900; margin-bottom: 16px; }
    .vpn-ty-card h3 { color: var(--vpn-ty-ink); font-size: 21px; letter-spacing: 0; line-height: 1.25; margin-bottom: 10px; }
    .vpn-ty-card p { color: var(--vpn-ty-muted); line-height: 1.65; margin-bottom: 0; }
    .vpn-ty-more { align-items: center; display: grid; gap: 30px; grid-template-columns: minmax(0, 1fr) auto; }
    .vpn-ty-actions { align-items: center; display: flex; flex-wrap: wrap; gap: 12px; margin-top: 22px; }
    .vpn-ty-btn { align-items: center; border-radius: 8px; display: inline-flex; font-weight: 850; justify-content: center; min-height: 48px; padding: 13px 18px; text-decoration: none; }
    .vpn-ty-btn-primary { background: var(--vpn-ty-blue); color: #fff; }
    .vpn-ty-btn-primary:hover { background: var(--vpn-ty-blue-2); color: #fff; }
    .vpn-ty-btn-secondary { background: #fff; border: 1px solid #c6d3e1; color: var(--vpn-ty-blue); }
    .vpn-ty-contact { background: #fff; border: 1px solid var(--vpn-ty-line); border-radius: 12px; min-width: 280px; padding: 20px; }
    .vpn-ty-contact strong { color: var(--vpn-ty-ink); display: block; margin-bottom: 10px; }
    .vpn-ty-contact a { color: var(--vpn-ty-blue); display: block; font-weight: 800; margin-top: 8px; text-decoration: none; }
    .vpn-ty-trust { background: #fff; border-top: 1px solid var(--vpn-ty-line); padding: 34px 0; }
    .vpn-ty-trust-list { display: grid; gap: 12px; grid-template-columns: repeat(4, minmax(0, 1fr)); list-style: none; margin: 0; padding: 0; }
    .vpn-ty-trust-list li { align-items: flex-start; color: #1e3148; display: flex; font-size: 14px; font-weight: 800; gap: 9px; line-height: 1.45; }
    .vpn-ty-trust-list li::before { background: #dcfce7; border-radius: 999px; color: #15803d; content: "\2713"; flex: 0 0 auto; font-size: 12px; font-weight: 900; height: 20px; line-height: 20px; margin-top: 1px; text-align: center; width: 20px; }
    @media (max-width: 860px) {
        .vpn-ty-hero { padding: 58px 0 48px; }
        .vpn-ty-steps, .vpn-ty-more, .vpn-ty-trust-list { grid-template-columns: 1fr; }
        .vpn-ty-contact { min-width: 0; }
    }
    @media (max-width: 520px) {
        .vpn-ty-wrap { width: min(100% - 24px, 1120px); }
        .vpn-ty-section { padding: 50px 0; }
        .vpn-thank-you-page h1 { font-size: 34px; }
        .vpn-ty-lede { font-size: 16px; }
        .vpn-ty-actions { align-items: stretch; flex-direction: column; }
        .vpn-ty-btn { width: 100%; }
        .vpn-ty-card { padding: 20px; }
    }
</style>

<main class="vpn-thank-you-page">
    <section class="vpn-ty-hero">
        <div class="vpn-ty-wrap">
            <div class="vpn-ty-confirm">
                <span class="vpn-ty-icon" aria-hidden="true">&check;</span>
                <span class="vpn-ty-kicker">Quote Request Received</span>
                <h1>Thank You for Your Packaging Quote Request</h1>
                <p class="vpn-ty-lede">We have received your custom packaging inquiry. Our team will review your box type, size, quantity, material, artwork, and delivery requirements, then contact you shortly with a suitable factory quotation.</p>
                <p class="vpn-ty-private">Your information is confidential and will only be used to prepare your packaging quotation.</p>
            </div>
        </div>
    </section>

    <section class="vpn-ty-section vpn-ty-soft">
        <div class="vpn-ty-wrap">
            <div class="vpn-ty-head">
                <span class="vpn-ty-kicker">What happens next?</span>
                <h2>A clear factory quotation process</h2>
            </div>
            <div class="vpn-ty-steps">
                <?php foreach ($next_steps as $step) : ?>
                    <article class="vpn-ty-card">
                        <span><?php echo esc_html($step['number']); ?></span>
                        <h3><?php echo esc_html($step['title']); ?></h3>
                        <p><?php echo esc_html($step['text']); ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="vpn-ty-section">
        <div class="vpn-ty-wrap vpn-ty-more">
            <div>
                <div class="vpn-ty-head">
                    <span class="vpn-ty-kicker">Need to add more details?</span>
                    <h2>Send artwork, dimensions, or timeline updates</h2>
                    <p>If you need to send additional artwork, reference images, dimensions, or urgent timeline details, please contact our sales team directly.</p>
                </div>
                <div class="vpn-ty-actions">
                    <a class="vpn-ty-btn vpn-ty-btn-primary" href="<?php echo esc_url($catalog_url); ?>">View Packaging Catalog</a>
                    <a class="vpn-ty-btn vpn-ty-btn-secondary" href="<?php echo esc_url($landing_url); ?>">Back to Paper Box Manufacturer Page</a>
                </div>
            </div>
            <aside class="vpn-ty-contact" aria-label="Sales contact details">
                <strong>Factory Sales Contact</strong>
                <a href="mailto:<?php echo esc_attr($email); ?>">Email: <?php echo esc_html($email); ?></a>
                <a href="<?php echo esc_url($phone_link); ?>">Phone / WhatsApp: <?php echo esc_html($phone_display); ?></a>
            </aside>
        </div>
    </section>

    <section class="vpn-ty-trust" aria-label="Factory trust points">
        <div class="vpn-ty-wrap">
            <ul class="vpn-ty-trust-list">
                <?php foreach ($trust_points as $trust_point) : ?>
                    <li><?php echo esc_html($trust_point); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>
</main>

<?php get_footer(); ?>
