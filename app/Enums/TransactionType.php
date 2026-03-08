<?php

namespace App\Enums;

enum TransactionType: string
{
  case WalletFunding    = 'wallet_funding';
  case WalletDebit      = 'wallet_debit';
  case WalletTransfer   = 'wallet_transfer';
  case BankTransfer     = 'bank_transfer';
  case Reversal         = 'reversal';
  case Settlement       = 'settlement';

  public function label(): string
  {
    return match ($this) {
      self::WalletFunding  => 'Wallet Funding',
      self::WalletDebit    => 'Wallet Debit',
      self::WalletTransfer => 'Wallet Transfer',
      self::BankTransfer   => 'Bank Transfer',
      self::Reversal       => 'Reversal',
      self::Settlement     => 'Settlement',
    };
  }

  public function badgeClass(): string
  {
    return match ($this) {
      self::WalletFunding  => 'badge-green',
      self::WalletDebit    => 'badge-red',
      self::WalletTransfer => 'badge-blue',
      self::BankTransfer   => 'badge-blue',
      self::Reversal       => 'badge-yellow',
      self::Settlement     => 'badge-slate',
    };
  }
}
