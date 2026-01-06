document.addEventListener('DOMContentLoaded', () => {

    // --- STATE MANAGEMENT ---
    const state = {
        slug: null,
        barbershopName: 'Sua Barbearia',
        services: [],
        availability: [],
        selectedService: null,
        selectedDate: null,
        selectedSlot: null,
        clientDetails: {},
        paymentMethod: null,
    };

    // --- DOM ELEMENTS ---
    const dom = {
        barbershopName: document.getElementById('barbershop-name'),
        barbershopAddress: document.getElementById('barbershop-address'),
        steps: {
            service: document.getElementById('step-service'),
            datetime: document.getElementById('step-datetime'),
            details: document.getElementById('step-details'),
            checkout: document.getElementById('step-checkout'),
            success: document.getElementById('step-success'),
        },
        serviceList: document.getElementById('service-list'),
        datePicker: document.getElementById('date-picker'),
        availabilityList: document.getElementById('availability-list'),
        clientForm: document.getElementById('client-form'),
        submitDetailsButton: document.getElementById('submit-details-button'),
        bookingSummary: document.getElementById('booking-summary'),
        paymentOptions: document.querySelectorAll('.payment-option'),
        pixInstructions: document.getElementById('pix-instructions'),
        pixKey: document.getElementById('pix-key'),
        pixWhatsappLink: document.getElementById('pix-whatsapp-link'),
        confirmBookingButton: document.getElementById('confirm-booking-button'),
        successSummary: document.getElementById('success-summary'),
        newBookingButton: document.getElementById('new-booking-button'),
        backButtons: document.querySelectorAll('.back-button'),
        notificationContainer: document.getElementById('notification-container'),
    };

    // --- API SERVICE ---
    const api = {
        baseUrl: '../backend/api.php', 
        
        async getShopAndServices(slug) {
            const response = await fetch(`${this.baseUrl}/public/services?slug=${slug}`);
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Barbearia não encontrada ou indisponível.');
            }
            return await response.json();
        },

        async getAvailability(slug, serviceId, date) {
            const response = await fetch(`${this.baseUrl}/public/availability?slug=${slug}&serviceId=${serviceId}&date=${date}`);
            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.error || 'Não foi possível carregar a disponibilidade.');
            }
            return await response.json();
        },

        async createAppointment(slug, data) {
            const response = await fetch(`${this.baseUrl}/public/appointments?slug=${slug}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.error || 'Erro desconhecido ao criar agendamento.');
            }
            return result;
        }
    };

    // --- UI FUNCTIONS ---
    const navigateTo = (stepName) => {
        Object.values(dom.steps).forEach(step => step.classList.add('hidden'));
        if (dom.steps[stepName]) {
            dom.steps[stepName].classList.remove('hidden');
            window.scrollTo(0, 0);
        }
    };
    
    const showNotification = (message, type = 'error') => {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        dom.notificationContainer.appendChild(toast);

        setTimeout(() => {
            toast.remove();
        }, 4000);
    };

    // --- RENDER FUNCTIONS ---
    const renderServices = () => {
        dom.serviceList.innerHTML = '';
        if (state.services.length === 0) {
            dom.serviceList.innerHTML = '<p>Nenhum serviço disponível para agendamento online.</p>';
            return;
        }
        state.services.forEach(service => {
            const item = document.createElement('div');
            item.className = 'service-item';
            item.dataset.id = service.id;
            item.innerHTML = `<h3>${service.name}</h3><div class="details"><span>Duração: ${service.duration_minutes} min</span><span>Preço: R$ ${parseFloat(service.price).toFixed(2).replace('.', ',')}</span></div>`;
            item.addEventListener('click', () => handleServiceSelect(service));
            dom.serviceList.appendChild(item);
        });
    };

    const renderAvailability = () => {
        dom.availabilityList.innerHTML = '';
        if (state.availability.length === 0) {
            dom.availabilityList.innerHTML = '<p style="text-align: center; padding: 20px;">Nenhum horário disponível para esta data.</p>';
            return;
        }
        const slotsByBarber = state.availability.reduce((acc, slot) => {
            (acc[slot.barber_name] = acc[slot.barber_name] || []).push(slot);
            return acc;
        }, {});
        for (const barberName in slotsByBarber) {
            const barberSlots = slotsByBarber[barberName];
            let slotsHTML = `<h4>${barberName}</h4><div class="time-slots-grid">`;
            barberSlots.forEach(slot => {
                slotsHTML += `<div class="time-slot" data-slot='${JSON.stringify(slot)}'>${slot.time}</div>`;
            });
            slotsHTML += '</div>';
            dom.availabilityList.innerHTML += slotsHTML;
        }
    };
    
    const updateCheckoutSummary = () => {
        if (!state.selectedService || !state.selectedDate || !state.selectedSlot) return;
        const summaryHtml = `<p><strong>Serviço:</strong> <span>${state.selectedService.name}</span></p><p><strong>Data:</strong> <span>${new Date(state.selectedDate + 'T00:00:00').toLocaleDateString('pt-BR')}</span></p><p><strong>Hora:</strong> <span>${state.selectedSlot.time}</span></p><p><strong>Barbeiro:</strong> <span>${state.selectedSlot.barber_name}</span></p><hr style="border-color: rgba(255,255,255,0.1); margin: 12px 0;"><p style="font-size: 1.1em;"><strong>Valor:</strong> <span>R$ ${parseFloat(state.selectedService.price).toFixed(2).replace('.', ',')}</span></p>`;
        dom.bookingSummary.innerHTML = summaryHtml;
    };


    // --- EVENT HANDLERS ---
    const handleServiceSelect = (service) => {
        state.selectedService = service;
        document.querySelectorAll('.service-item').forEach(el => el.classList.remove('selected'));
        document.querySelector(`.service-item[data-id='${service.id}']`).classList.add('selected');
        const today = new Date().toISOString().split('T')[0];
        dom.datePicker.setAttribute('min', today);
        if (!dom.datePicker.value || dom.datePicker.value < today) {
             dom.datePicker.value = today;
        }
        state.selectedDate = dom.datePicker.value;
        loadAvailability();
        navigateTo('datetime');
    };

    const handleTimeSelect = (e) => {
        if (e.target.classList.contains('time-slot')) {
            state.selectedSlot = JSON.parse(e.target.dataset.slot);
            document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('selected'));
            e.target.classList.add('selected');
            navigateTo('details');
        }
    };

    const handleDetailsSubmit = (e) => {
        e.preventDefault();
        if(!dom.clientForm.reportValidity()) return;
        state.clientDetails = {
            name: dom.clientForm['client-name'].value,
            phone: dom.clientForm['client-phone'].value,
            email: dom.clientForm['client-email'].value,
        };
        updateCheckoutSummary();
        navigateTo('checkout');
    };
    
    const handlePaymentSelect = (e) => {
        const selectedOption = e.target.closest('.payment-option');
        if (!selectedOption) return;
        state.paymentMethod = selectedOption.dataset.method;
        dom.paymentOptions.forEach(opt => opt.classList.remove('selected'));
        selectedOption.classList.add('selected');
        if (state.paymentMethod === 'pix' && state.selectedSlot) {
            const pixKey = state.selectedSlot.barber_pix_key;
            const whatsapp = state.selectedSlot.barber_whatsapp;
            if (pixKey && whatsapp) {
                dom.pixKey.textContent = pixKey;
                dom.pixWhatsappLink.href = `https://wa.me/${whatsapp.replace(/\D/g, '')}`;
                dom.pixInstructions.classList.remove('hidden');
            } else {
                 dom.pixInstructions.innerHTML = '<p>O PIX não está configurado para este barbeiro.</p>';
                 dom.pixInstructions.classList.remove('hidden');
            }
        } else {
            dom.pixInstructions.classList.add('hidden');
        }
    };

    const handleConfirmBooking = async () => {
        if(!state.paymentMethod) {
            showNotification('Por favor, selecione uma forma de pagamento.');
            return;
        }
        const appointmentData = {
            name: state.clientDetails.name,
            phone: state.clientDetails.phone,
            email: state.clientDetails.email,
            serviceId: state.selectedService.id,
            barberId: state.selectedSlot.barber_id,
            dateTime: `${state.selectedDate} ${state.selectedSlot.time}:00`,
            payment_method: state.paymentMethod,
        };
        try {
            dom.confirmBookingButton.disabled = true;
            dom.confirmBookingButton.textContent = 'Agendando...';
            const result = await api.createAppointment(state.slug, appointmentData);
            if (result.success) {
                dom.successSummary.innerHTML = dom.bookingSummary.innerHTML;
                navigateTo('success');
            }
        } catch (error) {
            showNotification(`Erro ao agendar: ${error.message}`);
        } finally {
            dom.confirmBookingButton.disabled = false;
            dom.confirmBookingButton.textContent = 'Finalizar Agendamento';
        }
    };

    const loadAvailability = async () => {
        if (!state.slug || !state.selectedService || !state.selectedDate) return;
        dom.availabilityList.innerHTML = '<div class="loader"></div>';
        try {
            state.availability = await api.getAvailability(state.slug, state.selectedService.id, state.selectedDate);
            renderAvailability();
        } catch (error) {
            dom.availabilityList.innerHTML = `<p style="text-align: center; padding: 20px;">${error.message}</p>`;
        }
    };

    const resetBooking = () => {
        Object.assign(state, { selectedService: null, selectedDate: null, selectedSlot: null, clientDetails: {}, paymentMethod: null });
        dom.clientForm.reset();
        document.querySelectorAll('.selected').forEach(el => el.classList.remove('selected'));
        dom.pixInstructions.classList.add('hidden');
        navigateTo('service');
    };

    // --- INITIALIZATION ---
    const init = async () => {
        const params = new URLSearchParams(window.location.search);
        state.slug = params.get('slug');
        if (!state.slug) {
            document.querySelector('main').innerHTML = '<h2 style="text-align: center;">Barbearia não encontrada.</h2><p style="text-align: center;">Verifique se o link está correto. Ex: ...?slug=minha-barbearia</p>';
            return;
        }
        try {
            state.services = await api.getShopAndServices(state.slug);
            state.barbershopName = state.slug.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            dom.barbershopName.textContent = state.barbershopName;
            dom.barbershopAddress.textContent = 'Agendamento Online'; 
            renderServices();
            navigateTo('service');
        } catch (error) {
            document.querySelector('main').innerHTML = `<h2 style="text-align: center;">Não foi possível carregar a barbearia</h2><p style="text-align: center; color: var(--text-secondary);">${error.message}</p>`;
        }
    };

    // --- EVENT LISTENERS BINDING ---
    dom.datePicker.addEventListener('change', (e) => {
        state.selectedDate = e.target.value;
        loadAvailability();
    });
    dom.availabilityList.addEventListener('click', handleTimeSelect);
    dom.submitDetailsButton.addEventListener('click', handleDetailsSubmit);
    dom.paymentOptions.forEach(opt => opt.addEventListener('click', handlePaymentSelect));
    dom.confirmBookingButton.addEventListener('click', handleConfirmBooking);
    dom.newBookingButton.addEventListener('click', resetBooking);
    dom.backButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const targetStep = button.dataset.step;
            navigateTo(targetStep);
        });
    });

    init();
});
