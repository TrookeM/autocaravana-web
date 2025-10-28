<div>
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <form wire:submit.prevent="submitReview" 
          class="card mb-8"
          x-data="{
              name: @entangle('customer_name'),
              email: @entangle('customer_email'),
              rating: @entangle('rating'),
              comment: @entangle('comment'),
              get isValid() {
                  return this.name.trim() !== '' && 
                         this.email.trim() !== '' && 
                         this.rating > 0 &&
                         this.comment.trim() !== '';
              }
          }">

        <h3 class="text-xl font-bold text-gray-800 mb-4">Deja tu valoración</h3>
        <p class="text-gray-600 mb-6">Escribe el email que usaste en tu reserva completada para validarla.</p>
        
        <div class="space-y-6">
            
            <div class="relative">
                <input type="text" id="customer_name" wire:model.live="customer_name" 
                       class="peer form-input-modern" 
                       placeholder=" ">
                <label for="customer_name" class="form-label-modern">Tu Nombre <span class="text-red-500">*</span></label>
                @error('customer_name') <p class="text-red-500 text-xs mt-2 flex items-center">{{ $message }}</p> @enderror
            </div>

            <div class="relative">
                <input type="email" id="customer_email" wire:model.live="customer_email" 
                       class="peer form-input-modern" 
                       placeholder=" ">
                <label for="customer_email" class="form-label-modern">Tu Email de Reserva <span class="text-red-500">*</span></label>
                @error('customer_email') <p class="text-red-500 text-xs mt-2 flex items-center">{{ $message }}</p> @enderror
            </div>

            <div class="star-rating">
                <label class="form-label mb-2">Puntuación: <span class="text-red-500">*</span></label>
                <div class="review-form-stars">
                    @foreach (range(5, 1) as $star)
                        <input type="radio" id="star{{ $star }}" wire:model.live="rating" value="{{ $star }}">
                        <label for="star{{ $star }}" class="star-label" title="{{ $star }} estrellas">&#9733;</label>
                    @endforeach
                </div>
                @error('rating') <p class="text-red-500 text-xs mt-2 flex items-center">{{ $message }}</p> @enderror
            </div>

            <div class="relative">
                <textarea id="comment" wire:model.live="comment" rows="4" 
                          class="peer form-input-modern" 
                          placeholder=" "></textarea>
                <label for="comment" class="form-label-modern">Tu reseña: <span class="text-red-500">*</span></label>
                @error('comment') <p class="text-red-500 text-xs mt-2 flex items-center">{{ $message }}</p> @enderror
            </div>
        </div>

        <button type="submit"
                class="btn-full w-full py-3 px-6 rounded-lg text-white font-bold shadow-lg transition duration-200 focus:outline-none focus:ring-4 focus:ring-offset-2 focus:ring-emerald-500 mt-8"
                :disabled="!isValid"
                :class="{
                    'bg-emerald-500 hover:bg-emerald-600 cursor-pointer': isValid,
                    'bg-gray-400 cursor-not-allowed': !isValid
                }"
                wire:loading.attr="disabled"
                wire:loading.class="!bg-gray-400 !cursor-not-allowed"
                wire:target="submitReview">

            <span wire:loading.remove wire:target="submitReview">
                Enviar Reseña
            </span>
            <span wire:loading wire:target="submitReview">
                Enviando...
            </span>
        </button>
    </form>


    <hr class="my-8">
    
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Reseñas ({{ $reviewCount }})</h2>
    
    @if ($reviewCount > 0)
        <p class="text-lg text-gray-700 mb-6">
            <strong>Puntuación Media: {{ number_format($averageRating, 1) }} / 5</strong>
            <span class="text-yellow-500 ml-2">&#9733;</span>
        </p>

        <div class="reviews-list space-y-6">
            @foreach ($reviews as $review)
                <div class="review-item bg-white p-5 rounded-lg shadow">
                    <div class="flex items-center justify-between mb-2">
                        <strong class="text-gray-900">{{ $review->customer_name ?? 'Usuario' }}</strong>
                        <span class="text-sm text-gray-500">{{ $review->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex items-center mb-3">
                        @foreach (range(1, 5) as $star)
                            <span class="{{ $review->rating >= $star ? 'text-yellow-500' : 'text-gray-300' }} text-xl">&#9733;</span>
                        @endforeach
                    </div>
                    <p class="text-gray-600 leading-relaxed">{{ $review->comment }}</p>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-gray-600">Esta caravana aún no tiene reseñas. ¡Sé el primero!</p>
    @endif
    
</div>