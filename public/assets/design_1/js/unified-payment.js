class UnifiedPaymentHandler {
    constructor() {
        this.loader = document.getElementById('loader');
    }

    async initiatePayment(paymentType, itemId, userDetails) {
        try {
            if (!this.validateInputs(userDetails)) {
                return false;
            }

            // this.showLoader();

            const response = await fetch('/payments/initiate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    payment_type: paymentType,
                    item_id: itemId,
                    name: userDetails.name,
                    email: userDetails.email,
                    number: userDetails.number,
                    discount_id: userDetails.discount_id || null,
                    installment_id: userDetails.installment_id || null,
                    Country: userDetails.Country || null,
                    StateProvince: userDetails.StateProvince || null,
                    City: userDetails.City || null,
                    pin_code: userDetails.pin_code || null,
                    address: userDetails.address || null,
                    message: userDetails.message || null,
                    amount: userDetails.amount || null,
                    selectedDay: userDetails.selectedDay || null
                })
            });

            
            // console.log(response);
            
            if (!response.ok) {
                throw new Error('Failed to initiate payment');
            }

            const data = await response.json();
            this.openRazorpayCheckout(data, userDetails);

        } catch (error) {
            console.error('Payment error:', error);
            alert('Payment failed. Please try again.');
            this.hideLoader();
        }
    }

    openRazorpayCheckout(orderData, userDetails) {
        const options = {
            key: orderData.key,
            amount: orderData.amount,
            currency: orderData.currency,
            name: 'Asttrolok',
            order_id: orderData.razorpay_order_id,
            
            handler: (response) => {
                this.handleSuccess(response, orderData.order_id);
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
                ondismiss: () => {
                    this.hideLoader();
                    alert('Payment cancelled');
                }
            }
        };

        const rzp = new Razorpay(options);
        rzp.open();
    }

    handleSuccess(response, orderId) {
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '/payments/callback';

        const fields = {
            'razorpay_payment_id': response.razorpay_payment_id,
            'razorpay_order_id': response.razorpay_order_id,
            'razorpay_signature': response.razorpay_signature,
            'order_id': orderId
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

    validateInputs(userDetails) {
        if (!userDetails.name || !userDetails.email || !userDetails.number) {
            alert('Please fill all required fields');
            return false;
        }
        return true;
    }

    showLoader() {
        if (this.loader) this.loader.style.display = 'block';
        document.body.classList.add('disabled-page');
    }

    hideLoader() {
        if (this.loader) this.loader.style.display = 'none';
        // Also hide the paymentLoader overlay used in buyNow pages
        const paymentLoader = document.getElementById('paymentLoader');
        if (paymentLoader) paymentLoader.style.display = 'none';
        document.body.classList.remove('disabled-page');
    }
}

window.paymentHandler = new UnifiedPaymentHandler();

function initiatePayment(type, itemId, userDetails) {
    return window.paymentHandler.initiatePayment(type, itemId, userDetails);
}