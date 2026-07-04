<?php
$quote_section_id = isset($args['section_id']) ? sanitize_html_class($args['section_id']) : 'quote';
?>

<section class="quote-section" id="<?php echo esc_attr($quote_section_id); ?>">

    <div class="container quote-wrapper">

        <!-- LEFT -->
        <div class="quote-left">

            <h2>
                Experience Innovation, Precision, and Trust in
                <span>Every Packaging Solution</span>
            </h2>

            <ul class="quote-list">

                <li>
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Expert Consultation Team</strong>
                        <p>Personalized guidance that fits perfectly.</p>
                    </div>
                </li>

                <li>
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Creative Design Perfection</strong>
                        <p>Turning great concepts into reality.</p>
                    </div>
                </li>

                <li>
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Premium Packaging Materials</strong>
                        <p>Built strong for beauty and durability.</p>
                    </div>
                </li>

                <li>
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Advanced Color Printing</strong>
                        <p>Delivering flawless color and clarity.</p>
                    </div>
                </li>

                <li>
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Fast Production Process</strong>
                        <p>Always ready when you need.</p>
                    </div>
                </li>

                <li>
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Global Export Support</strong>
                        <p>Export-ready packing for international buyers.</p>
                    </div>
                </li>

                <li>
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <strong>Eco-Friendly Commitment</strong>
                        <p>Sustainable recyclable packaging every time.</p>
                    </div>
                </li>

            </ul>

        </div>

        <!-- RIGHT FORM -->
        <div class="quote-form-box">

            <div class="form-header">
                Get Your Custom Box Quote
            </div>

            <?php if (isset($_GET['quote_status'])) : ?>
                <?php
                $quote_status = sanitize_text_field(wp_unslash($_GET['quote_status']));
                $quote_messages = array(
                    'success' => 'Thank you. Your quote request has been sent successfully.',
                    'failed'  => 'Sorry, we could not send your request right now. Please try again later.',
                    'missing' => 'Please fill in your name, email, and product name.',
                    'invalid' => 'The form session expired. Please refresh the page and try again.',
                    'file'    => 'Please upload a valid artwork file under 10MB.',
                    'captcha' => 'Please complete the security check correctly.',
                    'spam'    => 'Sorry, this request could not be accepted.',
                    'rate_limited' => 'Too many quote requests. Please wait a few minutes and try again.',
                );
                ?>
                <?php if (isset($quote_messages[$quote_status])) : ?>
                    <div class="quote-form-message quote-form-message-<?php echo esc_attr($quote_status); ?>">
                        <?php echo esc_html($quote_messages[$quote_status]); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <form class="quote-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="custom_box_quote_form">
                <input type="hidden" name="product_type" value="boxes">
                <?php wp_nonce_field('custom_box_quote_form', 'custom_box_quote_nonce'); ?>
                <input class="quote-hp" type="text" name="website_url" tabindex="-1" autocomplete="off" aria-hidden="true">

                <label>Product Name:</label>
                <input type="text" name="product_name" placeholder="Boxes" value="<?php echo esc_attr(function_exists('custom_box_quote_product_name') ? custom_box_quote_product_name() : ''); ?>" required>

                <label>Product Size:</label>
                <div class="form-row">
                    <input type="number" name="length" min="0" step="any" placeholder="Length">
                    <input type="number" name="width" min="0" step="any" placeholder="Width">
                    <input type="number" name="depth" min="0" step="any" placeholder="Depth">
                    <select name="unit" required>
                        <option value="">Units</option>
                        <option value="cm">CM</option>
                        <option value="mm">MM</option>
                        <option value="inch">Inch</option>
                    </select>
                </div>

                <label>More Information:</label>
                <div class="form-row">
                    <select name="stock_option">
                        <option value="">Stock Options</option>
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
                    <select name="printing_option">
                        <option value="">Printing Options</option>
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
                    <select name="finishing_option">
                        <option value="">Finishing Options</option>
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

                <label>Quantity:</label>
                <input type="number" name="quantity" placeholder="Quantity" min="1">

                <label>Upload Your Artwork:</label>
                <input type="file" name="artwork" accept=".png,.pdf,.jpg,.jpeg,.webp,.doc,.docx,.gif,.psd,.cdr,.eps">

                <label>Personal Information:</label>
                <input type="text" name="full_name" placeholder="Full Name" required>
                <div class="form-row">
                    <input type="text" name="phone" placeholder="Contact Number">
                    <input type="email" name="email" placeholder="Email" required>
                </div>

                <textarea name="message" placeholder="Additional Message"></textarea>

                <?php custom_box_quote_form_recaptcha_fields(); ?>

                <button type="submit" class="btn-primary">Submit Quote</button>

            </form>

            <script>
                (function() {
                    var forms = document.querySelectorAll('.quote-form');
                    var statusMessages = {
                        success: 'Thank you. Your quote request has been sent successfully.',
                        failed: 'Sorry, we could not send your request right now. Please try again later.',
                        missing: 'Please fill in your name, email, and product name.',
                        invalid: 'The form session expired. Please refresh the page and try again.',
                        file: 'Please upload a valid artwork file under 10MB.',
                        captcha: 'Please complete the security check correctly.',
                        spam: 'Sorry, this request could not be accepted.',
                        rate_limited: 'Too many quote requests. Please wait a few minutes and try again.'
                    };

                    function isRecaptchaComplete(form) {
                        var recaptcha = form.querySelector('.custom-box-recaptcha');
                        var response = form.querySelector('[name="g-recaptcha-response"]');

                        return !recaptcha || (response && response.value.trim() !== '');
                    }

                    function resetRecaptcha(form) {
                        var widget = form.querySelector('.g-recaptcha[data-widget-id]');

                        if (widget && window.grecaptcha && window.grecaptcha.reset) {
                            window.grecaptcha.reset(widget.dataset.widgetId);
                        }
                    }

                    forms.forEach(function(form, index) {
                        if (form.dataset.quoteIframeReady) {
                            return;
                        }

                        form.dataset.quoteIframeReady = '1';
                        var submitted = false;

                        var iframeName = 'custom_box_quote_submit_' + index + '_' + Date.now();
                        var iframe = document.createElement('iframe');
                        iframe.name = iframeName;
                        iframe.title = 'Quote form submission';
                        iframe.hidden = true;
                        iframe.style.display = 'none';
                        form.parentNode.appendChild(iframe);

                        form.addEventListener('submit', function(event) {
                            var button = form.querySelector('button[type="submit"]');
                            var message = form.parentNode.querySelector('.quote-form-message');

                            if (!message) {
                                message = document.createElement('div');
                                form.parentNode.insertBefore(message, form);
                            }

                            if (!isRecaptchaComplete(form)) {
                                event.preventDefault();
                                message.className = 'quote-form-message quote-form-message-captcha';
                                message.textContent = statusMessages.captcha;
                                return;
                            }

                            form.target = iframeName;
                            submitted = true;

                            message.className = 'quote-form-message quote-form-message-pending';
                            message.textContent = 'Sending your request...';

                            if (button) {
                                button.disabled = true;
                                button.textContent = 'Sending...';
                            }
                        });

                        iframe.addEventListener('load', function() {
                            var button = form.querySelector('button[type="submit"]');
                            var message = form.parentNode.querySelector('.quote-form-message');
                            var status = '';

                            if (!submitted) {
                                return;
                            }

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

                            if ('success' === status) {
                                if (message) {
                                    message.className = 'quote-form-message quote-form-message-success';
                                    message.textContent = statusMessages.success;
                                }
                                form.reset();
                                resetRecaptcha(form);
                            } else if (message) {
                                message.className = 'quote-form-message quote-form-message-' + status;
                                message.textContent = statusMessages[status] || statusMessages.failed;
                                if ('captcha' === status) {
                                    resetRecaptcha(form);
                                }
                            }

                            submitted = false;
                            if (button) {
                                button.disabled = false;
                                button.textContent = 'Submit Quote';
                            }
                        });
                    });
                })();
            </script>

        </div>

    </div>

</section>
