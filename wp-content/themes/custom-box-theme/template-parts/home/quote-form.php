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
$quote_status = isset($_GET['quote_status']) ? sanitize_text_field(wp_unslash($_GET['quote_status'])) : '';
$quote_messages = array(
    'success'      => 'Thank you. Your quote request has been sent successfully.',
    'failed'       => 'Sorry, we could not send your request right now. Please try again later.',
    'missing'      => 'Please fill in your name, email, and product name.',
    'invalid'      => 'The form session expired. Please refresh the page and try again.',
    'file'         => 'Please upload a valid artwork file under 10MB.',
    'captcha'      => 'Security verification could not be completed. Please reload the page and try again.',
    'spam'         => 'Sorry, this request could not be accepted.',
    'rate_limited' => 'Too many quote requests. Please wait a few minutes and try again.',
);
$quote_status_message = isset($quote_messages[$quote_status]) ? $quote_messages[$quote_status] : '';
?>

<section class="quote-section" id="<?php echo esc_attr($quote_section_id); ?>" aria-labelledby="<?php echo esc_attr($quote_heading_id); ?>">
    <div class="container quote-wrapper">
        <div class="quote-left">
            <h2 id="<?php echo esc_attr($quote_heading_id); ?>">
                Prepare a Clear Brief for
                <span>Your Packaging Quote</span>
            </h2>

            <ul class="quote-list">
                <li>
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <div><strong>Product and Box Type</strong><p>Identify what the pack holds and the structure you need.</p></div>
                </li>
                <li>
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <div><strong>Dimensions and Fit</strong><p>Share product size, box size, and any insert requirements.</p></div>
                </li>
                <li>
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <div><strong>Material Direction</strong><p>State known material needs or ask for options to compare.</p></div>
                </li>
                <li>
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <div><strong>Artwork and Print</strong><p>Attach available artwork and note colors or finishes to review.</p></div>
                </li>
                <li>
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <div><strong>Order Quantity</strong><p>Provide the quantity needed for a project-specific assessment.</p></div>
                </li>
                <li>
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <div><strong>Delivery Destination</strong><p>Include the destination so packing and logistics can be discussed.</p></div>
                </li>
                <li>
                    <i class="fas fa-check-circle" aria-hidden="true"></i>
                    <div><strong>Timing and Constraints</strong><p>Note your target date and any handling or compliance needs.</p></div>
                </li>
            </ul>
        </div>

        <div class="quote-form-box">
            <div class="form-header" role="heading" aria-level="2">Get Your Custom Box Quote</div>
            <p class="quote-form-intro">Tell us what you need. Fields marked <span aria-hidden="true">*</span><span class="screen-reader-text">required</span> are required.</p>

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
                            Product or packaging type <span class="required-marker" aria-hidden="true">*</span>
                        </label>
                        <input
                            id="<?php echo esc_attr($quote_id_prefix); ?>-product"
                            type="text"
                            name="product_name"
                            value="<?php echo esc_attr($quote_product_value); ?>"
                            placeholder="For example: rigid gift box"
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
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-length">Length</label>
                            <input id="<?php echo esc_attr($quote_id_prefix); ?>-length" type="number" name="length" min="0" step="any" inputmode="decimal" placeholder="0">
                        </div>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-width">Width</label>
                            <input id="<?php echo esc_attr($quote_id_prefix); ?>-width" type="number" name="width" min="0" step="any" inputmode="decimal" placeholder="0">
                        </div>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-depth">Depth</label>
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
                        <label for="<?php echo esc_attr($quote_id_prefix); ?>-quantity">Estimated quantity</label>
                        <input id="<?php echo esc_attr($quote_id_prefix); ?>-quantity" type="number" name="quantity" min="1" step="1" inputmode="numeric" placeholder="For example: 1,000">
                    </div>
                </fieldset>

                <details class="quote-optional-disclosure" open data-responsive-disclosure>
                    <summary>More packaging specifications (optional)</summary>
                    <fieldset class="quote-fieldset quote-fieldset-optional">
                        <legend class="screen-reader-text">Material, printing and finishing options</legend>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-stock">Material or stock</label>
                            <select id="<?php echo esc_attr($quote_id_prefix); ?>-stock" name="stock_option">
                                <option value="">Not decided yet</option>
                                <option value="12pt SBS Paperboard">12pt SBS Paperboard</option>
                                <option value="14pt C1S / C2S Cardstock">14pt C1S / C2S Cardstock</option>
                                <option value="16pt Premium Paperboard">16pt Premium Paperboard</option>
                                <option value="18pt Coated Cardstock">18pt Coated Cardstock</option>
                                <option value="20pt Thick Cardstock">20pt Thick Cardstock</option>
                                <option value="22pt Rigid Stock">22pt Rigid Stock</option>
                                <option value="24pt Chipboard">24pt Chipboard</option>
                                <option value="Kraft Brown Paperboard">Kraft Brown Paperboard</option>
                                <option value="White Kraft Board">White Kraft Board</option>
                                <option value="Corrugated E-Flute">Corrugated E-Flute</option>
                                <option value="Corrugated B-Flute">Corrugated B-Flute</option>
                                <option value="Corrugated C-Flute">Corrugated C-Flute</option>
                                <option value="Rigid 60-100 pt">Rigid 60-100 pt</option>
                                <option value="Recycled Cardstock">Recycled Cardstock</option>
                                <option value="Textured / Linen">Textured / Linen</option>
                                <option value="Metallic / Pearlescent">Metallic / Pearlescent</option>
                                <option value="Custom Option (other)">Custom Option (other)</option>
                            </select>
                        </div>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-printing">Printing</label>
                            <select id="<?php echo esc_attr($quote_id_prefix); ?>-printing" name="printing_option">
                                <option value="">Not decided yet</option>
                                <option value="No Printing (Plain)">No Printing (Plain)</option>
                                <option value="1 Color (Single Side)">1 Color (Single Side)</option>
                                <option value="2 Color (Single Side)">2 Color (Single Side)</option>
                                <option value="Full Color CMYK">Full Color CMYK</option>
                                <option value="PMS (Pantone) Printing">PMS (Pantone) Printing</option>
                                <option value="Digital Printing">Digital Printing</option>
                                <option value="Offset Printing">Offset Printing</option>
                                <option value="Inside & Outside Printing">Inside & Outside Printing</option>
                                <option value="Spot Color Printing">Spot Color Printing</option>
                                <option value="Custom Option (other)">Custom Option (other)</option>
                            </select>
                        </div>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-finishing">Finishing</label>
                            <select id="<?php echo esc_attr($quote_id_prefix); ?>-finishing" name="finishing_option">
                                <option value="">Not decided yet</option>
                                <option value="Gloss Lamination">Gloss Lamination</option>
                                <option value="Matte Lamination">Matte Lamination</option>
                                <option value="Soft Touch Lamination">Soft Touch Lamination</option>
                                <option value="Spot UV Coating">Spot UV Coating</option>
                                <option value="Aqueous Coating">Aqueous Coating</option>
                                <option value="Foil Stamping">Foil Stamping</option>
                                <option value="Embossing">Embossing</option>
                                <option value="Debossing">Debossing</option>
                                <option value="Die Cutting">Die Cutting</option>
                                <option value="Window Patching">Window Patching</option>
                                <option value="Inner Foil Lining">Inner Foil Lining</option>
                                <option value="Raised Ink">Raised Ink</option>
                                <option value="Custom Option (other)">Custom Option (other)</option>
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
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-country">Country or region</label>
                            <input id="<?php echo esc_attr($quote_id_prefix); ?>-country" type="text" name="country" autocomplete="country-name">
                        </div>
                        <div class="quote-field">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-phone">Phone or messaging number</label>
                            <input id="<?php echo esc_attr($quote_id_prefix); ?>-phone" type="tel" name="phone" autocomplete="tel" inputmode="tel">
                        </div>
                        <div class="quote-field quote-field-wide">
                            <label for="<?php echo esc_attr($quote_id_prefix); ?>-email">Email <span class="required-marker" aria-hidden="true">*</span></label>
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
                        <label for="<?php echo esc_attr($quote_id_prefix); ?>-message">Additional message <span class="optional-label">(optional)</span></label>
                        <textarea id="<?php echo esc_attr($quote_id_prefix); ?>-message" name="message" rows="5" placeholder="Product use, delivery destination, timeline, references or other requirements"></textarea>
                    </div>
                </fieldset>

                <?php if ($quote_require_privacy_consent) : ?>
                    <fieldset class="quote-fieldset quote-consent-fieldset">
                        <legend>Privacy</legend>
                        <label class="quote-consent-label">
                            <input type="checkbox" name="privacy_consent" value="yes" required>
                            <span>I agree that VPN Paper Box may use this information to advise on and quote this request. <a href="<?php echo esc_url(function_exists('get_privacy_policy_url') && get_privacy_policy_url() ? get_privacy_policy_url() : home_url('/contact/')); ?>">View the Privacy Policy</a>.</span>
                        </label>
                    </fieldset>
                <?php endif; ?>

                <fieldset class="quote-fieldset quote-security-fieldset">
                    <legend>Security check</legend>
                    <?php custom_box_quote_form_recaptcha_fields(); ?>
                </fieldset>

                <button type="submit" class="btn-primary quote-submit-button" data-submit-label="Submit Quote">
                    <span>Submit Quote</span>
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
