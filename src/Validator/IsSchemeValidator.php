<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) ai poole <imabiggeek@slurp.ai>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class IsSchemeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $schemes = ['http', 'https'];

        if (!$constraint instanceof IsScheme) {
            throw new UnexpectedTypeException($constraint, IsScheme::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            throw new UnexpectedValueException($value, 'string');
        }

        if (!in_array($value, $schemes)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation()
            ;
        }
    }
}
