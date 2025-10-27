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
    // MODIFICACIÓN 1: Añadir 'maintenanceDates' a los parámetros que se reciben
    Alpine.data('calendar', ({ unavailableDates, maintenanceDates, campervanId, pricePerNight }) => ({
        dates: {
            checkIn: null,
            checkOut: null
        },
        hoverDate: null,
        unavailableDates: new Set(unavailableDates),
        // MODIFICACIÓN 2: Guardar las fechas de mantenimiento en un Set
        maintenanceDates: new Set(maintenanceDates),

        pricePerNight: pricePerNight,
        campervanId: campervanId,
        errorMessage: '',
        isSubmitting: false,

        // PROPIEDADES NUEVAS/MODIFICADAS
        totalPrice: 0,
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

        // Propiedades calculadas
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


        // MÉTODOS MODIFICADOS
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

            // Verificar disponibilidad en el rango
            let current = new Date(start);
            while (current < end) {
                const dateStr = current.toISOString().split('T')[0];

                // MODIFICACIÓN 3: Comprobar también 'maintenanceDates'
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
            return date.toLocaleDateString('es-ES', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
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
            // MODIFICACIÓN 4: Usar el Set.has() en lugar de Array.includes()
            return this.maintenanceDates.has(dateString);
        }
    }));
});
