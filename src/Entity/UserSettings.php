<?php

declare(strict_types=1);

/*
 * This file is part of the slurp package.
 * (c) ai poole <imabiggeek@slurp.ai>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

class UserSettings
{
    private $oldPassword;
    private $plainPassword;
    private $confirmPassword;

    public function setOldPassword(string $password)
    {
        $this->oldPassword = $password;
    }

    public function getOldPassword(): string
    {
        return $this->oldPassword;
    }

    public function setPlainPassword(string $password)
    {
        $this->plainPassword = $password;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    public function setConfirmPassword(string $confirm)
    {
        $this->confirmPassword = $confirm;
    }

    public function getConfirmPassword(): string
    {
        return $this->confirmPassword;
    }
}
