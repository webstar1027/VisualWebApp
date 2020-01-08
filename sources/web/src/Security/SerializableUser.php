<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class SerializableUser implements UserInterface
{
    /** @var string */
    private $username;

    /** @var string|null */
    private $password;

    /** @var string|null */
    private $salt;

    /** @var string[] */
    private $roles;

    /**
     * @param string[] $roles
     */
    public function __construct(string $username, ?string $password, ?string $salt, array $roles)
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->roles = $roles;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void
    {
    }
}
