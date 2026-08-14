(function () {
    'use strict';

    var page = document.querySelector('.cpb-page');
    var form = document.getElementById('custom-paper-bags-quote-form');
    var quickForm = document.querySelector('.cpb-quick-form');
    var storageKey = 'vpn_custom_paper_bags_quote_draft_v1';
    var successKey = 'vpn_custom_paper_bags_success_v1_' + window.location.pathname;
    var started = false;

    function pushEvent(name, params) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push(Object.assign({ event: name }, params || {}));
    }

    function eventLocation(element) {
        var parent = element.closest('header, section, footer, .cpb-mobile-bar');
        return parent ? (parent.id || parent.className.split(' ')[0]) : 'unknown';
    }

    document.querySelectorAll('[data-track]').forEach(function (element) {
        element.addEventListener('click', function () {
            var eventName = element.getAttribute('data-track');
            pushEvent(eventName, {
                cta_location: eventLocation(element),
                product_category: 'paper_bags'
            });
        });
    });

    function safeStorage(method, key, value) {
        try {
            if (method === 'get') {
                return window.sessionStorage.getItem(key);
            }
            if (method === 'remove') {
                window.sessionStorage.removeItem(key);
                return null;
            }
            window.sessionStorage.setItem(key, value);
        } catch (error) {
            return null;
        }
        return value;
    }

    function fillAttributionFields() {
        if (!form) {
            return;
        }

        var params = new URLSearchParams(window.location.search);
        ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'gclid', 'gbraid', 'wbraid'].forEach(function (key) {
            var incoming = params.get(key);
            if (incoming) {
                safeStorage('set', 'vpn_custom_paper_bags_' + key, incoming.slice(0, 180));
            }

            var stored = safeStorage('get', 'vpn_custom_paper_bags_' + key);
            var field = form.elements[key];
            if (field && stored) {
                field.value = stored;
            }
        });

        if (form.elements.referrer_url && document.referrer) {
            form.elements.referrer_url.value = document.referrer.slice(0, 500);
        }
    }

    function saveDraft() {
        if (!form) {
            return;
        }

        var draft = {};
        Array.prototype.forEach.call(form.elements, function (field) {
            if (!field.name || field.type === 'file' || field.name.indexOf('nonce') !== -1 || field.name.indexOf('signature') !== -1) {
                return;
            }
            draft[field.name] = field.type === 'checkbox' ? field.checked : field.value;
        });
        safeStorage('set', storageKey, JSON.stringify(draft));
    }

    function restoreDraft() {
        if (!form || (page && page.getAttribute('data-quote-success') === '1')) {
            return;
        }

        var stored = safeStorage('get', storageKey);
        if (!stored) {
            return;
        }

        try {
            var draft = JSON.parse(stored);
            Object.keys(draft).forEach(function (name) {
                var field = form.elements[name];
                if (!field || field.type === 'file') {
                    return;
                }
                if (field.type === 'checkbox') {
                    field.checked = Boolean(draft[name]);
                } else if (!field.value && draft[name]) {
                    field.value = draft[name];
                }
            });
        } catch (error) {
            safeStorage('remove', storageKey);
        }
    }

    function showValidationErrors(invalidFields, customMessage) {
        if (!form) {
            return;
        }

        var summary = document.getElementById('cpb-form-errors');
        if (!summary) {
            return;
        }

        summary.innerHTML = '<strong>' + (customMessage || 'Please review the following fields:') + '</strong>';
        if (invalidFields.length) {
            var list = document.createElement('ul');
            invalidFields.forEach(function (field) {
                field.setAttribute('aria-invalid', 'true');
                var label = form.querySelector('label[for="' + field.id + '"]');
                var item = document.createElement('li');
                var link = document.createElement('a');
                link.href = '#' + field.id;
                link.textContent = (label ? label.textContent.replace('*', '').trim() : field.name) + ': ' + (field.validationMessage || 'Please complete this field.');
                item.appendChild(link);
                list.appendChild(item);
            });
            summary.appendChild(list);
        }
        summary.hidden = false;
        summary.focus();
    }

    document.querySelectorAll('[data-bag-type]').forEach(function (link) {
        link.addEventListener('click', function () {
            if (!form || !form.elements.product_name) {
                return;
            }
            form.elements.product_name.value = link.getAttribute('data-bag-type') || '';
        });
    });

    if (form) {
        fillAttributionFields();
        restoreDraft();

        form.addEventListener('focusin', function () {
            if (!started) {
                started = true;
                safeStorage('remove', successKey);
                pushEvent('paper_bag_form_start', { form_name: 'custom_paper_bags_quote' });
            }
        });

        form.addEventListener('input', function (event) {
            event.target.removeAttribute('aria-invalid');
            saveDraft();
        });
        form.addEventListener('change', function (event) {
            saveDraft();
            if (event.target.type === 'file' && event.target.files && event.target.files.length) {
                var file = event.target.files[0];
                pushEvent('paper_bag_file_upload', {
                    form_name: 'custom_paper_bags_quote',
                    file_type: file.name.split('.').pop().toLowerCase(),
                    file_size_bucket: file.size > 5242880 ? '5mb_plus' : 'under_5mb'
                });
            }
        });

        form.addEventListener('submit', function (event) {
            fillAttributionFields();
            var invalidFields = Array.prototype.filter.call(form.querySelectorAll('input, select, textarea'), function (field) {
                return !field.checkValidity();
            });
            var fileField = form.elements.artwork;
            var fileError = '';
            if (fileField && fileField.files && fileField.files.length) {
                var file = fileField.files[0];
                var extension = file.name.split('.').pop().toLowerCase();
                if (file.size > 10 * 1024 * 1024 || ['png', 'pdf', 'jpg', 'jpeg', 'webp', 'doc', 'docx', 'gif', 'psd', 'cdr', 'eps'].indexOf(extension) === -1) {
                    fileError = 'Please upload a supported artwork file no larger than 10MB.';
                    invalidFields.push(fileField);
                }
            }

            if (invalidFields.length || fileError) {
                event.preventDefault();
                event.stopPropagation();
                showValidationErrors(invalidFields, fileError || null);
                invalidFields[0].focus();
                return;
            }

            saveDraft();
            form.classList.add('is-submitting');
            form.setAttribute('aria-busy', 'true');
            var submitButton = form.querySelector('[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Sending…';
            }
        });
    }

    if (quickForm && !quickForm.dataset.quickQuoteReady) {
        quickForm.dataset.quickQuoteReady = '1';
        var quickIframeName = 'cpb_quick_quote_' + Date.now();
        var quickIframe = document.createElement('iframe');
        var quickSubmitted = false;
        var quickButton = quickForm.querySelector('button[type="submit"]');
        var quickMessage = quickForm.parentNode.querySelector('.cpb-quick-message');

        quickIframe.name = quickIframeName;
        quickIframe.hidden = true;
        quickIframe.style.display = 'none';
        quickIframe.title = 'Quick quote submission';
        quickForm.parentNode.appendChild(quickIframe);

        quickForm.addEventListener('submit', function (event) {
            event.preventDefault();
            quickForm.target = quickIframeName;
            quickSubmitted = true;

            if (quickMessage) {
                quickMessage.hidden = false;
                quickMessage.className = 'cpb-quick-message cpb-quick-message-pending';
                quickMessage.textContent = 'Sending your request...';
            }
            if (quickButton) {
                quickButton.disabled = true;
            }

            HTMLFormElement.prototype.submit.call(quickForm);
        });

        quickIframe.addEventListener('load', function () {
            var status = '';
            if (!quickSubmitted) {
                return;
            }

            try {
                status = new URL(quickIframe.contentWindow.location.href).searchParams.get('quote_status') || '';
            } catch (error) {
                status = '';
            }

            if (
                'success' === status &&
                !window.__vpnGoogleAdsQuoteConversionSent &&
                typeof window.gtag === 'function'
            ) {
                window.gtag('event', 'conversion', {
                    send_to: 'AW-18190091085/6FzwCNKm0NscEM2G2-FD'
                });
                window.__vpnGoogleAdsQuoteConversionSent = true;
            }

            if (quickMessage) {
                quickMessage.hidden = false;
                quickMessage.className = 'cpb-quick-message cpb-quick-message-' + (status || 'failed');
                quickMessage.textContent = 'success' === status
                    ? 'Thank you. Your quote request has been sent successfully.'
                    : ('captcha' === status
                        ? 'Security verification could not be completed. Please reload the page and try again.'
                        : 'Sorry, we could not send your request right now. Please try again later.');
            }

            quickSubmitted = false;
            if (quickButton) {
                quickButton.disabled = false;
            }
        });
    }

    if (page && page.getAttribute('data-quote-success') === '1' && !safeStorage('get', successKey)) {
        pushEvent('paper_bag_quote_submit_success', { form_name: 'custom_paper_bags_quote', product_category: 'paper_bags' });
        safeStorage('set', successKey, '1');
        safeStorage('remove', storageKey);
    }
}());
