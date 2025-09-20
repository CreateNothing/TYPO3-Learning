<?php

namespace App\Service;

class VectorFormatter
{
    /**
     * @param float[] $vector
     */
    public function toDatabaseLiteral(array $vector): string
    {
        $values = array_map(
            static fn (float $value): string => rtrim(rtrim(sprintf('%.8F', $value), '0'), '.'),
            $vector,
        );

        return '[' . implode(',', $values) . ']';
    }
}
