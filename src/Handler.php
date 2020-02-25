<?php
declare(strict_types=1);

namespace LamasFoker\FiscalcodeValidation;

use LamasFoker\FiscalcodeValidation\Model\Person;
use LamasFoker\FiscalcodeValidation\Model\Person\Loader;

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
     * @var string
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
        } else {
            //TODO: sanitiza fiscalcode in other way
            $fiscalCode = $this->person->getFiscalCode();
            $this->person->setFiscalCode(strtoupper(trim($fiscalCode)));
        }
        if ($this->validateLength() || $this->validateChecksum() || $this->validateChars()) {
            $this->setMessage('fiscal code is not valid');
        }
        if ($this->validateFirstNameChars()) {
            $this->setMessage('firstname does not match with fiscal code');
        }
        if ($this->validateLastNameChars()) {
            $message = 'lastname does not match with fiscal code';
        }
        //@todo: Optionally validate date of birth and gender, but be sure to take into account 'omocodie'
        //https://quifinanza.it/tasse/codice-fiscale-come-si-calcola-e-come-si-corregge-in-caso-di-omocodia/1708/

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
        $set1 = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $set2 = "ABCDEFGHIJABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $evenSet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $oddSet = "BAKPLCQDREVOSFTGUHMINJWZYX";
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
        $valid = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
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
        $firstName = strtoupper(trim($firstName));
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
        $lastName = strtoupper(trim($lastName));
        //First three consonants. If not enough consonants, add the vowels. If not anough, pad with X
        $lastNameLetters = substr(($this->keepConsonants($lastName) . $this->keepVowels($lastName) . 'XXX'), 0, 3);
        return substr($fiscalCode, 0, 3) === $lastNameLetters;
    }

    /**
     * @param $string string
     * @return string
     */
    private function keepConsonants(string $string): string
    {
        return preg_replace('/[^bcdfghjklmnpqrstvwxyz]+/i', '', $string);
    }

    /**
     * @param string $string
     * @return string
     */
    private function keepVowels(string $string): string
    {
        return preg_replace('/[^aeiou]+/i', '', $string);
    }

    /**
     * @return string
     */
    private function getMessage(): string
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
