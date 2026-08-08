<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonSerializable;
use Stringable;

abstract class ValueObject implements Castable, JsonSerializable, Stringable
{
    abstract public function toPrimitive(): mixed;

    abstract public static function fromPrimitive(mixed $value): static;

    public function equals(?self $other): bool
    {
        if ($other === null || get_class($this) !== get_class($other)) {
            return false;
        }

        return $this->toPrimitive() === $other->toPrimitive();
    }

    public function jsonSerialize(): mixed
    {
        return $this->toPrimitive();
    }

    public function __toString(): string
    {
        return (string) $this->toPrimitive();
    }

    /**
     * @param  array<string, mixed>  $arguments
     * @return CastsAttributes<ValueObject|null, mixed>
     */
    public static function castUsing(array $arguments): CastsAttributes
    {
        /** @implements CastsAttributes<ValueObject|null, mixed> */
        return new class(static::class) implements CastsAttributes
        {
            public function __construct(protected string $voClass) {}

            public function get(Model $model, string $key, mixed $value, array $attributes): mixed
            {
                return $value !== null ? ($this->voClass)::fromPrimitive($value) : null;
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): mixed
            {
                if ($value === null) {
                    return null;
                }

                if ($value instanceof ValueObject) {
                    return $value->toPrimitive();
                }

                return ($this->voClass)::fromPrimitive($value)->toPrimitive();
            }
        };
    }
}
