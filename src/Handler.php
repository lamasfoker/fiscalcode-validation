<?php
declare(strict_types=1);

namespace App;

use App\Model\Person;
use App\Model\Person\Loader;

class Handler
{
    /**
     * @var Loader
     */
    private $personLoader;
    /**
     * @var Person
     */
    private $person;
    /**
     * @var string|null
     */
    private $message = null;

    public function __construct()
    {
        $this->personLoader = new Loader();
    }

    /**
     * @param string $data
     */
    public function handle(string $data)
    {
        $this->person = $this->personLoader->load($data);

        if (is_null($this->person->getFiscalCode())) {
            $this->setMessage('fiscal code is not present');
        }
        if ($this->validateLength() || $this->validateChecksum() || $this->validateChars()) {
            $this->setMessage('fiscal code is not valid');
        }
        if ($this->validateFirstNameChars()) {
            $this->setMessage('firstname does not match with fiscal code');
        }
        if ($this->validateLastNameChars()) {
            $this->setMessage('lastname does not match with fiscal code');
        }
        if ($this->validateBirthDate()) {
            $this->setMessage('birth date does not match with fiscal code');
        }
        if ($this->validateGender()) {
            $this->setMessage('gender does not match with fiscal code');
        }
        //@todo: Optionally validate date of birth and gender, but be sure to take into account 'omocodie'
        //https://quifinanza.it/tasse/codice-fiscale-come-si-calcola-e-come-si-corregge-in-caso-di-omocodia/1708/
        //https://www1.agenziaentrate.gov.it/documentazione/versamenti/codici/ricerca/VisualizzaTabella.php?ArcName=COM-ICI

        $this->send();
    }

    /**
     * @return bool
     */
    private function validateLength(): bool
    {
        if ($this->getMessage()) {
            return true;
        }
        return strlen($this->person->getFiscalCode()) != 16;
    }

    /**
     * @return bool
     */
    private function validateChecksum(): bool
    {
        if ($this->getMessage()) {
            return true;
        }
        $fiscalCode = $this->person->getFiscalCode();
        $set1 = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $set2 = 'ABCDEFGHIJABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $evenSet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $oddSet = 'BAKPLCQDREVOSFTGUHMINJWZYX';
        $sum = 0;
        for ($i = 0; $i < 15; ++$i) {
            $charNoDigits = $set2[strpos($set1, $fiscalCode[$i])];
            if (($i % 2) == 0) {
                $sum += strpos($oddSet, $charNoDigits);
            } else {
                $sum += strpos($evenSet, $charNoDigits);
            }
        }
        return (($sum % 26) == ord($fiscalCode[15])-ord('A'));
    }

    /**
     * @return bool
     */
    private function validateChars(): bool
    {
        if ($this->getMessage()) {
            return true;
        }
        $fiscalCode = $this->person->getFiscalCode();
        $valid = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for ($i = 0; $i < 16; $i++) {
            if (strpos($valid, $fiscalCode[$i]) === false) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    private function validateFirstNameChars(): bool
    {
        if ($this->getMessage()) {
            return true;
        }
        $fiscalCode = $this->person->getFiscalCode();
        $firstName = $this->person->getFirstName();
        //https://quifinanza.it/tasse/codice-fiscale-come-si-calcola-e-come-si-corregge-in-caso-di-omocodia/1708/
        $firstNameConsonants = $this->keepConsonants($firstName);
        if (strlen($firstNameConsonants) >= 4) {
            //First, third and fourth consonant
            $firstNameLetters = $firstNameConsonants[0] . substr($firstNameConsonants, 2, 2);
        } else {
            //First three consonants. If not enough consonants, add the vowels. If not enough, pad with X
            $firstNameLetters = substr(($firstNameConsonants . $this->keepVowels($firstName) . 'XXX'), 0, 3);
        }
        return substr($fiscalCode, 3, 3) === $firstNameLetters;
    }

    /**
     * @return bool
     */
    private function validateLastNameChars(): bool
    {
        if ($this->getMessage()) {
            return true;
        }
        $fiscalCode = $this->person->getFiscalCode();
        $lastName = $this->person->getLastName();
        //First three consonants. If not enough consonants, add the vowels. If not enough, pad with X
        $lastNameLetters = substr(($this->keepConsonants($lastName) . $this->keepVowels($lastName) . 'XXX'), 0, 3);
        return substr($fiscalCode, 0, 3) === $lastNameLetters;
    }

    /**
     * @return bool
     */
    private function validateBirthDate(): bool
    {
        if ($this->getMessage()) {
            return true;
        }
        $fiscalCode = $this->person->getFiscalCode();
        $birthDate = $this->person->getBirthDate();
        $year = substr($birthDate, 2, 2);
        $month = (int)substr($birthDate, 5, 2);
        $day = substr($birthDate, 8, 2);
        $monthArrayMap = [
            1 => 'A',
            2 => 'B',
            3 => 'C',
            4 => 'D',
            5 => 'E',
            6 => 'H',
            7 => 'L',
            8 => 'M',
            9 => 'P',
            10 => 'R',
            11 => 'S',
            12 => 'T',
        ];
        if ($year !== substr($fiscalCode, 6, 2)) {
            return false;
        }
        if (!array_key_exists($month, $monthArrayMap) || $monthArrayMap[$month] !== substr($fiscalCode, 8, 1)) {
            return false;
        }
        return $day === substr($fiscalCode, 9, 2);
    }

    /**
     * @return bool
     */
    private function validateGender(): bool
    {
        if ($this->getMessage()) {
            return true;
        }
        $fiscalCode = $this->person->getFiscalCode();
        $day = (int)substr($fiscalCode, 9, 2);
        if (!$this->person->getIsMale()) {
            $day -= 40;
        }
        return $day > 0 && $day < 32;
    }

    /**
     * @param $string string
     * @return string
     */
    private function keepConsonants(string $string): string
    {
        return preg_replace('/[^BCDEFGHJKLMNPQRSTUVWXYZ]+/', '', $string);
    }

    /**
     * @param string $string
     * @return string
     */
    private function keepVowels(string $string): string
    {
        return preg_replace('/[^AEIOU]+/', '', $string);
    }

    /**
     * @return string|null
     */
    private function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    private function setMessage(string $message): void
    {
        $this->message = $message;
    }

    private function send()
    {
        if ($this->getMessage()) {
            http_response_code(404);
        } else {
            http_response_code(200);
            $this->setMessage('fiscal code is valid');
        }
        header('Content-Type: application/json');
        echo json_encode(['message' => $this->getMessage()]);
    }
}
