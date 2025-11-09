// ------------------------------
// 1. Comprobamos si Livewire cargará Alpine o no
// ------------------------------
let alpineLoadedByLivewire = false;

// Livewire avisa justo antes de iniciar Alpine
document.addEventListener('livewire:init', () => {
    alpineLoadedByLivewire = true;
});

// ------------------------------
// 2. Si Livewire NO está en esta página, importamos Alpine manualmente
// ------------------------------
let AlpineImported = null;

(async () => {
    if (!window.Livewire) {
        AlpineImported = (await import('alpinejs')).default;
        window.Alpine = AlpineImported;
        AlpineImported.start();
    }
})();

// ------------------------------
// 3. Importar Bootstrap (si Livewire existe, aquí cargará Alpine)
// ------------------------------
import './bootstrap';

// ------------------------------
// 4. Si Livewire cargó Alpine, iniciamos nuestros componentes con alpine:init
// ------------------------------
document.addEventListener('alpine:init', () => {

    // ✅ CUPÓN CHECKOUT
    // (Esta sección 'couponLogic' no tiene cambios)
    Alpine.data('couponLogic', () => ({
        basePrice: 0,
        nights: 0,
        extrasData: {},
        selectedExtras: [],
        depositPercentage: 0.3,
        dueDate: '',

        routeApply: '',
        routeRemove: '',

        couponCodeInput: '',
        couponDiscount: 0,
        couponCodeApplied: '',
        couponSuccess: '',
        couponError: '',

        init() {
            const el = this.$el;

            this.basePrice = parseFloat(el.dataset.basePrice || 0);
            this.nights = parseInt(el.dataset.nights || 0);
            this.extrasData = JSON.parse(el.dataset.extras || '{}');
            this.selectedExtras = JSON.parse(el.dataset.oldExtras || '[]');
            this.depositPercentage = parseFloat(el.dataset.depositPercentage || 0.3);
            this.dueDate = el.dataset.dueDate || '';

            this.routeApply = el.dataset.routeApply;
            this.routeRemove = el.dataset.routeRemove;

            this.couponDiscount = parseFloat(el.dataset.sessionDiscount || 0);
            this.couponCodeInput = el.dataset.sessionCode || '';
            this.couponCodeApplied = el.dataset.sessionCode || '';
            this.couponSuccess = el.dataset.sessionSuccess || '';
            this.couponError = el.dataset.sessionError || '';
        },

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
            }).then(r => r.json()).then(data => {
                if (data.error) {
                    this.couponError = data.error;
                    this.couponDiscount = 0;
                    this.couponCodeApplied = '';
                } else {
                    this.couponDiscount = parseFloat(data.discount_amount);
                    this.couponSuccess = data.message;
                    this.couponCodeApplied = data.coupon_code;
                    this.couponCodeInput = '';
                }
            });
        },

        removeCoupon() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(this.routeRemove, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            }).then(() => {
                this.couponCodeInput = '';
                this.couponDiscount = 0;
                this.couponCodeApplied = '';
                this.couponSuccess = '';
                this.couponError = '';
            });
        },

        handleExtraChange() {
            if (this.couponCodeApplied) {
                this.couponSuccess = 'Los extras han cambiado. Por favor, aplica el cupón nuevamente.';
                this.couponDiscount = 0;
                this.couponCodeApplied = '';
                this.couponCodeInput = '';
            }
        },

        clearField() {
            this.couponCodeInput = '';
        }
    }));

    // ✅ CALENDARIO (MODIFICADO RF12.2 - Marketing v2)
    Alpine.data('calendar', ({ unavailableDates, maintenanceDates, campervanId, pricePerNight, allDiscountTiers }) => ({
        dates: { checkIn: null, checkOut: null },
        hoverDate: null,
        unavailableDates: new Set(unavailableDates),
        maintenanceDates: new Set(maintenanceDates),
        pricePerNight: pricePerNight,
        campervanId: campervanId,
        errorMessage: '',
        isSubmitting: false,
        csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),

        // --- CAMPOS MODIFICADOS (RF12.2) ---
        totalPrice: 0,
        basePrice: 0,
        durationDiscountAmount: 0,
        durationDiscountPercentage: 0,
        allDiscountTiers: allDiscountTiers || [],
        // -------------------------------

        updateDates(unavailableJson, maintenanceJson) {
            this.unavailableDates = new Set(JSON.parse(unavailableJson));
            this.maintenanceDates = new Set(JSON.parse(maintenanceJson));
        },

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

        // ==========================================================
        // COMPUTED: MENSAJE DE MARKETING (LÓGICA CORREGIDA)
        // ==========================================================
        get marketingMessage() {
            if (this.nightsCount <= 0) {
                return '';
            }

            // Encontrar el tramo de descuento más cercano (el primero que sea MAYOR
            // que las noches actuales).
            const nextTier = this.allDiscountTiers.find(tier => tier.min_nights > this.nightsCount);

            // Si no hay tramos siguientes (ya tienen el descuento máximo), no mostrar nada.
            if (!nextTier) {
                return '';
            }

            const nightsNeeded = nextTier.min_nights - this.nightsCount;

            // Mostrar solo si están a 1 o 2 noches de conseguirlo
            if (nightsNeeded > 0 && nightsNeeded <= 2) {
                const nochesStr = nightsNeeded > 1 ? 'noches' : 'noche';
                return `¡Añade ${nightsNeeded} ${nochesStr} más y consigue un ${nextTier.percentage_discount}% de descuento!`;
            }

            return ''; // No están lo suficientemente cerca del *siguiente* tramo
        },
        // ==========================================================

        async fetchPrice() {
            // Reseteamos precios
            this.totalPrice = 0;
            this.basePrice = 0;
            this.durationDiscountAmount = 0;
            this.durationDiscountPercentage = 0;

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
                    this.totalPrice = data.price_breakdown.final_price;
                    this.basePrice = data.price_breakdown.base_price;
                    this.durationDiscountAmount = data.price_breakdown.duration_discount_amount;
                    this.durationDiscountPercentage = data.price_breakdown.duration_discount_percentage;
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
            // Reseteamos precios al seleccionar
            this.totalPrice = 0;
            this.basePrice = 0;
            this.durationDiscountAmount = 0;
            this.durationDiscountPercentage = 0;

            if (this.dates.checkIn && this.dates.checkOut) {
                this.dates.checkIn = date;
                this.dates.checkOut = null;
                this.errorMessage = '';
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
                // Ahora, calculamos el precio (fetchPrice) incluso si el rango no es válido
                // para poder mostrar el mensaje de marketing si solo falta 1 noche.
                // fetchPrice() y marketingMessage() ya comprueban internamente si hay errores.
                this.fetchPrice();
            } else {
                this.dates.checkIn = date;
                this.dates.checkOut = null;
                this.errorMessage = '';
            }
        },

        // MODIFICADO: validateRange() ya no llama a fetchPrice()
        validateRange() {
            this.errorMessage = '';
            if (!this.dates.checkIn || !this.dates.checkOut) return;

            const start = new Date(this.dates.checkIn);
            const end = new Date(this.dates.checkOut);

            if (end <= start) {
                this.errorMessage = 'La fecha de salida debe ser posterior a la de entrada';
                this.dates.checkOut = null;
                this.totalPrice = 0;
                this.basePrice = 0;
                this.durationDiscountAmount = 0;
                this.durationDiscountPercentage = 0;
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
                    this.basePrice = 0;
                    this.durationDiscountAmount = 0;
                    this.durationDiscountPercentage = 0;
                    return;
                }
                current.setDate(current.getDate() + 1);
            }
            // ¡Quitamos fetchPrice() de aquí! Se llama en selectDate()
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
                month: 'short', // <-- Solución A (mes corto) aplicada
                year: 'numeric'
            });
        },

        submitBooking() {
            if (!this.isRangeValid || this.isSubmitting || this.totalPrice <= 0) return;

            this.isSubmitting = true;

            // --- ¡MODIFICADO! ---
            // Ahora pasamos el desglose completo a la página de checkout
            const params = new URLSearchParams({
                campervan_id: this.campervanId,
                start_date: this.dates.checkIn,
                end_date: this.dates.checkOut,
                
                // El precio base ANTES de descuentos (con temporada)
                base_price_before_discount: this.basePrice, 
                
                // El descuento por duración
                duration_discount_amount: this.durationDiscountAmount,
                
                // El precio final (Base - Descuento Duración)
                // Este será el 'base_price' para el checkout (antes de cupones/extras)
                total_price: this.totalPrice 
            });
            // --- FIN DE MODIFICACIÓN ---

            setTimeout(() => {
                window.location.href = `/booking/create?${params.toString()}`;
            }, 300);
        },

        isMaintenanceDate(dateString) {
            return this.maintenanceDates.has(dateString);
        }
    }));
});

document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.getElementById('navbar');
    const navLogo = document.getElementById('nav-logo');
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    // Función para sincronizar estado visual de la navbar
    const updateNavbar = () => {
        const forceSolid = document.body && document.body.dataset.navSolid === 'true';
        if (forceSolid || window.scrollY > 50) {
            navbar.classList.add('scrolled', 'bg-white', 'shadow-lg');
            navLogo.classList.remove('text-white');
            navLogo.classList.add('text-emerald-700');
            mobileMenuButton.classList.remove('text-white');
            mobileMenuButton.classList.add('text-gray-800');
        } else {
            navbar.classList.remove('scrolled', 'bg-white', 'shadow-lg');
            navLogo.classList.add('text-white');
            navLogo.classList.remove('text-emerald-700');
            mobileMenuButton.classList.add('text-white');
            mobileMenuButton.classList.remove('text-gray-800');
            // Cierra el menú móvil si se vuelve a la parte superior
            mobileMenu.classList.add('hidden');
            mobileMenuButton?.classList.remove('open');
            mobileMenuButton?.setAttribute('aria-expanded', 'false');
        }
    };

    // Estado inicial + escucha de scroll
    updateNavbar();
    window.addEventListener('scroll', updateNavbar);

    // Toggle para el menú móvil con animación + overlay
    const mobileOverlay = document.getElementById('mobile-menu-overlay');
    mobileMenuButton.addEventListener('click', () => {
        const willOpen = mobileMenu.classList.contains('hidden');

        if (willOpen) {
            // Mostrar y animar apertura
            mobileMenu.classList.remove('hidden');
            // Forzar reflow para que la transición ocurra
            void mobileMenu.offsetHeight;
            mobileMenu.classList.add('open');
            mobileOverlay?.classList.remove('hidden');
            mobileOverlay?.classList.add('open');
            // Botón a estado abierto
            mobileMenuButton.classList.add('open');
            mobileMenuButton.setAttribute('aria-expanded', 'true');
        } else {
            // Animar cierre
            mobileMenu.classList.remove('open');
            mobileOverlay?.classList.remove('open');
            const onEnd = () => {
                mobileMenu.classList.add('hidden');
                mobileMenu.removeEventListener('transitionend', onEnd);
                mobileOverlay?.classList.add('hidden');
            };
            mobileMenu.addEventListener('transitionend', onEnd);
            // Botón a estado cerrado
            mobileMenuButton.classList.remove('open');
            mobileMenuButton.setAttribute('aria-expanded', 'false');
        }
    });

    // Cerrar menú móvil al hacer clic en overlay o en un enlace
    const closeMobileMenu = () => {
        if (!mobileMenu.classList.contains('hidden')) {
            mobileMenu.classList.remove('open');
            mobileOverlay?.classList.remove('open');
            const onEnd = () => {
                mobileMenu.classList.add('hidden');
                mobileMenu.removeEventListener('transitionend', onEnd);
                mobileOverlay?.classList.add('hidden');
            };
            mobileMenu.addEventListener('transitionend', onEnd);
            mobileMenuButton.classList.remove('open');
            mobileMenuButton.setAttribute('aria-expanded', 'false');
        }
    };
    mobileOverlay?.addEventListener('click', closeMobileMenu);
    document.querySelectorAll('#mobile-menu a').forEach(link => {
        link.addEventListener('click', closeMobileMenu);
    });
});

// ------------------------------
// 5. Importar Flatpickr
// ------------------------------
import flatpickr from "flatpickr";
import { Spanish } from "flatpickr/dist/l10n/es";
import "flatpickr/dist/flatpickr.min.css";

flatpickr.setDefaults({
    locale: Spanish,
    disableMobile: true
});
window.flatpickr = flatpickr;
