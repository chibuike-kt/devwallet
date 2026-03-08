<?php

namespace App\Enums;

enum TransactionStatus: string
{
  case Pending  = 'pending';
  case Success  = 'success';
  case Failed   = 'failed';
  case Reversed = 'reversed';

  public function label(): string
  {
    return match ($this) {
      self::Pending  => 'Pending',
      self::Success  => 'Success',
      self::Failed   => 'Failed',
      self::Reversed => 'Reversed',
    };
  }

  public function badgeClass(): string
  {
    return match ($this) {
      self::Pending  => 'badge-yellow',
      self::Success  => 'badge-green',
      self::Failed   => 'badge-red',
      self::Reversed => 'badge-slate',
    };
  }

  public function allowedTransitions(): array
  {
    return match ($this) {
      self::Pending  => [self::Success, self::Failed],
      self::Success  => [self::Reversed],
      self::Failed   => [],
      self::Reversed => [],
    };
  }

  public function canTransitionTo(self $next): bool
  {
    return in_array($next, $this->allowedTransitions());
  }
}
