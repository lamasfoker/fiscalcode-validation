<?php
declare(strict_types=1);

namespace LamasFoker\FiscalcodeValidation\Model;

class Person
{
    /**
     * @var string|null
     */
    private $fiscalCode = null;
    /**
     * @var string|null
     */
    private $firstName = null;
    /**
     * @var string|null
     */
    private $lastName = null;
    /**
     * @var string|null
     */
    private $birthDate = null;
    /**
     * @var bool|null
     */
    private $isMale = null;
    /**
     * @var string|null
     */
    private $municipality = null;

    /**
     * @return string|null
     */
    public function getFiscalCode(): ?string
    {
        return $this->fiscalCode;
    }

    /**
     * @param string|null $fiscalCode
     */
    public function setFiscalCode(?string $fiscalCode): void
    {
        $this->fiscalCode = $fiscalCode;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     */
    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     */
    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string|null
     */
    public function getBirthDate(): ?string
    {
        return $this->birthDate;
    }

    /**
     * @param string|null $birthDate
     */
    public function setBirthDate(?string $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    /**
     * @return bool|null
     */
    public function getIsMale(): ?bool
    {
        return $this->isMale;
    }

    /**
     * @param bool|null $isMale
     */
    public function setIsMale(?bool $isMale): void
    {
        $this->isMale = $isMale;
    }

    /**
     * @return string|null
     */
    public function getMunicipality(): ?string
    {
        return $this->municipality;
    }

    /**
     * @param string|null $municipality
     */
    public function setMunicipality(?string $municipality): void
    {
        $this->municipality = $municipality;
    }
}
