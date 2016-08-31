<?php

namespace Silly;

use ReflectionFunctionAbstract;
use Invoker\ParameterResolver\ParameterResolver;

/**
 * Tries to maps hyphenated parameters to a similarly-named,
 * non-hyphenated parameters in the function signature.
 *
 * E.g. `->call($callable, ['dry-run' => true])` will inject the boolean `true`
 * for a parameter named either `$dryrun` or `$dryRun`.
 *
 */
class HyphenatedInputResolver implements ParameterResolver
{
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ) {
        $parameters = [];

        foreach ($reflection->getParameters() as $index => $parameter) {
            $parameters[strtolower($parameter->name)] = $index;
        }

        foreach ($providedParameters as $name => $value) {
            $normalizedName = strtolower(str_replace("-", "", $name));

            // Skip parameters that do not exist with the normalized name
            if (! array_key_exists($normalizedName, $parameters)) {
                continue;
            }

            $normalizedParameterIndex = $parameters[$normalizedName];

            // Skip parameters already resolved
            if (array_key_exists($normalizedParameterIndex, $resolvedParameters)) {
                continue;
            }

            $resolvedParameters[$normalizedParameterIndex] = $value;
        }

        return $resolvedParameters;
    }
}
