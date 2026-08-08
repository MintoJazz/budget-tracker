<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use NumberFormatter;

final class Money extends ValueObject
{
    public function __construct(public readonly int $cents)
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Valores em linha contábil não podem ser negativos.');
        }
    }

    public static function fromPrimitive(mixed $value): static
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_int($value) || (is_string($value) && preg_match('/^-?\d+$/', trim($value)))) {
            return new self((int) $value);
        }

        if (is_numeric($value) || (is_string($value) && str_contains($value, ','))) {
            return self::fromDecimal($value);
        }

        $type = get_debug_type($value);

        throw new InvalidArgumentException("Valor inválido para instanciação de Money: '{$type}'");
    }

    public static function fromCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromDecimal(float|int|string $amount): self
    {
        if (is_string($amount) && str_contains($amount, ',')) {
            $amount = str_replace(',', '.', trim($amount));
        }

        if (! is_numeric($amount)) {
            throw new InvalidArgumentException("Valor numérico inválido: '{$amount}'");
        }

        $cents = (int) round(((float) $amount) * 100);

        return new self($cents);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function toPrimitive(): int
    {
        return $this->cents;
    }

    public function toDecimal(): float
    {
        return $this->cents / 100;
    }

    public function toFormattedString(string $locale = 'pt_BR', string $currency = 'BRL'): string
    {
        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $formatted = $formatter->formatCurrency($this->toDecimal(), $currency);

            if ($formatted !== false) {
                return $formatted;
            }
        }

        return 'R$ '.number_format($this->toDecimal(), 2, ',', '.');
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function subtract(self $other): self
    {
        return new self($this->cents - $other->cents);
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    public function isPositive(): bool
    {
        return $this->cents > 0;
    }

    public function isGreaterThan(self $other): bool
    {
        return $this->cents > $other->cents;
    }

    public function isLessThan(self $other): bool
    {
        return $this->cents < $other->cents;
    }

    public function isGreaterThanOrEqual(self $other): bool
    {
        return $this->cents >= $other->cents;
    }

    public function isLessThanOrEqual(self $other): bool
    {
        return $this->cents <= $other->cents;
    }
}
