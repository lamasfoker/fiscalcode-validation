<?php
declare(strict_types=1);

namespace App\Model\Person;

use App\Model\Person;

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
            $person->setFiscalCode(strtoupper(trim($data['fiscalcode'])));
        }
        if (array_key_exists('firstname', $data)) {
            $person->setFirstName(strtoupper(trim($data['firstname'])));
        }
        if (array_key_exists('lastname', $data)) {
            $person->setLastName(strtoupper(trim($data['lastname'])));
        }
        if (array_key_exists('birthdate', $data)) {
            $person->setBirthDate(strtoupper(trim($data['birthdate'])));
        }
        if (array_key_exists('ismale', $data)) {
            $person->setIsMale((bool)$data['ismale']);
        }
        if (array_key_exists('municipality', $data)) {
            $person->setMunicipality(strtoupper(trim($data['municipality'])));
        }
        return $person;
    }
}
