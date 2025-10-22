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
        
        // Mantener pricePerNight como base (aunque no se usa para el cálculo final)
        pricePerNight: pricePerNight, 
        
        campervanId: campervanId,
        errorMessage: '',
        isSubmitting: false,

        // PROPIEDADES NUEVAS/MODIFICADAS
        totalPrice: 0, // <-- Precio calculado por la API
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

        // Propiedades calculadas
        get nightsCount() {
            if (!this.dates.checkIn || !this.dates.checkOut) return 0;
            const start = new Date(this.dates.checkIn);
            const end = new Date(this.dates.checkOut);
            const diffTime = Math.abs(end - start);
            return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        },

        // El getter totalPrice ha sido ELIMINADO. Ahora es una propiedad dinámica.

        get isRangeValid() {
            return this.dates.checkIn && this.dates.checkOut && this.nightsCount >= 1 && !this.errorMessage;
        },
        
        // MÉTODO NUEVO: Llama al API para obtener el precio real
        async fetchPrice() {
            this.totalPrice = 0; // Resetear antes de la llamada

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
                    // Si el API devuelve un error (ej. validación), manejarlo
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
                // Reset si ya hay un rango completo
                this.dates.checkIn = date;
                this.dates.checkOut = null;
                this.errorMessage = '';
                this.totalPrice = 0; // Resetear precio
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
                
                // Si el rango es válido, llamamos al API para el precio
                if (this.isRangeValid) {
                    this.fetchPrice();
                }
            } else {
                // Si selecciona una fecha anterior, la hace checkIn
                this.dates.checkIn = date;
                this.dates.checkOut = null;
                this.errorMessage = '';
                this.totalPrice = 0; // Resetear precio
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
                this.totalPrice = 0; // Resetear precio
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
                    this.totalPrice = 0; // Resetear precio
                    return;
                }
                current.setDate(current.getDate() + 1);
            }
            
            // Si la validación pasa y no hay errores, se podría llamar fetchPrice aquí también
            // Pero es más limpio llamarlo solo una vez en selectDate para evitar llamadas duplicadas.
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
            // Se añade la verificación de que el precio sea mayor que 0
            if (!this.isRangeValid || this.isSubmitting || this.totalPrice <= 0) return; 

            this.isSubmitting = true;

            const params = new URLSearchParams({
                campervan_id: this.campervanId,
                start_date: this.dates.checkIn,
                end_date: this.dates.checkOut,
                total_price: this.totalPrice // <-- ¡Usamos el precio de la API!
            });

            // Redirigir después de un breve delay
            setTimeout(() => {
                window.location.href = `/booking/create?${params.toString()}`;
            }, 300);
        }
    }));
});