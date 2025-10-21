import './bootstrap';
import flatpickr from "flatpickr";
import { Spanish } from "flatpickr/dist/l10n/es";
import "flatpickr/dist/flatpickr.min.css";

flatpickr.setDefaults({
    locale: Spanish,
    disableMobile: true
});

window.flatpickr = flatpickr;

document.addEventListener('alpine:init', () => {
    Alpine.data('calendar', ({ unavailableDates, campervanId, pricePerNight }) => ({
        dates: {
            checkIn: null,
            checkOut: null
        },
        hoverDate: null,
        unavailableDates: new Set(unavailableDates),
        pricePerNight: pricePerNight,
        campervanId: campervanId,
        errorMessage: '',
        isSubmitting: false,

        // Propiedades calculadas
        get nightsCount() {
            if (!this.dates.checkIn || !this.dates.checkOut) return 0;
            const start = new Date(this.dates.checkIn);
            const end = new Date(this.dates.checkOut);
            const diffTime = Math.abs(end - start);
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        },

        get totalPrice() {
            return this.nightsCount * this.pricePerNight;
        },

        get isRangeValid() {
            return this.dates.checkIn && this.dates.checkOut && this.nightsCount >= 1 && !this.errorMessage;
        },

        // Métodos simplificados
        selectDate(date) {
            if (this.dates.checkIn && this.dates.checkOut) {
                // Reset si ya hay un rango completo
                this.dates.checkIn = date;
                this.dates.checkOut = null;
                this.errorMessage = '';
                return;
            }

            if (!this.dates.checkIn) {
                // Primera selección
                this.dates.checkIn = date;
                this.errorMessage = '';
                return;
            }

            // Segunda selección
            if (date > this.dates.checkIn) {
                this.dates.checkOut = date;
                this.validateRange();
            } else {
                // Si selecciona una fecha anterior, la hace checkIn
                this.dates.checkIn = date;
                this.dates.checkOut = null;
                this.errorMessage = '';
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
                return;
            }

            // Verificar disponibilidad en el rango
            let current = new Date(start);
            while (current < end) {
                const dateStr = current.toISOString().split('T')[0];
                if (this.unavailableDates.has(dateStr)) {
                    this.errorMessage = 'Algunas fechas seleccionadas no están disponibles';
                    this.dates.checkIn = null;
                    this.dates.checkOut = null;
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
            return date.toLocaleDateString('es-ES', { 
                day: 'numeric', 
                month: 'long', 
                year: 'numeric' 
            });
        },

        submitBooking() {
            if (!this.isRangeValid || this.isSubmitting) return;

            this.isSubmitting = true;

            const params = new URLSearchParams({
                campervan_id: this.campervanId,
                start_date: this.dates.checkIn,
                end_date: this.dates.checkOut,
                total_price: this.totalPrice
            });

            // Redirigir después de un breve delay
            setTimeout(() => {
                window.location.href = `/booking/create?${params.toString()}`;
            }, 300);
        }
    }));
});