(function () {
    jQuery(window).on("load", function () {
        const email = document.getElementById("email");
        const listener_url = document.querySelector("#cr-billing-email-listener").value;

        email.addEventListener('focusout', function (event) {
            jQuery.post(listener_url,
                {'billing_email': event.target.value});
        });
    });
})();

(function () {
    jQuery(window).on("load", function () {
        let newsletter_status_field = jQuery("#crNewsletterStatusField").val();

        if (!newsletter_status_field) {
            return;
        }

        let subscribe_url = jQuery("#crSubscribeUrl").val(),
            undo_url = jQuery("#crUndoUrl").val(),
            subscription_checkbox = jQuery("input[name='" + newsletter_status_field + "']"),
            subscription_checkbox_label = subscription_checkbox.parent()[0],
            subscription_component = subscription_checkbox_label.parentElement,
            email_input = jQuery("#email"),
            email_component = email_input.parent().parent(),
            subscription_label = jQuery('#crSubscriptionConfirmationMessage'),
            undo_button = jQuery('#crUndo'),
            is_abandoned_cart_enabled = jQuery("#isAbandonedCartEnabled").val(),
            ac_time = jQuery("#acTime").val(),
            timeoutID = 0;

        email_component.append(subscription_component);
        email_component.append(subscription_label);
        subscription_checkbox_label.style.display = "flex";

        subscription_checkbox.change(function () {
            if (this.checked && is_abandoned_cart_enabled) {
                jQuery.post(subscribe_url,
                    {
                        'billing_email': email_input.val(),
                        'cr_status': this.checked
                    },
                    function (response) {
                        if (response.success) {
                            subscription_checkbox_label.style.display = "none";
                            subscription_label.show(100);

                            timeoutID = setTimeout(function () {
                                subscription_checkbox.prop('checked', false);
                                subscription_label.hide();
                            }, ac_time * 1000);
                        }
                    }
                );
            }
        });

        undo_button.click(function () {
            jQuery.post(undo_url, null,
                function (response) {
                    if (response.success) {
                        subscription_checkbox.prop('checked', false);
                        subscription_label.hide(100);
                        subscription_checkbox_label.style.display = "flex";
                        clearTimeout(timeoutID);
                    }
                }
            );

        });
    });
})();