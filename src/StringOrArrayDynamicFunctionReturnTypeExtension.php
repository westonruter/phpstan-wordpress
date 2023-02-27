<?php

/**
 * Set return type of esc_sql().
 */

declare(strict_types=1);

namespace SzepeViktor\PHPStan\WordPress;

use PhpParser\Node\Expr\FuncCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Type\StringType;
use PHPStan\Type\ArrayType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\Type;

class StringOrArrayDynamicFunctionReturnTypeExtension implements \PHPStan\Type\DynamicFunctionReturnTypeExtension
{
    public function isFunctionSupported(FunctionReflection $functionReflection): bool
    {
        return $functionReflection->getName() === 'esc_sql';
    }

    /**
     * @see https://developer.wordpress.org/reference/functions/esc_sql/
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function getTypeFromFunctionCall(FunctionReflection $functionReflection, FuncCall $functionCall, Scope $scope): ?Type
    {
        $args = $functionCall->getArgs();
        if (count($args) === 0) {
            return null;
        }

        $dataArgType = $scope->getType($args[0]->value);
        if ($dataArgType->isArray()->yes()) {
            $keyType = $dataArgType->getIterableKeyType();
            if ($keyType instanceof StringType) {
                return new ArrayType(new StringType(), new StringType());
            }

            return new ArrayType(new IntegerType(), new StringType());
        }

        return new StringType();
    }
}
