<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final class AccountCode extends ValueObject
{
    public readonly string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);

        if ($normalized === '' || ! preg_match('/^\d+(\.\d+)*$/', $normalized)) {
            throw new InvalidArgumentException("Formato de código contábil inválido: '{$value}'");
        }

        $this->value = $normalized;
    }

    public static function fromPrimitive(mixed $value): static
    {
        if ($value instanceof self) {
            return $value;
        }

        if (! is_string($value) && ! is_numeric($value)) {
            $type = get_debug_type($value);
            throw new InvalidArgumentException("Valor inválido para instanciação de AccountCode: '{$type}'");
        }

        return new self((string) $value);
    }

    public function toPrimitive(): string
    {
        return $this->value;
    }

    public function level(): int
    {
        return substr_count($this->value, '.') + 1;
    }

    /**
     * @return array<int, string>
     */
    public function segments(): array
    {
        return explode('.', $this->value);
    }

    public function parentCode(): ?self
    {
        $lastDot = strrpos($this->value, '.');

        if ($lastDot === false) {
            return null;
        }

        return new self(substr($this->value, 0, $lastDot));
    }

    public function isChildOf(self $parent): bool
    {
        return str_starts_with($this->value, $parent->value.'.');
    }

    public function isParentOf(self $child): bool
    {
        return $child->isChildOf($this);
    }
}
