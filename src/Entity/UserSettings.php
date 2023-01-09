<?php

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
