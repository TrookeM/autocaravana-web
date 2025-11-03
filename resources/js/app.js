import './bootstrap';
import flatpickr from "flatpickr";
import { Spanish } from "flatpickr/dist/l10n/es";
import "flatpickr/dist/flatpickr.min.css";
import Alpine from 'alpinejs';
window.Alpine = Alpine;

// Configuración de Flatpickr
flatpickr.setDefaults({
    locale: Spanish,
    disableMobile: true
});
window.flatpickr = flatpickr;

document.addEventListener('alpine:init', () => {

    // LÓGICA 1: CALENDARIO
    Alpine.data('calendar', ({ unavailableDates, maintenanceDates, campervanId, pricePerNight }) => ({
        dates: { checkIn: null, checkOut: null },
        hoverDate: null,
        unavailableDates: new Set(unavailableDates),
        maintenanceDates: new Set(maintenanceDates),
        pricePerNight: pricePerNight,
        campervanId: campervanId,
        errorMessage: '',
        isSubmitting: false,
        totalPrice: 0,
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

        get nightsCount() {
            if (!this.dates.checkIn || !this.dates.checkOut) return 0;
            const start = new Date(this.dates.checkIn);
            const end = new Date(this.dates.checkOut);
            const diffTime = Math.abs(end - start);
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        },
        get isRangeValid() {
            return this.dates.checkIn && this.dates.checkOut && this.nightsCount >= 1 && !this.errorMessage;
        },
        async fetchPrice() {
            this.totalPrice = 0;
            if (!this.dates.checkIn || !this.dates.checkOut || this.errorMessage) {
                return;
            }
            try {
                const response = await fetch('/api/calculate-price', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrfToken,
                    },
                    body: JSON.stringify({
                        campervan_id: this.campervanId,
                        start_date: this.dates.checkIn,
                        end_date: this.dates.checkOut
                    })
                });
                const data = await response.json();
                if (response.ok) {
                    this.totalPrice = data.total_price;
                } else {
                    this.errorMessage = 'Error al calcular el precio. Inténtelo de nuevo.';
                    this.totalPrice = 0;
                }
            } catch (e) {
                console.error('Error de conexión:', e);
                this.errorMessage = 'Fallo en la conexión para calcular el precio.';
                this.totalPrice = 0;
            }
        },
        selectDate(date) {
            if (this.dates.checkIn && this.dates.checkOut) {
                this.dates.checkIn = date;
                this.dates.checkOut = null;
                this.errorMessage = '';
                this.totalPrice = 0;
                return;
            }
            if (!this.dates.checkIn) {
                this.dates.checkIn = date;
                this.errorMessage = '';
                return;
            }
            if (date > this.dates.checkIn) {
                this.dates.checkOut = date;
                this.validateRange();
                if (this.isRangeValid) {
                    this.fetchPrice();
                }
            } else {
                this.dates.checkIn = date;
                this.dates.checkOut = null;
                this.errorMessage = '';
                this.totalPrice = 0;
            }
        },
        validateRange() {
            this.errorMessage = '';
            if (!this.dates.checkIn || !this.dates.checkOut) return;
            const start = new Date(this.dates.checkIn);
            const end = new Date(this.dates.checkOut);
            if (end <= start) {
                this.errorMessage = 'La fecha de salida debe ser posterior a la de entrada';
                this.dates.checkOut = null;
                this.totalPrice = 0;
                return;
            }
            let current = new Date(start);
            while (current < end) {
                const dateStr = current.toISOString().split('T')[0];
                if (this.unavailableDates.has(dateStr) || this.maintenanceDates.has(dateStr)) {
                    this.errorMessage = 'Algunas fechas seleccionadas no están disponibles';
                    this.dates.checkIn = null;
                    this.dates.checkOut = null;
                    this.totalPrice = 0;
                    return;
                }
                current.setDate(current.getDate() + 1);
            }
        },
        isDateInRange(date) {
            if (!this.dates.checkIn || !this.dates.checkOut) return false;
            const testDate = new Date(date);
            const start = new Date(this.dates.checkIn);
            const end = new Date(this.dates.checkOut);
            return testDate > start && testDate < end;
        },
        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            return date.toLocaleDateString('es-ES', { day: 'numeric', month: 'long', year: 'numeric' });
        },
        submitBooking() {
            if (!this.isRangeValid || this.isSubmitting || this.totalPrice <= 0) return;
            this.isSubmitting = true;
            const params = new URLSearchParams({
                campervan_id: this.campervanId,
                start_date: this.dates.checkIn,
                end_date: this.dates.checkOut,
                total_price: this.totalPrice
            });
            setTimeout(() => {
                window.location.href = `/booking/create?${params.toString()}`;
            }, 300);
        },
        isMaintenanceDate(dateString) {
            return this.maintenanceDates.has(dateString);
        }
    }));

    // LÓGICA 2: CUPÓN DEL CHECKOUT - SIMPLE Y FUNCIONAL
    Alpine.data('couponLogic', () => ({
        // PROPIEDADES INICIALES (Variables de estado)
        basePrice: 0,
        nights: 0,
        extrasData: {},
        selectedExtras: [],
        depositPercentage: 0.3,
        dueDate: '',

        // RUTAS
        routeApply: '',
        routeRemove: '',
        
        // ESTADO REACTIVO DEL CUPÓN
        couponCodeInput: '',
        couponDiscount: 0,
        couponCodeApplied: '',
        couponSuccess: '',
        couponError: '',

        // FUNCIÓN DE INICIALIZACIÓN (Lee los data-*)
        initData() {
            const el = this.$el;
            
            this.basePrice = parseFloat(el.dataset.basePrice || 0);
            this.nights = parseInt(el.dataset.nights || 0);
            this.extrasData = JSON.parse(el.dataset.extras || '{}');
            this.selectedExtras = JSON.parse(el.dataset.oldExtras || '[]');
            this.depositPercentage = parseFloat(el.dataset.depositPercentage || 0.3);
            this.dueDate = el.dataset.dueDate || '';

            // Rutas
            this.routeApply = el.dataset.routeApply;
            this.routeRemove = el.dataset.routeRemove;

            // Datos de Sesión
            this.couponDiscount = parseFloat(el.dataset.sessionDiscount || 0);
            this.couponCodeInput = el.dataset.sessionCode || '';
            this.couponCodeApplied = el.dataset.sessionCode || '';
            this.couponSuccess = el.dataset.sessionSuccess || '';
            this.couponError = el.dataset.sessionError || '';
        },

        // 1. FUNCIONES DE CÁLCULO
        calculateExtrasCost() {
            let cost = 0;
            if (!this.selectedExtras) return 0;
            
            this.selectedExtras.forEach(id => {
                const extra = this.extrasData[id];
                if (extra) {
                    let precio = parseFloat(extra.precio);
                    cost += extra.es_por_dia ? (precio * this.nights) : precio;
                }
            });
            return cost;
        },

        // 2. PROPIEDADES GETTER
        get totalConExtras() {
            return this.basePrice + this.calculateExtrasCost();
        },
        get finalPriceWithCoupon() {
            let final = this.totalConExtras - this.couponDiscount;
            return Math.max(0, final);
        },
        get finalDepositAmount() {
            return this.finalPriceWithCoupon * this.depositPercentage;
        },
        get finalRemainingAmount() {
            return this.finalPriceWithCoupon - this.finalDepositAmount;
        },

        // 3. FUNCIONES DE ACCIÓN (AJAX)
        applyCoupon() {
            this.couponError = '';
            this.couponSuccess = '';
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (!this.couponCodeInput) {
                this.couponError = 'El código de cupón es obligatorio.';
                return;
            }

            fetch(this.routeApply, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ 
                    code: this.couponCodeInput,
                    price_for_coupon: this.totalConExtras.toFixed(2), 
                    extras: this.selectedExtras
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    this.couponError = data.error;
                    this.couponDiscount = 0;
                    this.couponCodeApplied = '';
                } else {
                    this.couponDiscount = parseFloat(data.discount_amount);
                    this.couponSuccess = data.message;
                    this.couponCodeApplied = data.coupon_code;
                    this.couponCodeInput = ''; // Limpiamos el campo después de aplicar
                }
            })
            .catch(error => {
                this.couponError = 'Error de conexión al aplicar el cupón.';
                console.error('Error:', error);
            });
        },
        
        removeCoupon() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(this.routeRemove, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            })
            .then(() => {
                this.couponCodeInput = '';
                this.couponDiscount = 0;
                this.couponCodeApplied = '';
                this.couponSuccess = '';
                this.couponError = '';
            })
            .catch(error => {
                this.couponError = 'Error al eliminar el cupón.';
                console.error('Error:', error);
            });
        },

        // 4. FUNCIÓN PARA MANEJAR CAMBIOS EN EXTRAS
        handleExtraChange() {
            // Cuando cambian los extras, quitamos el cupón aplicado
            if (this.couponCodeApplied) {
                this.couponSuccess = 'Los extras han cambiado. Por favor, aplica el cupón nuevamente si lo deseas.';
                this.couponDiscount = 0;
                this.couponCodeApplied = '';
                this.couponCodeInput = '';
            }
        },

        // 5. FUNCIÓN PARA LIMPIAR CAMPO MANUALMENTE
        clearField() {
            this.couponCodeInput = '';
        }
    }));
});

// INICIAR ALPINE
Alpine.start();