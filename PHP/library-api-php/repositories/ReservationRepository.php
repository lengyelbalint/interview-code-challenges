<?php

require_once __DIR__ . '/../lib/LibraryStore.php';

final class ReservationRepository
{
    public function __construct(private LibraryStore $store)
    {
    }

    /** @return Reservation[] */
    public function all(): array
    {
        return $this->store->reservations;
    }

    public function nextId(): int
    {
        $max = 0;
        foreach ($this->store->reservations as $reservation) {
            $max = max($max, (int) ($reservation->id ?? 0));
        }
        return $max + 1;
    }

    public function add(Reservation $reservationeservation): void
    {
        $this->store->reservations[] = $reservationeservation;
    }

    public function activeQueueForBook(int $bookId): array
    {
        $queue = [];
        foreach ($this->store->reservations as $reservation) {
            $status = $reservation->status ?? 'active';
            if ((int) $reservation->bookId === $bookId && $status === 'active') {
                $queue[] = $reservation;
            }
        }

        usort($queue, fn($a, $b) => strtotime($a->reservedAt) <=> strtotime($b->reservedAt));
        return $queue;
    }

    public function hasActiveReservation(int $bookId, int $borrowerId): bool
    {
        foreach ($this->store->reservations as $reservation) {
            $status = $reservation->status ?? 'active';
            if ((int) $reservation->bookId === $bookId && (int) $reservation->borrowerId === $borrowerId && $status === 'active') {
                return true;
            }
        }
        return false;
    }
}