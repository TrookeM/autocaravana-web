<?php

namespace App\Livewire;

use App\Models\Booking;
use App\Models\Campervan;
use App\Models\Review;
use Livewire\Component;

class CampervanReviews extends Component
{
    public Campervan $campervan;
    public $reviews;
    public $averageRating = 0;
    public $reviewCount = 0;

    // Nuevas propiedades para el formulario
    public $customer_name = '';
    public $customer_email = '';
    public $rating = 0;
    public $comment = '';

    public function mount(Campervan $campervan)
    {
        $this->campervan = $campervan;
        $this->loadReviews();
    }

    public function loadReviews()
    {
        // La relación hasManyThrough ahora se encarga de esto
        $this->reviews = $this->campervan->reviews()->latest()->get();
        $this->reviewCount = $this->reviews->count();
        $this->averageRating = $this->reviews->avg('rating');
    }

    // La función checkIfUserCanReview() se ha eliminado
    
    public function submitReview()
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|min:10',
        ]);

        // 1. Buscar una reserva completada que coincida
        $booking = Booking::where('campervan_id', $this->campervan->id)
            ->where('customer_email', $this->customer_email)
            ->where('status', 'confirmed')
            ->first();

        // 2. Si no se encuentra la reserva
        if (!$booking) {
            session()->flash('error', 'No se encontró una reserva completada con ese email para esta caravana.');
            return;
        }

        // 3. Comprobar si esa reserva ya tiene una reseña
        if ($booking->review) {
            session()->flash('error', 'Esa reserva ya ha sido utilizada para dejar una reseña.');
            return;
        }

        // 4. Crear la reseña vinculada a la reserva
        $booking->review()->create([
            'customer_name' => $this->customer_name,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);

        // Reseteamos todo
        session()->flash('success', '¡Gracias por tu reseña!');
        $this->reset(['customer_name', 'customer_email', 'rating', 'comment']);
        $this->loadReviews(); // Recargamos las reseñas
    }

    public function render()
    {
        return view('livewire.campervan-reviews');
    }
}