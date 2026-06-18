<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when a slot a patient tried to book is no longer available: either
 * it was already booked when re-checked under lock, or the UNIQUE constraint
 * on appointment.schedule_id rejected a concurrent second booking. Controllers
 * translate this into a calm "that slot was just taken" message.
 */
class SlotUnavailableException extends RuntimeException
{
    public static function make(): self
    {
        return new self('Sorry, that slot was just taken.');
    }
}
