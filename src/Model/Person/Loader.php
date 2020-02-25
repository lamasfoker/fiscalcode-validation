<?php
declare(strict_types=1);

namespace LamasFoker\FiscalcodeValidation\Model\Person;

use LamasFoker\FiscalcodeValidation\Model\Person;

class Loader
{
    /**
     * @var Person
     */
    private $person;

    public function __construct()
    {
        $this->person = new Person();
    }

    public function load(string $data): Person
    {
        $data = json_decode($data, true);
        $person = $this->person;
        if (array_key_exists('fiscalcode', $data)) {
            $person->setFiscalCode($data['fiscalcode']);
        }
        if (array_key_exists('firstname', $data)) {
            $person->setFirstName($data['firstname']);
        }
        if (array_key_exists('lastname', $data)) {
            $person->setLastName($data['lastname']);
        }
        if (array_key_exists('birthdate', $data)) {
            $person->setBirthDate($data['birthdate']);
        }
        if (array_key_exists('ismale', $data)) {
            $person->setIsMale($data['ismale']);
        }
        if (array_key_exists('municipality', $data)) {
            $person->setMunicipality($data['municipality']);
        }
        return $person;
    }
}
