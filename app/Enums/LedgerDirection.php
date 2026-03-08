<?php

namespace App\Enums;

enum LedgerDirection: string
{
  case Credit = 'credit';
  case Debit  = 'debit';

  public function label(): string
  {
    return match ($this) {
      self::Credit => 'Credit',
      self::Debit  => 'Debit',
    };
  }

  public function badgeClass(): string
  {
    return match ($this) {
      self::Credit => 'badge-green',
      self::Debit  => 'badge-red',
    };
  }
}
