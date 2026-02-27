/**
 * Subscription AutoPay Payment Handler
 * Handles Razorpay Subscription checkout for recurring payments.
 */
class SubscriptionPaymentHandler {
    constructor() {
        this.loaderEl = document.getElementById('paymentLoader');
    }

    showLoader() {
        if (this.loaderEl) this.loaderEl.style.display = 'block';
    }

    hideLoader() {
        if (this.loaderEl) this.loaderEl.style.display = 'none';
    }

    /**
     * Initiate AutoPay subscription via Razorpay Subscriptions API.
     */
    async initiateAutoPaySubscription(subscriptionId, userDetails) {
        try {
            if (!userDetails.name || !userDetails.email || !userDetails.number) {
                alert('Please fill all required fields');
                return false;
            }

            this.showLoader();

            const response = await fetch('/subscriptions/autopay/create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    subscription_id: subscriptionId,
                    name: userDetails.name,
                    email: userDetails.email,
                    number: userDetails.number,
                })
            });

            if (!response.ok) {
                const errorData = await response.json();
                throw new Error(errorData.error || 'Failed to create AutoPay subscription');
            }

            const data = await response.json();
            this.openRazorpaySubscriptionCheckout(data, userDetails);

        } catch (error) {
            console.error('AutoPay error:', error);
            alert(error.message || 'AutoPay setup failed. Please try again.');
            this.hideLoader();
        }
    }

    /**
     * Open Razorpay checkout in subscription mode.
     * Note: Razorpay Subscription checkout uses subscription_id instead of order_id.
     */
    openRazorpaySubscriptionCheckout(data, userDetails) {
        const self = this;

        const options = {
            key: data.key,
            subscription_id: data.razorpay_subscription_id,
            name: 'Asttrolok',
            description: 'Monthly Subscription - AutoPay',
            
            handler: function(response) {
                self.handleAutoPaySuccess(response, data.subscription_id);
            },

            prefill: {
                name: userDetails.name,
                email: userDetails.email,
                contact: userDetails.number
            },

            theme: {
                color: '#43d477'
            },

            modal: {
                ondismiss: function() {
                    self.hideLoader();
                    alert('AutoPay setup cancelled.');
                }
            }
        };

        const rzp = new Razorpay(options);
        rzp.open();
    }

    /**
     * After successful Razorpay subscription checkout, redirect to verify endpoint.
     */
    handleAutoPaySuccess(response, subscriptionId) {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '/subscriptions/autopay/verify';

        const fields = {
            'razorpay_payment_id': response.razorpay_payment_id,
            'razorpay_subscription_id': response.razorpay_subscription_id,
            'razorpay_signature': response.razorpay_signature,
            'subscription_id': subscriptionId
        };

        for (const [key, value] of Object.entries(fields)) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
    }
}

window.subscriptionPaymentHandler = new SubscriptionPaymentHandler();

function initiateAutoPaySubscription(subscriptionId, userDetails) {
    return window.subscriptionPaymentHandler.initiateAutoPaySubscription(subscriptionId, userDetails);
}
