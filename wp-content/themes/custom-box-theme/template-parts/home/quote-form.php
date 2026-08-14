<?php
$quote_args = is_array($args) ? $args : array();
$quote_section_id = isset($quote_args['section_id']) ? sanitize_html_class($quote_args['section_id']) : 'quote';
$quote_form_id = isset($quote_args['form_id']) ? sanitize_html_class($quote_args['form_id']) : $quote_section_id . '-request-form';
$quote_id_prefix = $quote_section_id . '-field';
$quote_heading_id = $quote_section_id . '-heading';
$quote_error_id = $quote_section_id . '-errors';
$quote_status_id = $quote_section_id . '-status';
$quote_product_type = isset($quote_args['product_type']) ? sanitize_key($quote_args['product_type']) : 'boxes';
$quote_product_value = array_key_exists('product_value', $quote_args) ? sanitize_text_field($quote_args['product_value']) : (function_exists('custom_box_quote_product_name') ? custom_box_quote_product_name() : '');
$quote_source = isset($quote_args['quote_source']) ? sanitize_key($quote_args['quote_source']) : '';
$quote_form_location = isset($quote_args['form_location']) ? sanitize_text_field($quote_args['form_location']) : '';
$quote_current_page_url = isset($quote_args['current_page_url']) ? esc_url_raw($quote_args['current_page_url']) : '';
$quote_require_privacy_consent = !empty($quote_args['require_privacy_consent']);
$quote_is_paper_bag = 'paper_bags' === $quote_product_type;
$quote_status = isset($_GET['quote_status']) ? sanitize_text_field(wp_unslash($_GET['quote_status'])) : '';
$quote_messages = array(
    'success'      => 'Thank you. Your quote request has been sent successfully.',
    'failed'       => 'Sorry, we could not send your request right now. Please try again later.',
    'missing'      => $quote_is_paper_bag ? 'Please fill in your name, email, quantity, delivery country or region, and paper bag type.' : 'Please fill in your name, email, and product name.',
    'invalid'      => 'The form session expired. Please refresh the page and try again.',
    'file'         => 'Please upload a valid artwork file under 10MB.',
    'captcha'      => 'Security verification could not be completed. Please reload the page and try again.',
    'spam'         => 'Sorry, this request could not be accepted.',
    'rate_limited' => 'Too many quote requests. Please wait a few minutes and try again.',
);
$quote_stock_options = $quote_is_paper_bag ? array(
    'Brown kraft paper',
    'White kraft paper',
    'Coated or art paper',
    'Recycled kraft paper',
    'Specialty or textured paper',
    'Other / custom option',
) : array(
    '12pt SBS Paperboard',
    '14pt C1S / C2S Cardstock',
    '16pt Premium Paperboard',
    '18pt Coated Cardstock',
    '20pt Thick Cardstock',
    '22pt Rigid Stock',
    '24pt Chipboard',
    'Kraft Brown Paperboard',
    'White Kraft Board',
    'Corrugated E-Flute',
    'Corrugated B-Flute',
    'Corrugated C-Flute',
    'Rigid 60-100 pt',
    'Recycled Cardstock',
    'Textured / Linen',
    'Metallic / Pearlescent',
    'Custom Option (other)',
);
$quote_handle_options = array(
    'Twisted paper handles',
    'Flat paper handles',
    'Cotton rope handles',
    'PP rope handles',
    'Ribbon handles',
    'Die-cut handles',
    'No handles',
    'Other / custom option',
);
$quote_printing_options = $quote_is_paper_bag ? array(
    'No printing',
    'One-color printing',
    'Two-color printing',
    'Full-color CMYK',
    'Pantone printing',
    'Other / custom option',
) : array(
    'No Printing (Plain)',
    '1 Color (Single Side)',
    '2 Color (Single Side)',
    'Full Color CMYK',
    'PMS (Pantone) Printing',
    'Digital Printing',
    'Offset Printing',
    'Inside & Outside Printing',
    'Spot Color Printing',
    'Custom Option (other)',
);
$quote_finishing_options = $quote_is_paper_bag ? array(
    'No additional finishing',
    'Matte lamination',
    'Gloss lamination',
    'Soft-touch lamination',
    'Foil stamping',
    'Embossing',
    'Debossing',
    'Spot UV',
    'Other / custom option',
) : array(
    'Gloss Lamination',
    'Matte Lamination',
    'Soft Touch Lamination',
    'Spot UV Coating',
    'Aqueous Coating',
    'Foil Stamping',
    'Embossing',
    'Debossing',
    'Die Cutting',
    'Window Patching',
    'Inner Foil Lining',
    'Raised Ink',
    'Custom Option (other)',
);
$quote_brief_points = $quote_is_paper_bag ? array(
    array('title' => 'Bag Type and Intended Use', 'text' => 'Tell us what the bag will carry and where it will be used.'),
    array('title' => 'Finished Dimensions', 'text' => 'Share the width, height and gusset if known.'),
    array('title' => 'Paper and Handle Preferences', 'text' => 'Select known preferences or ask our team to review suitable options.'),
    array('title' => 'Artwork and Printing', 'text' => 'Attach available artwork and note colors or finishes to review.'),
    array('title' => 'Estimated Quantity', 'text' => 'Provide the required quantity for a project-specific assessment.'),
    array('title' => 'Delivery Destination', 'text' => 'Include the country or region so packing and shipping requirements can be reviewed.'),
    array('title' => 'Target Schedule', 'text' => 'Share the required delivery or launch date when known.'),
) : array(
    array('title' => 'Product and Box Type', 'text' => 'Identify what the pack holds and the structure you need.'),
    array('title' => 'Dimensions and Fit', 'text' => 'Share product size, box size, and any insert requirements.'),
    array('title' => 'Material Direction', 'text' => 'State known material needs or ask for options to compare.'),
    array('title' => 'Artwork and Print', 'text' => 'Attach available artwork and note colors or finishes to review.'),
    array('title' => 'Order Quantity', 'text' => 'Provide the quantity needed for a project-specific assessment.'),
    array('title' => 'Delivery Destination', 'text' => 'Include the destination so packing and logistics can be discussed.'),
    array('title' => 'Timing and Constraints', 'text' => 'Note your target date and any handling or compliance needs.'),
);
$quote_status_message = isset($quote_messages[$quote_status]) ? $quote_messages[$quote_status] : '';
?>

<section class="quote-section" id="<?php echo esc_attr($quote_section_id); ?>" aria-labelledby="<?php echo esc_attr($quote_heading_id); ?>">
    <div class="container quote-wrapper">
        <div class="quote-left">
            <h2 id="<?php echo esc_attr($quote_heading_id); ?>">
                <?php if ($quote_is_paper_bag) : ?>
                    Prepare a Clear Brief for
                    <span>Your Paper Bag Quote</span>
                <?php else : ?>
                    Prepare a Clear Brief for
                    <span>Your Packaging Quote</span>
                <?php endif; ?>
            </h2>

            <ul class="quote-list">
                <?php foreach ($quote_brief_points as $quote_brief_point) : ?>
                    <li>
                        <i class="fas fa-check-circle" aria-hidden="true"></i>
                        <div><strong><?php echo esc_html($quote_brief_point['title']); ?></strong><p><?php echo esc_html($quote_brief_point['text']); ?></p></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="quote-form-box">
            <div class="form-header" role="heading" aria-level="2"><?php echo $quote_is_paper_bag ? 'Request Your Custom Paper Bag Quote' : 'Get Your Custom Box Quote'; ?></div>
            <p class="quote-form-intro"><?php echo $quote_is_paper_bag ? 'Share your bag specification and contact details. Fields marked ' : 'Tell us what you need. Fields marked '; ?><span aria-hidden="true">*</span><span class="screen-reader-text">required</span><?php echo $quote_is_paper_bag ? ' are required.' : ' are required.'; ?></p>

            <div
                class="quote-form-error-summary"
                id="<?php echo esc_attr($quote_error_id); ?>"
                role="alert"
                tabindex="-1"
                hidden
                data-form-error-summary
            ></div>

            <div
                class="quote-form-message<?php echo $quote_status ? ' quote-form-message-' . esc_attr($quote_status) : ''; ?>"
                id="<?php echo esc_attr($quote_status_id); ?>"
                role="status"
                aria-live="polite"
                aria-atomic="true"
                data-quote-status
                <?php echo $quote_status_message ? '' : 'hidden'; ?>
            ><?php echo esc_html($quote_status_message); ?></div>

            <form
                class="quote-form"
                id="<?php echo esc_attr($quote_form_id); ?>"
                method="post"
                action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                enctype="multipart/form-data"
                aria-describedby="<?php echo esc_attr($quote_error_id . ' ' . $quote_status_id); ?>"
                data-primary-quote-form
                novalidate
            >
                <input type="hidden" name="action" value="custom_box_quote_form">
                <input type="hidden" name="product_type" value="<?php echo esc_attr($quote_product_type); ?>">
                <?php if ($quote_source) : ?><input type="hidden" name="quote_source" value="<?php echo esc_attr($quote_source); ?>"><?php endif; ?>
                <?php if ($quote_form_location) : ?><input type="hidden" name="form_location" value="<?php echo esc_attr($quote_form_location); ?>"><?php endif; ?>
                <?php if ($quote_current_page_url) : ?><input type="hidden" name="current_page_url" value="<?php echo esc_url($quote_current_page_url); ?>"><?php endif; ?>
                <?php wp_nonce_field('custom_box_quote_form', 'custom_box_quote_nonce'); ?>
                <?php custom_box_quote_form_anti_spam_fields('quote'); ?>

                <fieldset class="quote-fieldset">
                    <legend>Project basics</legend>
                    <div class="quote-field">
                        <label for="<?php echo esc_attr($quote_id_prefix); ?>-product">
                            <?php echo $quote_is_paper_bag ? 'Paper bag type or intended use' : 'Product or packaging type'; ?> <span class="required-marker" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="<?php echo esc_attr($quote_id_prefix); ?>-product"
                            type="text"
                            name="product_name"
                            value="<?php echo esc_attr($quote_product_value); ?>"
                            placeholder="<?php echo esc_attr($quote_is_paper_bag ? 'For example: kraft retail bag with twisted paper handles' : 'For example: rigid gift box'); ?>"
                            autocomplete="off"
                            required
                        >
                    </div>
                </fieldset>

                <fieldset class="quote-fieldset">
                    <legend>Size and quantity</legend>
                    <p class="quote-fieldset-help" id="<?php echo esc_attr($quote_id_prefix); ?>-size-help">Enter finished product dimensions if known. All size fields are optional.</p>
                    <div class="quote-dimension-grid" aria-describedby="<?php echo esc_attr($quote_id_prefix); ?>-size-help">
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-length"><?php echo $quote_is_paper_bag ? 'Width' : 'Length'; ?></label>
                            <input id="<?php echo esc_attr($quote_id_prefix); ?>-length" type="number" name="length" min="0" step="any" inputmode="decimal" placeholder="0">
                        </div>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-width"><?php echo $quote_is_paper_bag ? 'Height' : 'Width'; ?></label>
                            <input id="<?php echo esc_attr($quote_id_prefix); ?>-width" type="number" name="width" min="0" step="any" inputmode="decimal" placeholder="0">
                        </div>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-depth"><?php echo $quote_is_paper_bag ? 'Gusset' : 'Depth'; ?></label>
                            <input id="<?php echo esc_attr($quote_id_prefix); ?>-depth" type="number" name="depth" min="0" step="any" inputmode="decimal" placeholder="0">
                        </div>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-unit">Unit</label>
                            <select id="<?php echo esc_attr($quote_id_prefix); ?>-unit" name="unit">
                                <option value="">Choose unit</option>
                                <option value="cm">CM</option>
                                <option value="mm">MM</option>
                                <option value="inch">Inch</option>
                            </select>
                        </div>
                    </div>
                    <div class="quote-field">
                        <label for="<?php echo esc_attr($quote_id_prefix); ?>-quantity">Estimated quantity<?php if ($quote_is_paper_bag) : ?> <span class="required-marker" aria-hidden="true">*</span><?php endif; ?></label>
                        <input id="<?php echo esc_attr($quote_id_prefix); ?>-quantity" type="number" name="quantity" min="1" step="1" inputmode="numeric" placeholder="<?php echo esc_attr($quote_is_paper_bag ? 'For example: 1,000 bags' : 'For example: 1,000'); ?>"<?php echo $quote_is_paper_bag ? ' required' : ''; ?>>
                    </div>
                </fieldset>

                <details class="quote-optional-disclosure" open data-responsive-disclosure>
                    <summary><?php echo $quote_is_paper_bag ? 'More paper bag specifications (optional)' : 'More packaging specifications (optional)'; ?></summary>
                    <fieldset class="quote-fieldset quote-fieldset-optional">
                        <legend class="screen-reader-text"><?php echo $quote_is_paper_bag ? 'Paper, handle, printing and finishing options' : 'Material, printing and finishing options'; ?></legend>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-stock"><?php echo $quote_is_paper_bag ? 'Paper / stock' : 'Material or stock'; ?></label>
                            <select id="<?php echo esc_attr($quote_id_prefix); ?>-stock" name="stock_option">
                                <option value="">Not decided yet</option>
                                <?php foreach ($quote_stock_options as $quote_stock_option) : ?><option value="<?php echo esc_attr($quote_stock_option); ?>"><?php echo esc_html($quote_stock_option); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($quote_is_paper_bag) : ?>
                            <div class="quote-field">
                                <label for="<?php echo esc_attr($quote_id_prefix); ?>-handle">Handle preference</label>
                                <select id="<?php echo esc_attr($quote_id_prefix); ?>-handle" name="material_preference">
                                    <option value="">Not decided yet</option>
                                    <?php foreach ($quote_handle_options as $quote_handle_option) : ?><option value="<?php echo esc_attr($quote_handle_option); ?>"><?php echo esc_html($quote_handle_option); ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-printing">Printing</label>
                            <select id="<?php echo esc_attr($quote_id_prefix); ?>-printing" name="printing_option">
                                <option value="">Not decided yet</option>
                                <?php foreach ($quote_printing_options as $quote_printing_option) : ?><option value="<?php echo esc_attr($quote_printing_option); ?>"><?php echo esc_html($quote_printing_option); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-finishing">Finishing</label>
                            <select id="<?php echo esc_attr($quote_id_prefix); ?>-finishing" name="finishing_option">
                                <option value="">Not decided yet</option>
                                <?php foreach ($quote_finishing_options as $quote_finishing_option) : ?><option value="<?php echo esc_attr($quote_finishing_option); ?>"><?php echo esc_html($quote_finishing_option); ?></option><?php endforeach; ?>
                            </select>
                        </div>
                    </fieldset>
                </details>

                <fieldset class="quote-fieldset">
                    <legend>Your contact details</legend>
                    <div class="quote-contact-grid">
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-name">Full name <span class="required-marker" aria-hidden="true">*</span></label>
                            <input id="<?php echo esc_attr($quote_id_prefix); ?>-name" type="text" name="full_name" autocomplete="name" required>
                        </div>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-company">Company</label>
                            <input id="<?php echo esc_attr($quote_id_prefix); ?>-company" type="text" name="company" autocomplete="organization">
                        </div>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-country"><?php echo $quote_is_paper_bag ? 'Delivery country or region' : 'Country or region'; ?><?php if ($quote_is_paper_bag) : ?> <span class="required-marker" aria-hidden="true">*</span><?php endif; ?></label>
                            <input id="<?php echo esc_attr($quote_id_prefix); ?>-country" type="text" name="country" autocomplete="country-name"<?php echo $quote_is_paper_bag ? ' required' : ''; ?>>
                        </div>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-phone"><?php echo $quote_is_paper_bag ? 'Phone or WhatsApp (optional)' : 'Phone or messaging number'; ?></label>
                            <input id="<?php echo esc_attr($quote_id_prefix); ?>-phone" type="tel" name="phone" autocomplete="tel" inputmode="tel">
                        </div>
                        <div class="quote-field quote-field-wide">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-email"><?php echo $quote_is_paper_bag ? 'Work email' : 'Email'; ?> <span class="required-marker" aria-hidden="true">*</span></label>
                            <input id="<?php echo esc_attr($quote_id_prefix); ?>-email" type="email" name="email" autocomplete="email" inputmode="email" required>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="quote-fieldset">
                    <legend>Artwork and project notes</legend>
                    <div class="quote-field">
                        <label for="<?php echo esc_attr($quote_id_prefix); ?>-artwork">Artwork file <span class="optional-label">(optional)</span></label>
                        <p class="quote-field-help" id="<?php echo esc_attr($quote_id_prefix); ?>-artwork-help">PNG, PDF, JPG, WebP, DOC, PSD, CDR or EPS; maximum 10MB.</p>
                        <input
                            id="<?php echo esc_attr($quote_id_prefix); ?>-artwork"
                            type="file"
                            name="artwork"
                            accept=".png,.pdf,.jpg,.jpeg,.webp,.doc,.docx,.gif,.psd,.cdr,.eps"
                            aria-describedby="<?php echo esc_attr($quote_id_prefix); ?>-artwork-help"
                        >
                    </div>
                    <div class="quote-field">
                        <label for="<?php echo esc_attr($quote_id_prefix); ?>-message"><?php echo $quote_is_paper_bag ? 'Project notes' : 'Additional message'; ?> <span class="optional-label">(optional)</span></label>
                        <textarea id="<?php echo esc_attr($quote_id_prefix); ?>-message" name="message" rows="5" placeholder="<?php echo esc_attr($quote_is_paper_bag ? 'Product use, paper, handles, artwork, printing, packing, delivery destination or other requirements' : 'Product use, delivery destination, timeline, references or other requirements'); ?>"></textarea>
                    </div>
                </fieldset>

                <?php if ($quote_require_privacy_consent) : ?>
                    <fieldset class="quote-fieldset quote-consent-fieldset">
                        <legend>Privacy</legend>
                        <label class="quote-consent-label">
                            <input type="checkbox" name="privacy_consent" value="yes" required>
                            <span>I agree that VPN Paper Box may use this information to advise on and quote this request. <?php
                                $quote_privacy_policy_id = (int) get_option('wp_page_for_privacy_policy');
                                $quote_privacy_policy_url = ($quote_privacy_policy_id > 0 && 'publish' === get_post_status($quote_privacy_policy_id) && function_exists('get_privacy_policy_url')) ? get_privacy_policy_url() : '';
                                if ($quote_privacy_policy_url) :
                                    ?><a href="<?php echo esc_url($quote_privacy_policy_url); ?>">View the Privacy Policy</a><?php
                                else :
                                    ?>Privacy policy page is not currently available.<?php
                                endif;
                            ?>.</span>
                        </label>
                    </fieldset>
                <?php endif; ?>

                <fieldset class="quote-fieldset quote-security-fieldset">
                    <legend>Security check</legend>
                    <?php custom_box_quote_form_recaptcha_fields(); ?>
                </fieldset>

                <button type="submit" class="btn-primary quote-submit-button" data-submit-label="<?php echo esc_attr($quote_is_paper_bag ? 'Request a Custom Paper Bag Quote' : 'Submit Quote'); ?>">
                    <span><?php echo $quote_is_paper_bag ? 'Request a Custom Paper Bag Quote' : 'Submit Quote'; ?></span>
                </button>
            </form>

            <script>
                (function() {
                    var statusMessages = {
                        success: 'Thank you. Your quote request has been sent successfully.',
                        failed: 'Sorry, we could not send your request right now. Please try again later.',
                        missing: 'Please fill in your name, email, and product name.',
                        invalid: 'The form session expired. Please refresh the page and try again.',
                        file: 'Please upload a valid artwork file under 10MB.',
                        captcha: 'Security verification could not be completed. Please reload the page and try again.',
                        spam: 'Sorry, this request could not be accepted.',
                        rate_limited: 'Too many quote requests. Please wait a few minutes and try again.'
                    };

                    function getFieldLabel(form, field) {
                        var label = field.id ? form.querySelector('label[for="' + field.id + '"]') : null;
                        return label ? label.textContent.replace('*', '').trim() : (field.name || 'This field');
                    }

                    function clearFieldError(field) {
                        var errorId = field.id ? field.id + '-error' : '';
                        var describedBy = (field.getAttribute('aria-describedby') || '')
                            .split(/\s+/)
                            .filter(function(id) { return id && id !== errorId; });
                        var inlineError = errorId ? document.getElementById(errorId) : null;

                        if (inlineError) {
                            inlineError.remove();
                        }

                        if (describedBy.length) {
                            field.setAttribute('aria-describedby', describedBy.join(' '));
                        } else {
                            field.removeAttribute('aria-describedby');
                        }

                        field.removeAttribute('aria-invalid');
                        field.classList.remove('is-invalid');
                    }

                    function validateQuoteForm(form) {
                        var summary = form.parentNode.querySelector('[data-form-error-summary]');
                        var invalidFields;

                        form.querySelectorAll('[aria-invalid="true"]').forEach(clearFieldError);

                        if (form.checkValidity()) {
                            if (summary) {
                                summary.hidden = true;
                                summary.innerHTML = '';
                            }
                            return true;
                        }

                        invalidFields = Array.from(
                            form.querySelectorAll('input:invalid, select:invalid, textarea:invalid')
                        );

                        if (summary) {
                            var heading = document.createElement('p');
                            var list = document.createElement('ul');
                            summary.innerHTML = '';
                            heading.textContent = 'Please review the following fields:';
                            summary.appendChild(heading);

                            invalidFields.forEach(function(field) {
                                var fieldLabel = getFieldLabel(form, field);
                                var errorText = fieldLabel + ': ' + (field.validationMessage || 'Please complete this field.');
                                var errorId = field.id ? field.id + '-error' : '';
                                var fieldWrapper = field.closest('.quote-field');
                                var item = document.createElement('li');
                                var link = document.createElement('a');
                                var inlineError;
                                var describedBy;

                                field.setAttribute('aria-invalid', 'true');
                                field.classList.add('is-invalid');

                                if (errorId && fieldWrapper) {
                                    inlineError = document.createElement('span');
                                    inlineError.className = 'quote-field-error';
                                    inlineError.id = errorId;
                                    inlineError.dataset.errorFor = field.id;
                                    inlineError.textContent = field.validationMessage || 'Please complete this field.';
                                    fieldWrapper.appendChild(inlineError);
                                    describedBy = (field.getAttribute('aria-describedby') || '')
                                        .split(/\s+/)
                                        .filter(Boolean);
                                    if (!describedBy.includes(errorId)) {
                                        describedBy.push(errorId);
                                    }
                                    field.setAttribute('aria-describedby', describedBy.join(' '));
                                }

                                link.href = field.id ? '#' + field.id : '#';
                                link.textContent = errorText;
                                link.addEventListener('click', function(linkEvent) {
                                    linkEvent.preventDefault();
                                    field.focus();
                                });
                                item.appendChild(link);
                                list.appendChild(item);
                            });

                            summary.appendChild(list);
                            summary.hidden = false;
                            summary.focus();
                        }

                        return false;
                    }

                    if (!window.customBoxQuoteValidationCaptureReady) {
                        window.customBoxQuoteValidationCaptureReady = true;
                        document.addEventListener('submit', function(event) {
                            var form = event.target;

                            if (!form || !form.matches('.quote-form[data-primary-quote-form]')) {
                                return;
                            }

                            if (!validateQuoteForm(form)) {
                                event.preventDefault();
                                event.stopImmediatePropagation();
                            }
                        }, true);
                    }

                    document.querySelectorAll('.quote-form[data-primary-quote-form]').forEach(function(form, index) {
                        if (form.dataset.quoteIframeReady) {
                            return;
                        }

                        form.dataset.quoteIframeReady = '1';
                        var submitted = false;
                        var restoreTimer = null;
                        var iframeName = 'custom_box_quote_submit_' + index + '_' + Date.now();
                        var iframe = document.createElement('iframe');
                        var summary = form.parentNode.querySelector('[data-form-error-summary]');
                        var message = form.parentNode.querySelector('[data-quote-status]');
                        var button = form.querySelector('button[type="submit"]');
                        var buttonLabel = button ? button.querySelector('span') : null;
                        var defaultButtonText = buttonLabel ? buttonLabel.textContent : 'Submit Quote';

                        iframe.name = iframeName;
                        iframe.title = 'Quote form submission';
                        iframe.hidden = true;
                        iframe.style.display = 'none';
                        form.parentNode.appendChild(iframe);

                        form.querySelectorAll('input, select, textarea').forEach(function(field) {
                            field.addEventListener('input', function() {
                                if (field.checkValidity()) {
                                    clearFieldError(field);
                                }
                            });
                            field.addEventListener('change', function() {
                                if (field.checkValidity()) {
                                    clearFieldError(field);
                                }
                            });
                        });

                        form.addEventListener('submit', function(event) {
                            event.preventDefault();

                            if (submitted || 'true' === form.dataset.submitting) {
                                return;
                            }

                            if (!validateQuoteForm(form)) {
                                return;
                            }

                            submitted = true;
                            form.dataset.submitting = 'true';
                            form.target = iframeName;

                            if (message) {
                                message.hidden = false;
                                message.className = 'quote-form-message quote-form-message-pending';
                                message.textContent = 'Sending your request...';
                            }

                            if (button) {
                                button.disabled = true;
                                button.setAttribute('aria-disabled', 'true');
                            }
                            if (buttonLabel) {
                                buttonLabel.textContent = 'Sending...';
                            }

                            window.clearTimeout(restoreTimer);
                            restoreTimer = window.setTimeout(function() {
                                if (!submitted) return;
                                submitted = false;
                                form.dataset.submitting = 'false';
                                if (button) {
                                    button.disabled = false;
                                    button.removeAttribute('aria-disabled');
                                }
                                if (buttonLabel) {
                                    buttonLabel.textContent = defaultButtonText;
                                }
                                if (message) {
                                    message.hidden = false;
                                    message.className = 'quote-form-message quote-form-message-failed';
                                    message.textContent = statusMessages.failed;
                                }
                            }, 30000);

                            HTMLFormElement.prototype.submit.call(form);
                        });

                        iframe.addEventListener('load', function() {
                            var status = '';

                            if (!submitted) {
                                return;
                            }

                            window.clearTimeout(restoreTimer);

                            try {
                                status = new URL(iframe.contentWindow.location.href).searchParams.get('quote_status') || '';
                                if (!status && iframe.contentWindow.document.body && iframe.contentWindow.document.body.textContent.indexOf('Too many quote requests') !== -1) {
                                    status = 'rate_limited';
                                }
                            } catch (error) {
                                status = '';
                            }

                            if (!status) {
                                status = 'failed';
                            }

                            if (message) {
                                message.hidden = false;
                                message.className = 'quote-form-message quote-form-message-' + status;
                                message.textContent = statusMessages[status] || statusMessages.failed;
                            }

                            if ('success' === status) {
                                if (
                                    !window.__vpnGoogleAdsQuoteConversionSent &&
                                    typeof window.gtag === 'function'
                                ) {
                                    window.gtag('event', 'conversion', {
                                        send_to: 'AW-18190091085/6FzwCNKm0NscEM2G2-FD'
                                    });

                                    window.__vpnGoogleAdsQuoteConversionSent = true;
                                }

                                form.reset();
                            }

                            submitted = false;
                            form.dataset.submitting = 'false';
                            if (button) {
                                button.disabled = false;
                                button.removeAttribute('aria-disabled');
                            }
                            if (buttonLabel) {
                                buttonLabel.textContent = defaultButtonText;
                            }
                        });
                    });
                })();
            </script>
        </div>
    </div>
</section>
